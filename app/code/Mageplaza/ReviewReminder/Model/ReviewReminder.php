<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ReviewReminder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ReviewReminder\Model;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ReviewReminder\Helper\Data;
use Mageplaza\ReviewReminder\Model\LogsFactory;
use Mageplaza\ReviewReminder\Model\ResourceModel\Logs\CollectionFactory;
use Psr\Log\LoggerInterface;
use Zend_Serializer_Exception;

/**
 * Class ReviewReminder
 * @package Mageplaza\ReviewReminder\Model
 */
class ReviewReminder
{
    /**
     * @var Data
     */
    private $reviewReminderData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Date model
     *
     * @var DateTime
     */
    private $date;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LogsFactory
     */
    private $logsFactory;

    /**
     * @var FactoryInterface
     */
    private $templateFactory;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var CollectionFactory
     */
    private $logsCollectionFactory;

    /**
     * @param Data $reviewReminderData
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param LogsFactory $logsFactory
     * @param FactoryInterface $templateFactory
     * @param SubscriberFactory $subscriberFactory
     * @param CollectionFactory $logsCollectionFactory
     */
    public function __construct(
        Data $reviewReminderData,
        LoggerInterface $logger,
        DateTime $date,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        LogsFactory $logsFactory,
        FactoryInterface $templateFactory,
        SubscriberFactory $subscriberFactory,
        CollectionFactory $logsCollectionFactory
    ) {
        $this->reviewReminderData    = $reviewReminderData;
        $this->date                  = $date;
        $this->logger                = $logger;
        $this->storeManager          = $storeManager;
        $this->transportBuilder      = $transportBuilder;
        $this->logsFactory           = $logsFactory;
        $this->templateFactory       = $templateFactory;
        $this->subscriberFactory     = $subscriberFactory;
        $this->logsCollectionFactory = $logsCollectionFactory;
    }

    /**
     * @param $order
     *
     * @return $this
     * @throws Zend_Serializer_Exception
     */
    public function prepareForSendingReviews($order)
    {
        $storeId = $order->getStoreId();
        if ($this->reviewReminderData->onlySendToSubscribed($storeId)) {
            $customerEmail = $order->getCustomerEmail();
            $subscriber    = $this->subscriberFactory->create()->loadByEmail($customerEmail);
            if (!$subscriber->isSubscribed()) {
                return $this;
            }
        }

        $configs = $this->reviewReminderData->getEmailConfig($storeId);
        if (!empty($configs)) {
            $updatedAt       = strtotime($order->getUpdatedAt());
            $shippingAddress = $order->getShippingAddress();
            $billingAddress  = $order->getBillingAddress();
            $sequence        = 1;
            if ($order->getCustomerId()) {
                $customerName = $order->getCustomerName();
            } elseif ($shippingAddress->getFirstname() && $shippingAddress->getLastname()) {
                $customerName = $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname();
            } elseif ($billingAddress->getFirstname() && $billingAddress->getLastname()) {
                $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
            } else {
                $customerName = __('Guest');
            }
            foreach ($configs as $config) {
                $time     = $updatedAt + $config['send'];
                $template = $this->createEmail($order, $config, $customerName);
                $this->logsFactory->create()->saveLogs(
                    $config,
                    $order,
                    $template->processTemplate(),
                    $template->getSubject(),
                    $sequence,
                    $this->date->date('Y-m-d H:i:s', $time),
                    $customerName
                );
                $sequence++;
            }
        }
    }

    /**
     * @param $order
     * @param $config
     * @param $customerName
     *
     * @return TemplateInterface
     * @throws NoSuchEntityException
     */
    private function createEmail($order, $config, $customerName)
    {
        $subscriber      = $this->subscriberFactory->create()->loadByEmail($order->getCustomerEmail());
        $unsubscribeLink = $subscriber->isSubscribed() ? $subscriber->getUnsubscriptionLink() : '';
        $store           = $this->storeManager->getStore($order->getStoreId());
        $vars            = [
            'store'            => $store,
            'customer_name'    => ucfirst($customerName),
            'sender'           => $config['sender'],
            'order'            => $order,
            'unsubscribe_link' => $unsubscribeLink
        ];
        $template        = $this->templateFactory->get(
            $config['template']
        )->setVars($vars)->setOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $store->getId()]
        );

        return $template;
    }

    /**
     * @return void
     */
    public function sendMailCron()
    {
        $logs = $this->logsCollectionFactory->create();
        $logs->addFieldToFilter('status', ['eq' => 3])
            ->addFieldToFilter('display', ['eq' => 1])
            ->addFieldToFilter('send_at', ['lteq' => $this->date->date()]);
        foreach ($logs as $log) {
            $this->sendMail($log);
        }
    }

    /**
     * @param $log
     */
    private function sendMail($log)
    {
        try {
            $store = $this->storeManager->getStore();
            $this->transportBuilder->setTemplateIdentifier('send_again')
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
                ->setTemplateVars(
                    [
                        'body'    => htmlspecialchars_decode($log->getEmailContent()),
                        'subject' => $log->getSubject()
                    ]
                )
                ->setFrom($log->getSender())
                ->addTo($log->getCustomerEmail(), $log->getCustomerName())
                ->getTransport()
                ->sendMessage();
            $log->setStatus(true);
        } catch (Exception $e) {
            $log->setStatus(false);
            $this->logger->error($e->getMessage());
        }
        $log->save();
    }

    /**
     * @param Logs $log
     *
     * @return void
     */
    public function sendMailNow($log)
    {
        $this->sendMail($log);
    }
}

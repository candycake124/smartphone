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
 * @package     Mageplaza_GoogleTagManager
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GoogleTagManager\Observer\Adminhtml;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GoogleTagManager\Helper\Data;
use Zend_Http_Response;

/**
 * Class CreateOrder
 * @package Mageplaza\GoogleTagManager\Observer\Adminhtml
 */
class CreateOrder implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CreateOrder constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $manager
     * @param CurlFactory $curlFactory
     * @param Data $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ManagerInterface $manager,
        CurlFactory $curlFactory,
        Data $helper
    ) {
        $this->_helper = $helper;
        $this->curlFactory = $curlFactory;
        $this->manager = $manager;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()
            && $this->_helper->getConfigAnalytics('enabled')
            && $this->_helper->getConfigAnalytics('admin_enabled')
        ) {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            $body = str_replace(' ', '%20', $this->getBodyData($order));
            $headers = ['Content-Type' => 'application/json', 'Content-Length' => '200'];

            $curl = $this->curlFactory->create();
            $curl->write('POST', 'https://www.google-analytics.com/collect', '1.1', $headers, $body);

            try {
                $resultCurl = $curl->read();
                if (!empty($resultCurl)) {
                    $result = Zend_Http_Response::extractCode($resultCurl);
                    if (isset($result) && in_array($result, [200, 201], false)) {
                        $this->manager->addSuccessMessage(__('Send to Google Analytics Success'));
                    } else {
                        $this->manager->addErrorMessage(__('Cannot connect to server. Please try again later.'));
                    }
                } else {
                    $this->manager->addErrorMessage(__('Cannot connect to server. Please try again later.'));
                }
            } catch (Exception $e) {
                $this->manager->addErrorMessage($e->getMessage());
            }
            $curl->close();
        }

        return $this;
    }

    /**
     * @param Order $order
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getBodyData($order)
    {
        $array = [
            'v' => '1',
            'tid' => trim($this->_helper->getConfigAnalytics('tag_id')),
            'cid' => $order->getCustomerId(),
            't' => 'pageview',
            'dh' => $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB),
            'dp' => '/success',
            'dt' => 'Success Page',
            'ti' => $order->getIncrementId(),
            'tr' => $order->getGrandTotal(),
            'tt' => $order->getBaseTaxAmount(),
            'ts' => $order->getBaseShippingAmount(),
            'tcc' => (string)$order->getCouponCode(),
            'pa' => 'purchase',
            'cu' => $this->_helper->getCurrentCurrency()
        ];

        $items = $order->getItems();

        foreach ($items as $key => $item) {
            $key++;
            /** @var Item $item */
            $data = $this->_helper->getCheckoutProductData($item);
            $array['pr' . $key . 'id'] = $data['id'];
            $array['pr' . $key . 'nm'] = $data['name'];
            $array['pr' . $key . 'qt'] = $data['quantity'];
            $array['pr' . $key . 'pr'] = $data['price'];
            if (array_key_exists('category', $data)) {
                $array['pr' . $key . 'ca'] = $data['category'];
            }
            if (array_key_exists('brand', $data)) {
                $array['pr' . $key . 'br'] = $data['brand'];
            }
            if (array_key_exists('variant', $data)) {
                $array['pr' . $key . 'va'] = $data['variant'];
            }
        }

        return implode('&', array_map(
            function ($v, $k) {
                return sprintf('%s=%s', $k, $v);
            },
            $array,
            array_keys($array)
        ));
    }
}

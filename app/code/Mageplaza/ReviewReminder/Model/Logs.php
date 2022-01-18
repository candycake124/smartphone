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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Logs
 * @package Mageplaza\ReviewReminder\Model
 */
class Logs extends AbstractModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\Logs::class);
    }

    /**
     * @param $config
     * @param $order
     * @param $body
     * @param $subject
     * @param $sequence
     * @param $scheduled
     * @param $customerName
     */
    public function saveLogs(
        $config,
        $order,
        $body,
        $subject,
        $sequence,
        $scheduled,
        $customerName
    ) {
        $this->setSubject($subject)
            ->setCustomerEmail($order->getCustomerEmail())
            ->setSender($config['sender'])
            ->setCustomerName($customerName)
            ->setEmailContent(htmlspecialchars($body))
            ->setOrderId($order->getId())
            ->setSequenceNumber($sequence)
            ->setSendAt($scheduled)
            ->setStatus(3)
            ->save();
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param $dimension
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function loadReportData($fromDate, $toDate, $dimension)
    {
        return $this->_getResource()->loadReportData($fromDate, $toDate, $dimension);
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function clear()
    {
        return $this->_getResource()->clear();
    }
}

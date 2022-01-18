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

namespace Mageplaza\ReviewReminder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\ReviewReminder\Helper\Data;
use Mageplaza\ReviewReminder\Model\ReviewReminder;
use Zend_Serializer_Exception;

/**
 * Class CreateLogs
 * @package Mageplaza\ReviewReminder\Observer
 */
class CreateLogs implements ObserverInterface
{
    /**
     * @var ReviewReminder
     */
    private $reviewReminderModel;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param ReviewReminder $reviewReminderModel
     * @param Data $helperData
     */
    public function __construct(
        ReviewReminder $reviewReminderModel,
        Data $helperData
    ) {
        $this->reviewReminderModel = $reviewReminderModel;
        $this->helperData          = $helperData;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param Observer $observer
     *
     * @throws Zend_Serializer_Exception
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $items = $this->helperData->getItemsToReview($order);
        if (count($items)) {
            $this->reviewReminderModel->prepareForSendingReviews($order);
        }
    }
}

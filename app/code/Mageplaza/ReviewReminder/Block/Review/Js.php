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

namespace Mageplaza\ReviewReminder\Block\Review;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\ReviewReminder\Helper\Data;

/**
 * Class Js
 * @package Mageplaza\ReviewReminder\Block\Review
 */
class Js extends Template
{
    /**
     * @var Data
     */
    private $reviewReminderData;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $reviewReminderData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $reviewReminderData,
        array $data = []
    ) {
        $this->reviewReminderData = $reviewReminderData;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isDisplay()
    {
        return $this->reviewReminderData->isEnabled() && $this->getRequest()->getParam('rb');
    }
}

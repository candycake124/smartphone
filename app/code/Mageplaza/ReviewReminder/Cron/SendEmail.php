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

namespace Mageplaza\ReviewReminder\Cron;

use Exception;
use Mageplaza\ReviewReminder\Helper\Data;
use Mageplaza\ReviewReminder\Model\ReviewReminder;
use Psr\Log\LoggerInterface;

/**
 * Class SendEmail
 * @package Mageplaza\ReviewReminder\Cron
 */
class SendEmail
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ReviewReminder
     */
    private $reviewReminderModel;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param ReviewReminder $reviewReminderModel
     * @param Data $helperData
     */
    public function __construct(
        LoggerInterface $logger,
        ReviewReminder $reviewReminderModel,
        Data $helperData
    ) {
        $this->logger              = $logger;
        $this->reviewReminderModel = $reviewReminderModel;
        $this->helperData          = $helperData;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->helperData->isEnabled()) {
            try {
                $this->reviewReminderModel->sendMailCron();
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}

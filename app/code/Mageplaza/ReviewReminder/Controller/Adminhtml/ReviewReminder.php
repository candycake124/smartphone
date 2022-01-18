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

namespace Mageplaza\ReviewReminder\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\ReviewReminder\Model\LogsFactory;
use Mageplaza\ReviewReminder\Model\ReviewReminder as ReviewReminderModel;
use Psr\Log\LoggerInterface;

/**
 * Class ReviewReminder
 * @package Mageplaza\ReviewReminder\Controller\Adminhtml
 */
abstract class ReviewReminder extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mageplaza_ReviewReminder::reviewreminder';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LogsFactory
     */
    protected $logsFactory;

    /**
     * @var ReviewReminderModel
     */
    protected $reviewReminderModel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $jsonHelper
     * @param LoggerInterface $logger
     * @param LogsFactory $logsFactory
     * @param ReviewReminderModel $reviewReminderModel
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $jsonHelper,
        LoggerInterface $logger,
        LogsFactory $logsFactory,
        ReviewReminderModel $reviewReminderModel
    ) {
        $this->resultPageFactory   = $resultPageFactory;
        $this->jsonHelper          = $jsonHelper;
        $this->logger              = $logger;
        $this->logsFactory         = $logsFactory;
        $this->reviewReminderModel = $reviewReminderModel;

        parent::__construct($context);
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mageplaza_ReviewReminder::reviewreminder')
            ->_addBreadcrumb(__('ReviewReminder'), __('Logs'));

        return $this;
    }
}

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

namespace Mageplaza\ReviewReminder\Controller\Adminhtml\Index;

use Magento\Framework\View\Result\Page;
use Mageplaza\ReviewReminder\Controller\Adminhtml\ReviewReminder;

/**
 * Class Logs
 * @package Mageplaza\ReviewReminder\Controller\Adminhtml\Index
 */
class Logs extends ReviewReminder
{
    /**
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_ReviewReminder::log');
        $resultPage->getConfig()->getTitle()->prepend((__('Logs')));

        //Add bread crumb
        $resultPage->addBreadcrumb(__('Mageplaza'), __('Mageplaza'));
        $resultPage->addBreadcrumb(__('ReviewReminder'), __('Logs'));

        return $resultPage;
    }
}

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
 * Class Report
 * @package Mageplaza\ReviewReminder\Controller\Adminhtml\Index
 */
class Report extends ReviewReminder
{
    /**
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_ReviewReminder::report');
        $resultPage->getConfig()->getTitle()->prepend((__('Report')));
        $resultPage->addBreadcrumb(__('ReviewReminder'), __('Report'));

        return $resultPage;
    }
}

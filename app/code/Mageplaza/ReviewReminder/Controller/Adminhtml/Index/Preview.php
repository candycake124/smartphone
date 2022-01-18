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

use Exception;
use Mageplaza\ReviewReminder\Controller\Adminhtml\ReviewReminder;

/**
 * Class Preview
 * @package Mageplaza\ReviewReminder\Controller\Adminhtml\Index
 */
class Preview extends ReviewReminder
{
    /**
     * Preview email action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Email Preview'));
            $this->_view->renderLayout();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred. The email template can not be opened for preview.'));
            $this->_redirect('reviewreminder/*/');
        }
    }
}

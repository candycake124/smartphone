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
 * Class SentNow
 * @package Mageplaza\ReviewReminder\Controller\Adminhtml\Index
 */
class SentNow extends ReviewReminder
{
    /**
     * Sent now action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $log = $this->logsFactory->create()->load($id);
            if ($log->getId()) {
                try {
                    $this->reviewReminderModel->sendMailNow($log);
                    $this->messageManager->addSuccessMessage(__('Success.'));
                } catch (Exception $e) {
                    $this->logger->critical($e);
                    $this->messageManager->addErrorMessage(__('Error.'));
                }
            }
        }
        $this->_redirect('reviewreminder/*/logs');
    }
}

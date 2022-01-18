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
 * Class Clear
 * @package Mageplaza\ReviewReminder\Controller\Adminhtml\Index
 */
class Clear extends ReviewReminder
{
    /**
     * Clear logs
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->logsFactory->create()->clear();
            $this->messageManager->addSuccess(__('Clear success.'));
        } catch (Exception $e) {
            $this->messageManager->addError(__('Error.'));
        }
        $this->_redirect('reviewreminder/*/logs');
    }
}

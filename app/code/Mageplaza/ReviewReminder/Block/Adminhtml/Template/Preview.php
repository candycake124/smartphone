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

namespace Mageplaza\ReviewReminder\Block\Adminhtml\Template;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Mageplaza\ReviewReminder\Model\LogsFactory;

/**
 * Class Preview
 * @package Mageplaza\ReviewReminder\Block\Adminhtml\Template
 */
class Preview extends Widget
{
    /**
     * @var LogsFactory
     */
    private $logsFactory;

    /**
     * @param Context $context
     * @param LogsFactory $logsFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        LogsFactory $logsFactory,
        array $data = []
    ) {
        $this->logsFactory = $logsFactory;

        parent::__construct($context, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $content = '';
        if (!empty($this->getLogs()->getData())) {
            $content = htmlspecialchars_decode($this->getLogs()->getEmailContent());
        }

        return $content;
    }

    /**
     * Load email log by id
     *
     * @return mixed
     */
    private function getLogs()
    {
        $logId = $this->getRequest()->getParam('id');
        $log   = $this->logsFactory->create()->load($logId);
        if ($log) {
            return $log;
        }

        return false;
    }
}

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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\ReviewReminder\Controller\Adminhtml\ReviewReminder;

/**
 * Class Ajax
 * @package Mageplaza\ReviewReminder\Controller\Adminhtml\Index
 */
class Ajax extends ReviewReminder
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $resultPage       = $this->resultPageFactory->create();
        $fromDate         = $this->getRequest()->getParam('from');
        $toDate           = $this->getRequest()->getParam('to');
        $dimension        = $this->getRequest()->getParam('dimension');
        $result           = [];
        $result['status'] = false;
        try {
            if ($fromDate && $toDate && strtotime($fromDate) <= strtotime($toDate)) {
                $data              = $this->logsFactory->create()->loadReportData($fromDate, $toDate, $dimension);
                $reportData        = json_encode($data);
                $html              = $resultPage->getLayout()
                    ->createBlock('Magento\Backend\Block\Template')
                    ->setTemplate('Mageplaza_ReviewReminder::report/content.phtml')
                    ->setReportData($reportData)
                    ->toHtml();
                $result['content'] = $html;
                $result['status']  = true;
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }
}

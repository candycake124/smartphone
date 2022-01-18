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

namespace Mageplaza\ReviewReminder\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Order
 * @package Mageplaza\ReviewReminder\Block\Adminhtml\Grid\Column\Renderer
 */
class Order extends AbstractRenderer
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;

        parent::__construct($context, $data);
    }

    /**
     * Render sku by product id
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $html = '';
        if ($orderId = $this->_getValue($row)) {
            $order = $this->orderFactory->create()->load($orderId);
            $url   = $this->getUrl('sales/order/view', ['order_id' => $orderId]);
            $html  .= '<a href="' . $url . '">';
            $html  .= '<span>' . $order->getIncrementId() . '</span>';
            $html  .= '<a>';
        }

        return $html;
    }
}

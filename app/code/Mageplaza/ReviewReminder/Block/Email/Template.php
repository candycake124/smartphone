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

namespace Mageplaza\ReviewReminder\Block\Email;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Mageplaza\ReviewReminder\Helper\Data;

/**
 * Class Template
 * @package Mageplaza\ReviewReminder\Block\Email
 */
class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    private $_productRepositoryFactory;

    /**
     * @var Order
     */
    private $_order;

    /**
     * @var int
     */
    private $_storeId;

    /**
     * @var mixed
     */
    private $orderRepository;

    /**
     * Template constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param OrderRepositoryInterface|null $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        OrderRepositoryInterface $orderRepository = null,
        array $data = []
    ) {
        $this->imageHelper               = $context->getImageHelper();
        $this->helperData                = $helperData;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->orderRepository           = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    /**
     * Get items in order
     *
     * @return array
     */
    public function getProductCollection()
    {
        $products = [];
        if ($this->_order) {
            $items = $this->helperData->getItemsToReview($this->_order);
            foreach ($items as $item) {
                $products[] = $this->_productRepositoryFactory->create()->getById($item->getProductId());
            }
        }

        return $products;
    }

    /**
     * Get image url in order
     *
     * @param Product $_item
     *
     * @return string
     */
    public function getProductImage($_item)
    {
        return $this->imageHelper->init($_item, 'category_page_grid', ['height' => 100, 'width' => 100])->getUrl();
    }

    /**
     * Get product url in order
     *
     * @param Product $_item
     *
     * @return string
     */
    public function getProductUrl($_item)
    {
        if (!$this->_order) {
            return '';
        }
        $productUrl = $this->helperData->getUrlEmail(
            'catalog/product/view',
            [
                'id' => $_item->getId(),
                's'  => $_item->getUrlKey()
            ]
        );
        $productUrl .= $this->helperData->getAnalyticsConfig($this->_storeId);

        return $productUrl;
    }

    /**
     *
     * @param Product $_item
     *
     * @return string
     */
    public function getReviewLink($_item)
    {
        if (!$this->_order) {
            return '';
        }
        $reviewLink = $this->helperData->getUrlEmail(
            'review/product/list',
            [
                'id' => $_item->getId(),
                'rb' => 1
            ]
        );
        $reviewLink .= $this->helperData->getAnalyticsConfig($this->_storeId);

        return $reviewLink;
    }

    /**
     * Get order from template var
     *
     */
    protected function _prepareLayout()
    {
        $this->_order = $this->getOrder();
        if ($this->_order) {
            $this->_storeId = $this->_order->getStoreId();
        }

        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        $order = $this->getData('order');
        if ($order !== null) {
            return $order;
        }
        $orderId = (int) $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }
}

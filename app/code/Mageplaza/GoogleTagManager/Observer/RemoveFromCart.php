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
 * @package     Mageplaza_GoogleTagManager
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GoogleTagManager\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\GoogleTagManager\Helper\Data;

/**
 * Class RemoveFromCart
 * @package Mageplaza\GoogleTagManager\Observer
 */
class RemoveFromCart implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * RemoveFromCart constructor.
     *
     * @param ProductFactory $productFactory
     * @param Data $helper
     */
    public function __construct(
        ProductFactory $productFactory,
        Data $helper
    ) {
        $this->_productFactory = $productFactory;
        $this->_helper         = $helper;
    }

    /**
     * Catch remove from cart event
     *
     * @param Observer $observer
     *
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $quoteItem = $observer->getData('quote_item');
            $qty       = $quoteItem->getQty();

            if ($quoteItem->getProductType() === 'configurable') {
                $selectedProduct = $this->_productFactory->create();
                $selectedProduct->load($selectedProduct->getIdBySku($quoteItem->getSku()));
                $this->setGTMRemoveFromCartData($selectedProduct, $qty);
                $this->setGARemoveFromCartData($selectedProduct, $qty);
            } else {
                $this->setGTMRemoveFromCartData($quoteItem, $qty);
                $this->setGARemoveFromCartData($quoteItem, $qty);
            }
        }

        return $this;
    }

    /**
     * Set Google Tag Manage RemoveFromCart event
     *
     * @param Product $product
     * @param float $qty
     *
     * @throws NoSuchEntityException
     */
    protected function setGTMRemoveFromCartData($product, $qty)
    {
        if ($this->_helper->getConfigGTM('enabled')) {
            $this->_helper->getSessionManager()->setGTMRemoveFromCartData($this->_helper->getGTMRemoveFromCartData(
                $product,
                $qty
            ));
        }
    }

    /**
     * @param Product $product
     * @param float $qty
     *
     * @throws NoSuchEntityException
     */
    protected function setGARemoveFromCartData($product, $qty)
    {
        if ($this->_helper->getConfigAnalytics('enabled')) {
            $this->_helper->getSessionManager()->setGARemoveFromCartData($this->_helper->getGARemoveFromCartData(
                $product,
                $qty
            ));
        }
    }
}

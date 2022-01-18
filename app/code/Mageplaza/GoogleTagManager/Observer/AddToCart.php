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
use Magento\Framework\ObjectManagerInterface;
use Mageplaza\GoogleTagManager\Helper\Data;

/**
 * Class AddToCart
 * @package Mageplaza\GoogleTagManager\Observer
 */
class AddToCart implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * AddToCart constructor.
     *
     * @param ProductFactory $productFactory
     * @param ObjectManagerInterface $objectManager
     * @param Data $helper
     */
    public function __construct(
        ProductFactory $productFactory,
        ObjectManagerInterface $objectManager,
        Data $helper
    ) {
        $this->_productFactory = $productFactory;
        $this->_objectManager  = $objectManager;
        $this->_helper         = $helper;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $product = $observer->getData('product');
            $request = $observer->getData('request');

            $qty = $request->getParam('qty') ?: 1;
            if ($product->getTypeId() === 'configurable') {
                $selectedProduct = $this->_productFactory->create();
                $selectedProduct->load($selectedProduct->getIdBySku($product->getSku()));
                $this->setGTMAddToCartData($selectedProduct, $qty);
                $this->setPixelAddToCartData($selectedProduct, $qty);
                $this->setGAAddToCartData($selectedProduct, $qty);
            } else {
                $this->setGTMAddToCartData($product, $qty);
                $this->setPixelAddToCartData($product, $qty);
                $this->setGAAddToCartData($product, $qty);
            }
        }

        return $this;
    }

    /**
     * @param Product $product
     * @param float $qty
     *
     * @throws NoSuchEntityException
     */
    protected function setGTMAddToCartData($product, $qty)
    {
        if ($this->_helper->getConfigGTM('enabled')) {
            $productsGa4 = [];
            $products    = $this->_helper->getGTMAddToCartData($product, $qty);

            if ($this->_helper->isEnabledGTMGa4()) {
                $productsGa4 = $this->_helper->getGTMGa4AddToCartData($product, $qty);
            }

            if ($this->_helper->getSessionManager()->getGTMAddToCartData()) {
                $data   = $this->_helper->getSessionManager()->getGTMAddToCartData();
                $status = true;
                foreach ($data['ecommerce']['add']['products'] as $key => $value) {
                    if ($product->getId() === $value['id']) {
                        $status                                                 = false;
                        $data['ecommerce']['add']['products'][$key]['quantity'] += $qty;
                    }
                }
                if ($this->_helper->isEnabledGTMGa4()) {
                    foreach ($data['ecommerce']['items'] as $k => $val) {
                        if ($product->getId() === $val['item_id']) {
                            $status                                     = false;
                            $data['ecommerce']['items'][$k]['quantity'] += $qty;
                        }
                    }
                }

                if ($status) {
                    $data['ecommerce']['add']['products'][] = $products;

                    if ($this->_helper->isEnabledGTMGa4()) {
                        $data['ecommerce']['items'][] = $productsGa4;
                    }
                }
            } else {
                $data = [
                    'event'     => 'addToCart',
                    'ecommerce' => [
                        'currencyCode' => $this->_helper->getCurrentCurrency(),
                        'add'          => [
                            'products' => [$products]
                        ]
                    ]
                ];

                if ($this->_helper->isEnabledGTMGa4()) {
                    $data['ga4_event']          = 'add_to_cart';
                    $data['ecommerce']['items'] = [$productsGa4];
                }
            }
            $this->_helper->getSessionManager()->setGTMAddToCartData($data);
        }
    }

    /**
     * @param Product $product
     * @param float $qty
     *
     * @throws NoSuchEntityException
     */
    protected function setPixelAddToCartData($product, $qty)
    {
        if ($this->_helper->getConfigPixel('enabled')) {
            $products = $this->_helper->getFBAddToCartData($product, $qty);
            if ($this->_helper->getSessionManager()->getPixelAddToCartData()) {
                $data   = $this->_helper->getSessionManager()->getPixelAddToCartData();
                $status = true;
                foreach ($data['contents'] as $key => $value) {
                    if ($product->getId() === $value['id']) {
                        $status                             = false;
                        $data['contents'][$key]['quantity'] += $qty;
                    }
                }
                if ($status) {
                    $data['content_ids'][]  = $products['id'];
                    $data['content_name'][] = $products['name'];
                    $data['value']          += (float) $products['price'];
                    $data['contents'][]     = $products;
                }
            } else {
                $data = [
                    'content_ids'  => [$products['id']],
                    'content_name' => [$products['name']],
                    'content_type' => 'product',
                    'contents'     => [$products],
                    'currency'     => $this->_helper->getCurrentCurrency(),
                    'value'        => (float) $products['price']
                ];
            }
            $this->_helper->getSessionManager()->setPixelAddToCartData($data);
        }
    }

    /**
     * @param Product $product
     * @param float $qty
     *
     * @throws NoSuchEntityException
     */
    protected function setGAAddToCartData($product, $qty)
    {
        if ($this->_helper->getConfigAnalytics('enabled')) {
            $products = $this->_helper->getGAAddToCartData($product, $qty);
            if ($this->_helper->getSessionManager()->getGAAddToCartData()) {
                $data   = $this->_helper->getSessionManager()->getGAAddToCartData();
                $status = true;
                foreach ($data['items'] as $key => $value) {
                    if ($product->getId() === $value['id']) {
                        $status                          = false;
                        $data['items'][$key]['quantity'] += $qty;
                    }
                }
                if ($status) {
                    $data['items'][] = $products;
                }
            } else {
                $data = [
                    'items' => [$products],
                ];
            }
            $this->_helper->getSessionManager()->setGAAddToCartData($data);
        }
    }
}

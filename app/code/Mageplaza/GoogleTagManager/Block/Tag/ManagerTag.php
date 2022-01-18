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

namespace Mageplaza\GoogleTagManager\Block\Tag;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Item;
use Mageplaza\GoogleTagManager\Block\TagManager;

/**
 * Class ManagerTag
 * @package Mageplaza\GoogleTagManager\Block\Tag
 */
class ManagerTag extends TagManager
{
    /**
     * Get GTM Id
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTagId($storeId = null)
    {
        return $this->_helper->getConfigGTM('tag_id', $storeId);
    }

    /**
     * Get GTM use ID or Sku
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getUseIdOrSku($storeId = null)
    {
        return $this->_helper->getConfigGTM('use_id_or_sku', $storeId);
    }

    /**
     * Check condition show page
     *
     * @return bool
     */
    public function canShowGtm()
    {
        return $this->_helper->isEnabled() && $this->_helper->getConfigGTM('enabled');
    }

    /**
     * Tag manager dataLayer
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getGtmDataLayer()
    {
        $action = $this->getRequest()->getFullActionName();
        switch ($action) {
            case 'cms_index_index':
                return $this->encodeJs($this->getHomeData());
            case 'catalogsearch_result_index':
                return $this->encodeJs($this->getSearchData());
            case 'catalog_category_view': // Product list page
                return $this->encodeJs($this->getCategoryData());
            case 'catalog_product_view': // Product detail view page
                return $this->encodeJs($this->getProductView());
            case 'checkout_index_index':  // Checkout page
                return $this->encodeJs($this->getCheckoutProductData('2', 'Checkout Page'));
            case 'checkout_cart_index':   // Shopping cart
                return $this->encodeJs($this->getCheckoutProductData('1', 'Shopping Cart'));
            case 'onestepcheckout_index_index': // Mageplaza One step check out page
                return $this->encodeJs($this->getCheckoutProductData('2', 'Checkout Page'));
            case 'checkout_onepage_success': // Purchase page
            case 'multishipping_checkout_success':
            case 'mpthankyoupage_index_index': // Mageplaza Thank you page
                return $this->encodeJs($this->getCheckoutSuccessData());
        }

        return $this->encodeJs($this->getDefaultData());
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function getCheckoutSuccessData()
    {
        $order       = $this->_helper->getSessionManager()->getLastRealOrder();

        if ($this->_helper->isEnabledIgnoreOrders($this->_helper->getStoreId()) && $order->getBaseGrandTotal() <= 0) {
            return [];
        }

        $products    = [];
        $productsGa4 = [];
        $items       = $order->getItemsCollection([], true);
        $skuItems    = [];
        $skuItemsQty = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $productSku    = $item->getProduct()->getSku();
            $products[]    = $this->_helper->getProductOrderedData($item);
            $skuItems[]    = $productSku;
            $skuItemsQty[] = $productSku . ':' . (int) $item->getQtyOrdered();
            if ($this->_helper->isEnabledGTMGa4()) {
                $productsGa4[]    = $this->_helper->getGa4ProductOrderedData($item);
            }
        }

        $eCommProdId = [];
        foreach ($products as $product) {
            $eCommProdId[] = $product['id'];
        }

        $data['ecomm_prodid']     = $eCommProdId;
        $data['ecomm_pagetype']   = 'purchase';
        $data['ecomm_totalvalue'] = $this->_helper->calculateTotals($order);
        $createdAt                = $this->timezone->date(
            new DateTime($order->getCreatedAt(), new DateTimeZone('UTC')),
            $this->localeResolver->getLocale(),
            true
        );

        $data['ecommerce'] = [
            'purchase'     => [
                'actionField' => [
                    'id'          => $order->getIncrementId(),
                    'affiliation' => $this->_helper->getAffiliationName(),
                    'order_id'    => $order->getIncrementId(),
                    'subtotal'    => $order->getSubtotal(),
                    'shipping'    => $order->getBaseShippingAmount(),
                    'tax'         => $order->getBaseTaxAmount(),
                    'revenue'     => $this->_helper->calculateTotals($order),
                    'discount'    => $order->getDiscountAmount(),
                    'coupon'      => (string) $order->getCouponCode(),
                    'created_at'  => $createdAt->format('Y-m-d H:i:s'),
                    'items'       => implode(';', $skuItems),
                    'items_qty'   => implode(';', $skuItemsQty)
                ],
                'products'    => $products
            ],
            'currencyCode' => $this->_helper->getCurrentCurrency()
        ];

        if ($this->_helper->isEnabledGTMGa4()) {
            $data['ga4_event']                   = 'purchase';
            $data['ecommerce']['transaction_id'] =  $order->getIncrementId();
            $data['ecommerce']['affiliation']    =  $this->_helper->getAffiliationName();
            $data['ecommerce']['value']          =  $this->_helper->calculateTotals($order);
            $data['ecommerce']['tax']            =  $order->getBaseTaxAmount();
            $data['ecommerce']['shipping']       =  $order->getBaseShippingAmount();
            $data['ecommerce']['currency']       =  $this->_helper->getCurrentCurrency();
            $data['ecommerce']['coupon']         =  (string) $order->getCouponCode();
            $data['ecommerce']['items']          =  $productsGa4;
        }

        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getDefaultData()
    {
        $data = [
            'ecomm_pagetype'   => 'other',
            'ecommerce'        => [
                'currencyCode' => $this->_helper->getCurrentCurrency()
            ]
        ];

        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getProductView()
    {
        $currentProduct = $this->_helper->getGtmRegistry()->registry('product');

        return $this->_helper->getProductDetailData($currentProduct);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getHomeData()
    {
        $data = [
            'ecomm_pagetype' => 'home',
            'ecommerce'      => [
                'currencyCode' => $this->_helper->getCurrentCurrency()
            ]
        ];

        return $data;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getSearchData()
    {
        $data = [
            'ecomm_pagetype' => 'searchresults',
            'ecommerce'      => [
                'currencyCode' => $this->_helper->getCurrentCurrency()
            ]
        ];

        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getCategoryData()
    {
        /** get current breadcrumb path name */
        $path          = $this->_helper->getBreadCrumbsPath();
        $products      = [];
        $productsGa4   = [];
        $result        = [];
        $resultGa4     = [];
        $i             = 0;
        $categoryId    = $this->_registry->registry('current_category')->getId();
        $category      = $this->_category->load($categoryId);
        $storeId       = $category->getStore()->getId();
        $useIdOrSku    = $this->getUseIdOrSku($storeId);
        $sort          = $this->_toolbar->getCurrentOrder();
        $dir           = $this->_toolbar->getCurrentDirection();
        $loadedProduct = $category->getProductCollection()->addAttributeToSelect('*')
            ->setOrder($sort, $dir);

        $loadedProduct->setCurPage($this->getPageNumber())->setPageSize($this->getPageLimit());

        foreach ($loadedProduct as $item) {
            $i++;
            $products[$i]['id']       = $useIdOrSku ? $item->getSku() : $item->getId();
            $products[$i]['name']     = $item->getName();
            $products[$i]['price']    = $this->_helper->getPrice($item);
            $products[$i]['list']     = $category->getName();
            $products[$i]['position'] = $i;
            $products[$i]['category'] = $category->getName();

            if ($this->_helper->isEnabledBrand($item, $storeId)) {
                $products[$i]['brand'] = $this->_helper->getProductBrand($item);
            }

            if ($this->_helper->isEnabledVariant($item, $storeId)) {
                $products[$i]['variant'] = $this->_helper->getColor($item);
            }

            $products[$i]['path']          = implode(' > ', $path) . ' > ' . $item->getName();
            $products[$i]['category_path'] = implode(' > ', $path);
            $result[]                      = $products[$i];

            if ($this->_helper->isEnabledGTMGa4()) {
                $productsGa4[$i]['item_id']        = $useIdOrSku ? $item->getSku() : $item->getId();
                $productsGa4[$i]['item_name']      = $item->getName();
                $productsGa4[$i]['price']          = $this->_helper->getPrice($item);
                $productsGa4[$i]['item_list_name'] = $category->getName();
                $productsGa4[$i]['item_list_id']   = $category->getId();
                $productsGa4[$i]['index']          = $i;
                $productsGa4[$i]['quantity']       = '1';

                if ($this->_helper->isEnabledBrand($item, $storeId)) {
                    $productsGa4[$i]['item_brand'] = $this->_helper->getProductBrand($item);
                }

                if ($this->_helper->isEnabledVariant($item, $storeId)) {
                    $productsGa4[$i]['item_variant'] = $this->_helper->getColor($item);
                }

                if (!empty($path)) {
                    $j = null;
                    foreach ($path as $cat) {
                        $key                   = 'item_category' . $j;
                        $j                     = (int) $j;
                        $productsGa4[$i][$key] = $cat;
                        $j++;
                    }
                }

                $resultGa4[] = $productsGa4[$i];
            }
        }

        $data['ecomm_pagetype'] = 'category';
        $data['ecommerce']      = [
            'currencyCode' => $this->_helper->getCurrentCurrency(),
            'impressions'  => $result
        ];

        if ($this->_helper->isEnabledGTMGa4()) {
            $data['ga4_event']          = 'view_item_list';
            $data['ecommerce']['items'] = $resultGa4;
        }

        return $data;
    }

    /**
     * Get product data in checkout page
     *
     * @param string $step
     *
     * @param string $option
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCheckoutProductData($step, $option = 'Checkout')
    {
        $cart        = $this->_cart;
        // retrieve quote items array
        $items       = $cart->getQuote()->getAllVisibleItems();
        $products    = [];
        $productsGa4 = [];
        $i           = 1;

        if (empty($items)) {
            return [];
        }

        foreach ($items as $item) {
            $products[]    = $this->_helper->getProductCheckOutData($item);
            if ($this->_helper->isEnabledGTMGa4() && $step === '2') {
                $productGa4          = $this->_helper->getProductGa4CheckOutData($item);
                $productGa4['index'] = $i;
                $productsGa4[]       = $productGa4;
                $i++;
            }
        }

        $eCommProdId = [];
        foreach ($products as $product) {
            $eCommProdId[] = $product['id'];
        }

        $data = [
            'event'     => 'checkout',
            'ecommerce' => [
                'checkout' => [
                    'actionField' => [
                        'step'   => $step,
                        'option' => $option
                    ],
                    'products'    => $products
                ]
            ]
        ];

        if ($option === 'Shopping Cart') {
            $data['ecomm_prodid']     = $eCommProdId;
            $data['ecomm_pagetype']   = 'cart';
            $data['ecomm_totalvalue'] =  $cart->getQuote()->getGrandTotal();
        }

        if ($this->_helper->isEnabledGTMGa4() && $step === '2') {
            $data['ga4_event']          = 'begin_checkout';
            $data['ecommerce']['items'] = $productsGa4;
        }

        return $data;
    }
}

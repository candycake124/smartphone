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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\GoogleTagManager\Block\TagManager;

/**
 * Class AnalyticsTag
 * @package Mageplaza\GoogleTagManager\Block\Tag
 */
class AnalyticsTag extends TagManager
{
    /**
     * @return bool
     */
    public function canShowAnalytics()
    {
        return $this->_helper->isEnabled() && $this->_helper->getConfigAnalytics('enabled');
    }

    /**
     * @return mixed
     */
    public function getTagId()
    {
        return trim($this->_helper->getConfigAnalytics('tag_id'));
    }

    /**
     * @return mixed
     */
    public function getSubTagId()
    {
        return $this->_helper->getConfigAnalytics('second_tag_id');
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrency()
    {
        return $this->_helper->getCurrentCurrency();
    }

    /**
     * @return string
     */
    public function isLinkAttribution()
    {
        return $this->_helper->getConfigAnalytics('link_attribution') ? 'true' : 'false';
    }

    /**
     * @return string
     */
    public function isAnonymizeIp()
    {
        return $this->_helper->getConfigAnalytics('ip_anonymization') ? 'true' : 'false';
    }

    /**
     * @return string
     */
    public function isDisplayFeatures()
    {
        return $this->_helper->getConfigAnalytics('display_features') ? 'true' : 'false';
    }

    /**
     * @return bool
     */
    public function isLinker()
    {
        return $this->_helper->getConfigAnalytics('linker')
            && trim($this->_helper->getConfigAnalytics('linker_domain'), ',');
    }

    /**
     * @return string
     */
    public function getLinkerDomains()
    {
        $domains = explode(';', $this->_helper->getConfigAnalytics('linker_domain'));
        foreach ($domains as $key => $domain) {
            if (empty($domain)) {
                unset($domains[$key]);
            }
        }

        return $this->encodeJs($domains);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAnalyticsData()
    {
        $action = $this->getRequest()->getFullActionName();
        $data   = [];
        switch ($action) {
            case 'catalogsearch_result_index':
                $productSearch = $this->_getProductCollection();
                $productSearch->setCurPage($this->getPageNumber())
                    ->setPageSize($this->getPageLimit());
                $products = [];
                $sub      = 1;
                foreach ($productSearch as $product) {
                    $products[] = $this->_helper->getItems($product, 'Search Results', $sub++);
                }

                $data = [
                    'event_name' => ['view_item_list'],
                    'data'       => [
                        'items' => $products,
                    ]
                ];

                return $data;
            case 'catalog_category_view': // Product list page
                /** get current breadcrumb path name */
                $products = [];
                $i        = 0;

                $categoryId = $this->_registry->registry('current_category')->getId();
                $category   = $this->_category->load($categoryId);
                $sort       = $this->_toolbar->getCurrentOrder();
                $dir        = $this->_toolbar->getCurrentDirection();

                $loadedProduct = $category->getProductCollection()
                    ->addAttributeToSelect('*')
                    ->setOrder($sort, $dir)
                    ->setCurPage($this->getPageNumber())
                    ->setPageSize($this->getPageLimit());

                foreach ($loadedProduct as $item) {
                    $products[] = $this->_helper->getItems($item, $category->getName(), ++$i);
                }

                $data = [
                    'event_name' => ['view_item_list'],
                    'data'       => [
                        'items' => $products,
                    ]
                ];

                return $data;
            case 'catalog_product_view': // Product detail view page
                $currentProduct = $this->_helper->getGtmRegistry()->registry('product');
                $products       = $this->_helper->getViewProductData($currentProduct);
                $data           = [
                    'event_name' => ['view_item'],
                    'data'       => [
                        'items' => [$products],
                    ]
                ];

                return $data;
            case 'checkout_index_index':  // Checkout page
                return $this->getCheckoutProductData(false);
            case 'checkout_cart_index':   // Shopping cart
                return $this->getCheckoutProductData(true);
            case 'onestepcheckout_index_index': // Mageplaza One step check out page
                return $this->_helper->moduleIsEnable('Mageplaza_Osc') ? $this->getCheckoutProductData(false) : [];
            case 'checkout_onepage_success': // Purchase page
            case 'multishipping_checkout_success':
            case 'mpthankyoupage_index_index': // Mageplaza Thank you page
                $order    = $this->_helper->getSessionManager()->getLastRealOrder();

                if ($this->_helper->isEnabledIgnoreOrders($this->_helper->getStoreId())
                    && $order->getBaseGrandTotal() <= 0) {
                    return [];
                }

                $products = [];
                $items    = $order->getItemsCollection([], true);
                foreach ($items as $item) {
                    $products[] = $this->_helper->getCheckoutProductData($item);
                }

                $data = [
                    'event_name' => ['purchase'],
                    'data'       => [
                        'transaction_id' => $order->getIncrementId(),
                        'value'          => $this->_helper->calculateTotals($order),
                        'currency'       => $this->_helper->getCurrentCurrency(),
                        'tax'            => $order->getBaseTaxAmount(),
                        'shipping'       => $order->getBaseShippingAmount(),
                        'items'          => $products
                    ]
                ];

                return $data;
        }

        return $data;
    }

    /**
     * @param bool $step
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCheckoutProductData($step)
    {
        // retrieve quote items array
        $items = $this->_cart->getQuote()->getAllVisibleItems();
        if (empty($items)) {
            return [];
        }

        $products = [];
        foreach ($items as $item) {
            $products[] = $this->_helper->getCheckoutProductData($item);
        }

        $data = [
            'event_name' => $step ? ['begin_checkout'] : ['checkout_progress'],
            'data'       => [
                'items'         => $products,
                'coupon'        => $this->_cart->getQuote()->getCouponCode() ?: '',
                'checkout_step' => $step ? 1 : 2,
            ],
            'step'       => [
                'event'           => 'set_checkout_option',
                'checkout_step'   => $step ? 1 : 2,
                'checkout_option' => $step ? 'begin_checkout' : 'checkout_progress'
            ]
        ];

        return $data;
    }
}

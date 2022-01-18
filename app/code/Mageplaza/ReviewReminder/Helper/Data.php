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

namespace Mageplaza\ReviewReminder\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Zend_Serializer_Exception;

/**
 * Class Data
 * @package Mageplaza\ReviewReminder\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'reviewreminder';
    const ANALYTICS_PATH     = 'analytics';

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Url $urlHelper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Url $urlHelper
    ) {
        $this->urlHelper = $urlHelper;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $order
     *
     * @return array
     */
    public function getItemsToReview($order)
    {
        $storeId     = $order->getStoreId();
        $excludeSkus = $this->getOrderConfig('exclude', $storeId);
        $includeSkus = $this->getOrderConfig('include', $storeId);
        $items       = [];
        if ($this->isEnabled($storeId) && $order && $order->getState() === 'complete') {
            foreach ($order->getAllVisibleItems() as $item) {
                $sku = $item->getSku();
                if (count($excludeSkus) && $this->filterSku($sku, $excludeSkus)) {
                    continue;
                }
                if (count($includeSkus) && !$this->filterSku($sku, $includeSkus)) {
                    continue;
                }
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param $field
     * @param null $store
     *
     * @return array
     */
    public function getOrderConfig($field, $store = null)
    {
        $orderConfig = $this->getModuleConfig('order/' . $field, $store);
        if ($orderConfig) {
            return explode(',', $orderConfig);
        }

        return [];
    }

    /**
     * @param $itemSku
     * @param $rules
     *
     * @return bool
     */
    public function filterSku($itemSku, $rules)
    {
        $result = false;
        foreach ($rules as $rule) {
            $rule    = trim($rule);
            $matches = preg_match('/' . $rule . '/i', $itemSku);
            if ($matches) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param $storeId
     *
     * @return array|mixed
     * @throws Zend_Serializer_Exception
     */
    public function getEmailConfig($storeId)
    {
        $emailConfig = $this->getModuleConfig('general/email', $storeId);
        if ($emailConfig) {
            $configs = $this->unserialize($emailConfig);
            $send    = [];
            foreach ($configs as $configId => $config) {
                if ($config['send']) {
                    $configSeconds = 0;
                    $configTimes   = explode(' ', $config['send']);
                    foreach ($configTimes as $configTime) {
                        if (strpos($configTime, 'd') !== false) {
                            $configSeconds += (int) str_replace('d', '', $configTime) * 24 * 60 * 60;
                        }
                        if (strpos($configTime, 'h') !== false) {
                            $configSeconds += (int) str_replace('h', '', $configTime) * 60 * 60;
                        }
                        if (strpos($configTime, 'm') !== false) {
                            $configSeconds += (int) str_replace('m', '', $configTime) * 60;
                        }
                    }
                    $configs[$configId]['send'] = $configSeconds;
                    $send[$configId]            = $configSeconds;
                }
            }

            if (empty($send)) {
                return [];
            }

            array_multisort($send, SORT_DESC, $configs);

            return $configs;
        }

        return [];
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getAnalyticsConfig($store = null)
    {
        $suffix = '';
        $source = $this->getModuleConfig(self::ANALYTICS_PATH . '/source', $store);
        if ($this->getModuleConfig(self::ANALYTICS_PATH . '/enabled', $store) && $source) {
            $suffix .= '?utm_source=' . $source;
            if ($medium = $this->getModuleConfig(self::ANALYTICS_PATH . '/medium', $store)) {
                $suffix .= '&utm_medium=' . $medium;
            }
            if ($name = $this->getModuleConfig(self::ANALYTICS_PATH . '/name', $store)) {
                $suffix .= '&utm_campaign=' . $name;
            }
            if ($term = $this->getModuleConfig(self::ANALYTICS_PATH . '/term', $store)) {
                $suffix .= '&utm_term=' . $term;
            }
            if ($content = $this->getModuleConfig(self::ANALYTICS_PATH . '/content', $store)) {
                $suffix .= '&utm_content=' . $content;
            }
        }

        return $suffix;
    }

    /**
     * Get Url For Email
     *
     * @param string $routePath
     * @param array $routeParams
     *
     * @return string
     */
    public function getUrlEmail($routePath, $routeParams = [])
    {
        return $this->urlHelper->setUseSession(false)->getUrl($routePath, $routeParams);
    }

    /**
     * @param string $storeId
     *
     * @return mixed
     */
    public function onlySendToSubscribed($storeId)
    {
        return $this->getConfigGeneral('send_subscribed_only', $storeId);
    }
}

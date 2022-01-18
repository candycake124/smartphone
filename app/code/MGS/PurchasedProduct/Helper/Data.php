<?php

namespace MGS\PurchasedProduct\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    )
    {
        parent::__construct($context);
    }
    public function getConfig($cfg='')
    {
        if($cfg) return $this->scopeConfig->getValue( $cfg, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $this->scopeConfig;
    }

    public function getResizeImage(){
        $maxWidthHeight = $this->getConfig('purchasedProduct/general/product_image_size');
        $maxWidthHeight = explode('x', $maxWidthHeight);
        return $maxWidthHeight;
    }
    
    public function getSpeed()
    {
        $data = $this->getConfig('purchasedProduct/general/speed');
        if($data == ''|| !settype($data, 'int') || $data == '0'){
            return 8000;
        }
        return $data;
    }

    public function getTimeout()
    {
        $data = $this->getConfig('purchasedProduct/general/timeout');
        if($data == ''|| !settype($data, 'int') || $data == '0'){
            return 3000;
        }
        return $data;
    }
}

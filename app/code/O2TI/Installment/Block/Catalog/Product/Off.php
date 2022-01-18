<?php

namespace O2TI\Installment\Block\Catalog\Product;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Off extends Template
{   

   
    protected $priceCurrency;

    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
        
    }  

    public function getOff(){
        $finalPrice = $this->getCalcForPrice();
        $calc = $this->getDiscounts($finalPrice);
      
        return $calc;
    }

    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    public function getDiscounts($finalPrice){
        $calc = round(($finalPrice*2), 2);
        return $calc;
    }

}
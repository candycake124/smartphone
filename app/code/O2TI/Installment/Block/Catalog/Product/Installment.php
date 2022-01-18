<?php

namespace O2TI\Installment\Block\Catalog\Product;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Installment extends Template
{   

    const MINAMMOUT = 5;
    const MAXINSTALMENT = 12;

   
    protected $_registry = null;
    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->_registry = $registry;
    }

    public function applyFullInstallment($productId){
        // if($ProductInRegistry = $this->_registry->registry('product')){
        //     if($ProductInRegistry->getId() == $productId){
        //          return true;
        //      } else {
        //         return false;
        //      }
        // } else {
        //     return false;
        // }
        return false;
    }

}
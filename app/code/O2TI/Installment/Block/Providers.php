<?php

namespace O2TI\Installment\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\CurrencyFactory;

class Providers extends Template
{   

    const MINAMMOUT = 5;
    const MAXINSTALMENT = 12;

    protected $_storeManager;

   
    protected $_registry = null;
    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_isScopePrivate = true;
        $this->_registry = $registry;
        $this->currencyCode = $currencyFactory->create();
    }

    public function getFormartCurrentCurrencySymbol()
    {    
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currency->getCurrencySymbol();
    }

    public function getFormartCurrentCurrency(){
        
        return  $this->_storeManager->getStore()->getCurrentCurrency()->getOutputFormat();
    }

    public function getLimitByPortionNumber()
    {
        $maxconfig = $this->_scopeConfig->getValue('o2ti_installment/module/max_installment', ScopeInterface::SCOPE_STORE);
        return ($maxconfig < self::MAXINSTALMENT) ? $maxconfig : self::MAXINSTALMENT;
    }

    public function getLimitByPlotPrice()
    {
        $minconfig = $this->_scopeConfig->getValue('o2ti_installment/module/min_installment', ScopeInterface::SCOPE_STORE);
        return ($minconfig > self::MINAMMOUT) ? $minconfig : self::MINAMMOUT;
    }

    public function getTypeInterest() {
        return $this->_scopeConfig->getValue('o2ti_installment/module/type_interest', ScopeInterface::SCOPE_STORE);
    }

    public function getInfoInterest()
    {
        $interest = array();
        $interest['0'] = 0;
        $interest['1'] = $this->_scopeConfig->getValue('o2ti_installment/module/installment_1', ScopeInterface::SCOPE_STORE);
        $interest['2'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_2', ScopeInterface::SCOPE_STORE);
        $interest['3'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_3', ScopeInterface::SCOPE_STORE);
        $interest['4'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_4', ScopeInterface::SCOPE_STORE);
        $interest['5'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_5', ScopeInterface::SCOPE_STORE);
        $interest['6'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_6', ScopeInterface::SCOPE_STORE);
        $interest['7'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_7', ScopeInterface::SCOPE_STORE);
        $interest['8'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_8', ScopeInterface::SCOPE_STORE);
        $interest['9'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_9', ScopeInterface::SCOPE_STORE);
        $interest['10'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_10', ScopeInterface::SCOPE_STORE);
        $interest['11'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_11', ScopeInterface::SCOPE_STORE);
        $interest['12'] =  $this->_scopeConfig->getValue('o2ti_installment/module/installment_12', ScopeInterface::SCOPE_STORE);
       
        return $interest;
    }

}
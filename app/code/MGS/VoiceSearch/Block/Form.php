<?php
namespace MGS\VoiceSearch\Block;
use MGS\VoiceSearch\Model\Saveimage;
use \Magento\Store\Model\StoreManagerInterface;
class Form extends \Magento\Framework\View\Element\Template {
    protected $_registry;
    protected $_mediaURL = "";
    protected $_storeManager;
    public $_resolver, $_micOff, $_micListening;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\Resolver $resolver,
       array $data = []
    ) {        
        $this->_registry = $registry;
        parent::__construct($context, $data);	
        $this->_storeManager = $storeManager;
        $this->_resolver = $resolver;
        $this->_mediaURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        //$imageURL = $this->_mediaURL.Saveimage::UPLOAD_DIR."/";
        $this->_micOff = $this->_scopeConfig->getValue('mgsvoicesearch/general/mic_off', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!empty($this->_micOff)) {
            $this->_micOff = $imageURL.$this->_micOff;
        }
        else {
            $this->_micOff = $this->escapeUrl($this->getViewFileUrl('MGS_VoiceSearch::images/mike_off.svg'));
        }
        $this->_micListening = $this->_scopeConfig->getValue('mgsvoicesearch/general/mic_on', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!empty($this->_micListening)) {
            $this->_micListening = $imageURL.$this->_micListening;
        }
        else {
            $this->_micListening = $this->escapeUrl($this->getViewFileUrl('MGS_VoiceSearch::images/listening.png'));
        }
    }
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
}
<?php

namespace O2TI\Installment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
 
class Data extends AbstractHelper
{
   
    private $httpContext;
 
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) 
    {
        parent::__construct($context);
    }

    public function getOffAmount($OldPrice, $NewPrice)
    {
        
        if($OldPrice != $NewPrice) {
            return round((($OldPrice-$NewPrice)/$OldPrice)*100);
        } else {
            return $this;
        }
    }
}
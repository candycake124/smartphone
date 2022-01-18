<?php

namespace O2TI\Installment\Plugin\Catalog\Model;

class FinalPrice
{
	function aroundToHtml($subject, callable $proceed)
    {	
    	$installmentBlockHtml = "";
    	$PointsBlockHtml = "";
		$returnHtml = $proceed();
		$type = $subject->getSaleableItem()->getTypeId();
		if ($type === 'simple' || $type === 'configurable' || $type === 'bundle' || $type === 'grouped') {
			$finalPrice = $subject->getSaleableItem()->getPriceInfo()->getPrice('final_price')->getValue();
			$id =  $subject->getSaleableItem()->getId();
			$installmentBlockHtml = $subject->getLayout()
							->createBlock('O2TI\Installment\Block\Catalog\Product\Installment')
							->setTemplate('O2TI_Installment::catalog/installments.phtml')
							->setProductId($id)
							->setCalcForPrice($finalPrice)
							->toHtml();

			$PointsBlockHtml = $subject->getLayout()
							->createBlock('O2TI\Installment\Block\Catalog\Product\Off')
							->setTemplate('O2TI_Installment::catalog/off.phtml')
							->setProductId($id)
							->setCalcForPrice($finalPrice)
							->toHtml();
		}

	
		
		return $returnHtml.$installmentBlockHtml.$PointsBlockHtml;
    }

}
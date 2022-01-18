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

namespace Mageplaza\GoogleTagManager\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class Linkers
 * @package Mageplaza\GoogleTagManager\Model\Config\Backend
 */
class Linkers extends Value
{
    /**
     * @return Value|void
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        if (!empty($this->getValue())) {
            $valueArray = explode(',', $this->getValue());
            foreach ($valueArray as $value) {
                if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                    throw new ValidatorException(__('Not a valid URL.'));
                }
            }
        }
        parent::beforeSave();
    }
}

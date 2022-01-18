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

namespace Mageplaza\ReviewReminder\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Framework\DataObject;

/**
 * Class Action
 * @package Mageplaza\ReviewReminder\Block\Adminhtml\Grid\Column\Renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $actions = [
            [
                'url'     => $this->getUrl('reviewreminder/*/preview', ['id' => $row->getId()]),
                'popup'   => true,
                'caption' => __('Preview')
            ],
            [
                'url'     => $this->getUrl('reviewreminder/*/delete', ['id' => $row->getId()]),
                'caption' => __('Delete')
            ],
            [
                'url'     => $this->getUrl('reviewreminder/*/sentnow', ['id' => $row->getId()]),
                'caption' => __('Send Now')
            ]
        ];

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}

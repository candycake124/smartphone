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

namespace Mageplaza\ReviewReminder\Block\Adminhtml\Config\Backend;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Config\Model\Config\Source\Email\Identity;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Data\Form\Element\Factory;

/**
 * Class Email
 * @package Mageplaza\ReviewReminder\Block\Adminhtml\Config\Backend
 */
class Email extends AbstractFieldArray
{
    /**
     * @var Factory
     */
    private $elementFactory;

    /**
     * @var Identity
     */
    private $emailIdentity;

    /**
     * @var CollectionFactory
     */
    private $templatesFactory;

    /**
     * @var Config
     */
    private $emailConfig;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param CollectionFactory $templatesFactory
     * @param Identity $emailIdentity
     * @param Config $emailConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        CollectionFactory $templatesFactory,
        Identity $emailIdentity,
        Config $emailConfig,
        array $data = []
    ) {
        $this->elementFactory   = $elementFactory;
        $this->emailIdentity    = $emailIdentity;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig      = $emailConfig;

        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    public function _construct()
    {
        $this->addColumn('send', ['label' => __('Send after'), 'style' => 'width:150px']);
        $this->addColumn('sender', ['label' => __('Sender')]);
        $this->addColumn('template', ['label' => __('Email template')]);
        $this->_addAfter       = false;
        $this->_addButtonLabel = __('More');

        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return mixed|string
     * @throws Exception
     */
    public function renderCellTemplate($columnName)
    {
        $options = [];
        if (isset($this->_columns[$columnName])) {
            switch ($columnName) {
                case 'sender':
                    $options = $this->emailIdentity->toOptionArray();
                    break;
                case 'template':
                    $options = $this->getEmailTemplateOption();
                    break;
                default:
                    break;
            }
            if (!empty($options)) {
                $element = $this->elementFactory->create('select');
                $element->setForm($this->getForm())
                    ->setName($this->_getCellInputElementName($columnName))
                    ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
                    ->setValues($options)
                    ->setStyle('width:110px');

                return str_replace("\n", '', $element->getElementHtml());
            }
        }

        return parent::renderCellTemplate($columnName);
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    private function getEmailTemplateOption()
    {
        $collection   = $this->templatesFactory->create()->load();
        $emailOptions = $collection->toOptionArray();

        $templateIds = [
            'mageplaza_review_reminder_email_template'
        ];
        foreach ($templateIds as $templateId) {
            $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
            $templateLabel = __('%1 (Default)', $templateLabel);
            array_unshift($emailOptions, ['value' => $templateId, 'label' => $templateLabel]);
        }

        return $emailOptions;
    }
}

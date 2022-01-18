<?php
/**
* Copyright © 2016 SW-THEMES. All rights reserved.
*/

namespace MGS\Social\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Bibhu\Customattribute\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mgs_social_fid'
            );
            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mgs_social_ftoken'
            );
            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mgs_social_gid'
            );
            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mgs_social_gtoken'
            );
            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mgs_social_tid'
            );
            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'mgs_social_ttoken'
            );
        }

        $setup->endSetup();
    }
}
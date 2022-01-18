<?php

namespace MGS\Testimonial\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0.2') < 0) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('mgs_testimonial_store'))
                ->addColumn(
                    'testimonial_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Testimonial Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addIndex(
                    $setup->getIdxName('mgs_testimonial_store', ['testimonial_id']),
                    ['testimonial_id']
                )
                ->addIndex(
                    $setup->getIdxName('mgs_testimonial_store', ['store_id']),
                    ['store_id']
                )
                ->addForeignKey(
                    $setup->getFkName('mgs_testimonial_store', 'testimonial_id', 'testimonial', 'testimonial_id'),
                    'testimonial_id',
                    $setup->getTable('testimonial'),
                    'testimonial_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName('mgs_testimonial_store', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $setup->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Testimonial Store');
            $setup->getConnection()->createTable($table); 
        }

        $setup->endSetup();
    }
}

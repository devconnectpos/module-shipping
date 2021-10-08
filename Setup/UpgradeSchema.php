<?php

namespace SM\Shipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->addShippingAdditionalDataTable($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function addShippingAdditionalDataTable(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('sm_shipping_carrier_additional_data');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
                'ID'
            )
            ->addColumn(
                'carrier_code',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Carrier Code'
            )->addColumn(
                'additional_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Additional Data JSON'
            );

        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}

<?php

namespace SM\Shipping\Model\ResourceModel\ShippingCarrierAdditionalData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \SM\Shipping\Model\ShippingCarrierAdditionalData::class,
            \SM\Shipping\Model\ResourceModel\ShippingCarrierAdditionalData::class
        );
    }
}

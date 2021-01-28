<?php

namespace SM\Shipping\Model\ResourceModel;

class ShippingCarrierAdditionalData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    protected function _construct()
    {
        $this->_init('sm_shipping_carrier_additional_data', 'id');
    }
}

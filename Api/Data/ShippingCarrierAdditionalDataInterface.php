<?php

namespace SM\Shipping\Api\Data;

interface ShippingCarrierAdditionalDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CARRIER_CODE = 'carrier_code';
    const ADDITIONAL_DATA = 'additional_data';
    
    /**
     * @return string
     */
    public function getCarrierCode();
    
    /**
     * @param string $code
     * @return $this
     */
    public function setCarrierCode($code);
    
    /**
     * @return array
     */
    public function getAdditionalData();
    
    /**
     * @param string|array $data
     * @return $this
     */
    public function setAdditionalData($data);
}

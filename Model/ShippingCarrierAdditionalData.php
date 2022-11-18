<?php

namespace SM\Shipping\Model;

use Magento\Framework\Model\AbstractModel;
use SM\Shipping\Api\Data\ShippingCarrierAdditionalDataInterface;

class ShippingCarrierAdditionalData extends AbstractModel implements ShippingCarrierAdditionalDataInterface
{
    protected function _construct()
    {
        $this->_init(\SM\Shipping\Model\ResourceModel\ShippingCarrierAdditionalData::class);
    }

    /**
     * @param $code
     * @return $this
     */
    public function loadByCarrierCode($code)
    {
        $this->load($code, self::CARRIER_CODE);
        return $this;
    }

    public function getCarrierCode()
    {
        return $this->getData(self::CARRIER_CODE);
    }

    public function setCarrierCode($code)
    {
        return $this->setData(self::CARRIER_CODE, $code);
    }

    public function getAdditionalData()
    {
        $additionalData = $this->getData(self::ADDITIONAL_DATA);

        if (empty($additionalData)) {
            return [];
        }

        return json_decode($additionalData, true);
    }

    public function setAdditionalData($data)
    {
        if (is_array($data)) {
            return $this->setData(self::ADDITIONAL_DATA, json_encode($data));
        }
        return $this->setData(self::ADDITIONAL_DATA, $data);
    }
}

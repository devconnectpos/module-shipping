<?php

namespace SM\Shipping\Helper;

class Shipping
{
    /**
     * @var \SM\Integrate\Helper\Data
     */
    private $integrateHelper;
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    public function __construct(\SM\Integrate\Helper\Data $integrateHelper, \Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->integrateHelper = $integrateHelper;
        $this->objectManager = $objectManager;
    }

    /**
     * function get shipping method allowed
     *
     * @return array
     */
    public function getAllowedShippingMethods()
    {
        $allowedMethods = ['retailshipping', 'smstorepickup', 'dhl', 'ups', 'usps', 'fedex', 'flatrate', 'tablerate', 'freeshipping'];

        if ($this->integrateHelper->isIntegrateMageShip()) {
	        $shipperCarrier = $this->objectManager->create('Maurisource\MageShip\Model\Carrier');
            array_push($allowedMethods, $shipperCarrier->getCarrierCode());
        }

        if ($this->integrateHelper->isIntegrateShipperHQ()) {
            $shipperCarrier = $this->objectManager->create('ShipperHQ\Shipper\Model\Carrier\Shipper');
            array_push($allowedMethods, $shipperCarrier->getCarrierCode());
        }

        if ($this->integrateHelper->isIntegrateMatrixRate()) {
            $shipperCarrier = $this->objectManager->create('WebShopApps\MatrixRate\Model\Carrier\Matrixrate');
            array_push($allowedMethods, $shipperCarrier->getCarrierCode());
        }

        if ($this->integrateHelper->isExistCustomShipping()) {
            $shipperCarrier = $this->objectManager->create('Magecomp\Customshipping\Model\Carrier');
            array_push($allowedMethods, $shipperCarrier->getCarrierCode());
        }

        return $allowedMethods;
    }
}

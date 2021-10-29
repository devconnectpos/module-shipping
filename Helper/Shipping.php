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

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shipconfig;

    public function __construct(
        \SM\Integrate\Helper\Data $integrateHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Shipping\Model\Config $shipconfig
    ) {
        $this->integrateHelper = $integrateHelper;
        $this->objectManager = $objectManager;
        $this->shipconfig = $shipconfig;
    }

    /**
     * function get shipping method allowed
     *
     * @return array
     */
    public function getAllowedShippingMethods()
    {
        $carriers = $this->shipconfig->getAllCarriers();
        $allowedMethods = [];

        foreach($carriers as $carrierCode => $carrierModel) {
            $allowedMethods[] = $carrierCode;
        }

        return $allowedMethods;
    }
}

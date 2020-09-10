<?php

namespace SM\Shipping\Plugin;

use SM\Shipping\Model\Carrier\RetailShipping;

class CustomShipping
{
    /**
     * @var \Magecomp\Customshipping\Helper\Data
     */
    protected $customShippingHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    public function __construct(\Magento\Framework\Registry $registry, \Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->registry = $registry;
        $this->objectManager = $objectManager;
    }
    
    public function aroundAroundCollect(
        \Magecomp\Customshipping\Plugin\Quote\Address\Total\ShippingPlugin $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Address\Total\Shipping $shipping,
        callable $shippingProceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if (!\SM\Sales\Repositories\OrderManagement::$FROM_API || !class_exists('\Magecomp\Customshipping\Model\Carrier')) {
            return $proceed($shipping, $shippingProceed, $quote, $shippingAssignment, $total);
        }
        
        $address = $shippingAssignment->getShipping()->getAddress();
        $method = $address->getShippingMethod();
        
        if (!$this->getCustomShippingHelper()->IsCustomShippingAllowedForFrontend()
            || $address->getAddressType() != \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
            || strpos($method, \Magecomp\Customshipping\Model\Carrier::CODE) === false) {
            return $proceed($shipping, $shippingProceed, $quote, $shippingAssignment, $total);
        }
        
        if ($method) {
            $shippingAssignment->getShipping()->setMethod($method);
            $address->setShippingMethod($method);
            
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $method) {
                    $price = $this->registry->registry(RetailShipping::RETAIL_SHIPPING_AMOUNT_KEY);
                    $rate->setPrice($price);
                    $rate->setCost($price);
                }
            }
        }
        
        return $proceed($shipping, $shippingProceed, $quote, $shippingAssignment, $total);
    }
    
    /**
     * @return \Magecomp\Customshipping\Helper\Data|mixed
     */
    protected function getCustomShippingHelper()
    {
        if (!$this->customShippingHelper) {
            $this->customShippingHelper = $this->objectManager->get('Magecomp\Customshipping\Helper\Data');
        }
        
        return $this->customShippingHelper;
    }
}

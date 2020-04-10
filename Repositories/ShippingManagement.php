<?php
/**
 * Created by PhpStorm.
 * User: xuantung
 * Date: 9/14/18
 * Time: 2:11 PM
 */

namespace SM\Shipping\Repositories;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use SM\Core\Api\Data\ShippingMethod;
use SM\Shipping\Helper\Shipping as ShippingHelper;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

class ShippingManagement extends ServiceAbstract
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;
    /** @var \Magento\Shipping\Model\Config $shippingConfig */
    protected $shippingConfig;

    protected $objectManager;
	/**
	 * @var ShippingHelper
	 */
	private $shippingHelper;
	/**
	 * @var CartManagementInterface
	 */
	private $cartManagement;
	/**
	 * @var CartRepositoryInterface
	 */
	private $cartRepository;
	/**
	 * @var ProductRepositoryInterface
	 */
	private $productRepository;
	/**
	 * @var ShipmentEstimationInterface
	 */
	private $shipmentEstimation;
	
	/**
	 * ShippingManagement constructor.
	 *
	 * @param \Magento\Framework\App\RequestInterface $requestInterface
	 * @param \SM\XRetail\Helper\DataConfig $dataConfig
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param ScopeConfigInterface $scopeConfig
	 * @param \Magento\Shipping\Model\Config $shippingConfig
	 * @param ObjectManagerInterface $objectManager
	 * @param ShippingHelper $shippingHelper
	 * @param CartManagementInterface $cartManagement
	 * @param CartRepositoryInterface $cartRepository
	 * @param ProductRepositoryInterface $productRepository
	 * @param ShipmentEstimationInterface $shipmentEstimation
	 */
    public function __construct(
	    RequestInterface $requestInterface,
	    DataConfig $dataConfig,
	    StoreManagerInterface $storeManager,
	    ScopeConfigInterface $scopeConfig,
	    Config $shippingConfig,
	    ObjectManagerInterface $objectManager,
	    ShippingHelper $shippingHelper,
		CartManagementInterface $cartManagement,
		CartRepositoryInterface $cartRepository,
		ProductRepositoryInterface $productRepository,
		ShipmentEstimationInterface $shipmentEstimation
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shippingConfig = $shippingConfig;
	    $this->objectManager = $objectManager;
	    $this->shippingHelper = $shippingHelper;
	    $this->cartManagement = $cartManagement;
	    $this->cartRepository = $cartRepository;
	    $this->productRepository = $productRepository;
	    $this->shipmentEstimation = $shipmentEstimation;
	    parent::__construct($requestInterface, $dataConfig, $storeManager);
    }
	
	/**
	 * @return array
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
    public function getShippingMethods()
    {
        $methods = array();
        $activeCarriers = $this->shippingConfig->getAllCarriers();
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            if (in_array($carrierCode, $this->shippingHelper->getAllowedShippingMethods())) {
	            $carrierTitle = $this->scopeConfig->getValue(
	                'carriers/' . $carrierCode . '/title',
	                ScopeInterface::SCOPE_STORE
	            );
	            
	            if (!$carrierTitle) {
	            	$carrierTitle = $this->scopeConfig->getValue(
			            'carriers/' . $carrierCode . '/name',
			            ScopeInterface::SCOPE_STORE
		            );
	            }
	
	            if (!$carrierTitle) {
		            $carrierTitle = $carrierCode;
	            }
	
	            $shipping_method = new ShippingMethod();
	            $shipping_method->setData('code', $carrierCode);
	            $shipping_method->setData('label', $carrierTitle);
	            $shipping_method->setData('is_active', $carrierModel->getConfigData('active'));
	            $shipping_method->setData('magento_active', $carrierModel->getConfigData('active'));
	            $shipping_method->setData('showmethod', $carrierModel->getConfigData('showmethod'));
	            $methods[] = $shipping_method;
            }
        }
        if ($this->getSearchCriteria()->getData('currentPage') > 1) {
            return $this->getSearchResult()->setItems([])->getOutput();
        }

        return $this->getSearchResult()
            ->setSearchCriteria($this->getSearchCriteria())
            ->setItems($methods)
            ->setTotalCount(count($methods))
            ->setMessageError([])
            ->getOutput();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function updateShippingMethods()
    {
        $data = $this->getRequest()->getParams();
        $items = array();
        foreach ($data['methods'] as $method) {
            $item = new ShippingMethod();
            $item->setData('code', $method['code']);
            $item->setData('label', $method['label']);
            $item->setData('is_active', $method['is_active']);
            $item->setData('magento_active', $method['magento_active']);
            $item->setData('showmethod', $method['showmethod']);
            $items[] = $item;
        }
        return $this->getSearchResult()
            ->setItems($items)
            ->setTotalCount(1)
            ->setLastPageNumber(1)
            ->getOutput();
    }
	
	/**
	 * @return array
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	public function getMultiShippingRates()
    {
    	$data = $this->getRequest()->getParams();
    	
    	if (!isset($data['shipments'])) {
		    return $this->getSearchResult()
			    ->setItems([])
			    ->setTotalCount(0)
			    ->setLastPageNumber(1)
			    ->getOutput();
	    };
	
    	$shipments = [];
	    foreach ($data['shipments'] as $shipment) {
		    /** @var \Magento\Quote\Model\Quote $quote */
	    	$quote = $this->cartRepository->get($this->cartManagement->createEmptyCart());
		    $shippingAddress = $this->objectManager->create(
			    \Magento\Quote\Model\Quote\Address::class
		    )->setData(
			    $shipment['shipping_address']
		    )->setAddressType(
			    \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING
		    );
		    $quote->setShippingAddress($shippingAddress);
		    foreach ($shipment['items'] as $config) {
		    	$config['qty'] = (double)$config['qty'];
			    $quote->addProduct($this->productRepository->getById($config['product_id']), new \Magento\Framework\DataObject($config));
		    }
		    $quote->getShippingAddress()->setLimitCarrier($shipment['carrier']);
		    $this->cartRepository->save($quote->collectTotals());
			$this->shipmentEstimation->estimateByExtendedAddress($quote->getId(), $quote->getShippingAddress());

		    $rates = $quote->getShippingAddress()->getGroupedAllShippingRates();
		    
		    $arr = [];
		    foreach ($rates as $rate) {
			    foreach ($rate as $item) {
				    $rateData = $item->getData();
				    $arr[] = $rateData;
			    }
		    }
			$shipment['shipping_rate'] = $arr;
		    $shipments[] = $shipment;
		    $this->cartRepository->delete($quote);
    	}
	
	    return $this->getSearchResult()
		    ->setItems($shipments)
		    ->setTotalCount(count($shipments))
		    ->setMessageError([])
		    ->getOutput();
    }
}

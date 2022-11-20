<?php

namespace SM\Shipping\Block\Adminhtml\Order\Shipping;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Model\Order;
use SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory;

class Information
{
    const OUTLET_ID = "id";
    const ENABLE_VALUE = '1';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param AbstractOrder $subject
     * @param $result
     * @return Order
     */
    public function afterGetOrder(AbstractOrder $subject, $result): Order
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter(self::OUTLET_ID, $result->getOutletId());

        foreach ($collection as $item) {
            if ($item->getData('allow_click_and_collect') === self::ENABLE_VALUE
                && $item->getData('is_active') === self::ENABLE_VALUE
            )
            {
                $result['outlet_info'] = $item->getData('street') . ', ' . $item->getData('city')
                    . ', ' . $item->getData('country_id');
            }
        }

        return $result;
    }
}

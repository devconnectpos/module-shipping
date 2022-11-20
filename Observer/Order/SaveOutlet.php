<?php

namespace SM\Shipping\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class SaveOutlet implements ObserverInterface
{
    const OUTLET_ID = 'outlet_id';
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var Order
     */
    private Order $order;
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param LoggerInterface $logger
     * @param Order $order
     */
    public function __construct(
        LoggerInterface          $logger,
        Order                    $order,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->logger = $logger;
        $this->order = $order;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');
        $outletId = $quote->getOutletId();

        if ($outletId) {
            $order->setData(self::OUTLET_ID, $outletId);
            $this->orderRepository->save($order);
        }
    }
}

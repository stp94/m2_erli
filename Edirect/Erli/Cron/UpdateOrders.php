<?php
/**
 * Created in PhpStorm.
 * by marcin.paul
 * on 07, styczeń, 10:54, 2021
 * @category Edirect
 * @package  Edirect_Module
 * @author   marcin.paul <marcin.paul@edirect24.pl>
 * @projekt   Erli
 * @copyright Copyright (C) 2020 marcin.paul
 **/

namespace Edirect\Erli\Cron;

use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Edirect\Erli\Helper\Data;
use Edirect\Erli\Helper\Erli;
use Edirect\Erli\Helper\Order;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order as OrderStatus;

/**
 * Update Orders Cron
 * Class UpdateOrders
 */
class UpdateOrders
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var CollectionFactory
     */
    protected $orderCollection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Erli
     */
    protected $erliHelper;

    /**
     * @var Order
     */
    protected $orderHelper;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * CreateOrders constructor.
     * @param LoggerInterface $loggerInterface
     * @param Json $json
     * @param CollectionFactory $orderCollection
     * @param ResourceConnection $resource
     * @param Data $helper
     * @param Erli $erliHelper
     * @param Order $orderHelper
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        Json $json,
        CollectionFactory $orderCollection,
        ResourceConnection $resource,
        Erli $erliHelper,
        Data $helper,
        Order $orderHelper,
        TimezoneInterface $timezoneInterface
    ) {
        $this->logger = $loggerInterface;
        $this->json = $json;
        $this->orderCollection = $orderCollection;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->erliHelper = $erliHelper;
        $this->orderHelper = $orderHelper;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        //if (!$this->state->getAreaCode()) {
            //$this->state->setAreaCode(Area::AREA_FRONTEND);
        //}

        $collection = $this->orderCollection
            ->create()
            /*->addFieldToFilter('status', ['nin' =>
                [
                    OrderStatus::STATE_COMPLETE,
                    OrderStatus::STATE_CLOSED,
                    OrderStatus::STATE_CANCELED
                ]
            ])*/
            ->addFieldToFilter('erli_id', ['neq' => null]);

        $collection->getSelect()->where('updated_at > erli_updated_at');

        foreach ($collection as $order) {

            $erliStatus = $this->getErliStatus($order->getStatus());

            if ($erliStatus) {

                if ($order->getTracksCollection()->getSize() > 0) {

                    $trackingNumber = $order->getTracksCollection()->getFirstItem()->getTrackNumber();
                    $vendor = $this->getVendor($order->getShippingMethod());
                    $response = $this->erliHelper->callCurlPatch(
                        'orders/'.$order->getErliId(),
                        ['deliveryTracking' => ['status' => $erliStatus,
                            'vendor' => $vendor,
                            'trackingNumber' => $trackingNumber]
                        ],
                        'order',
                        $order->getIncrementId()
                    );

                    if ($response['status'] == 202) {
                        $this->updateOrderErliUpdatedAt($order->getId());
                    }

                } else {

                    $response = $this->erliHelper->callCurlPatch(
                        'orders/'.$order->getErliId(),
                        ['deliveryTracking' => ['status' => $erliStatus]],
                        'order',
                        $order->getIncrementId()
                    );

                    if ($response['status'] == 202) {
                        $this->updateOrderErliUpdatedAt($order->getId());
                    }

                }
            }

        }

        return $this;
    }

    /**
     * @param $status
     * @return string
     */
    public function getErliStatus($status)
    {
        $preparingStatus = $this->helper->getDeliveryStatus('preparing');
        $waitingForCourierStatus = $this->helper->getDeliveryStatus('waitingForCourier');
        $sentStatus = $this->helper->getDeliveryStatus('sent');
        $erliStatus = '';

        switch ($status) {
            case $preparingStatus:
                $erliStatus = "preparing";
                break;
            case $waitingForCourierStatus:
                $erliStatus = "waitingForCourier";
                break;
            case $sentStatus:
                $erliStatus = "sent";
                break;
        }

        return $erliStatus;
    }

    /**
     * @return mixed
     */
    protected function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @param $orderId
     */
    public function updateOrderErliUpdatedAt($orderId)
    {
        $connection = $this->getConnection();
        $connection->update(
            'sales_order',
            ['erli_updated_at' => $this->timezoneInterface->date()->format('Y-m-d H:i:s')],
            ['entity_id = ?' => $orderId]
        );
    }

    /**
     * @param $shippingMethod
     * @return string
     */
    public function getVendor($shippingMethod)
    {
        $method = explode('_', $shippingMethod);
        $erliMethod = $this->orderHelper->getErliShippingMethod($method[0]);
        $erliMethod = rtrim($erliMethod, 'Cod');
        $vendors = explode(',', $this->helper->getDeliveryTrackingVendor());

        if (in_array($erliMethod, $vendors)) {
            return $erliMethod;
        }

        return 'other';
    }
}

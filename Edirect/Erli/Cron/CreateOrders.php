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
use Magento\Framework\App\ResourceConnection;
use Edirect\Erli\Helper\Erli;
use Edirect\Erli\Helper\Order;

/**
 * Create New Orders Cron
 * Class CreateOrders
 */
class CreateOrders
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
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Erli
     */
    protected $erliHelper;

    /**
     * @var Order
     */
    protected $erliOrderHelper;

    /**
     * CreateOrders constructor.
     * @param LoggerInterface $loggerInterface
     * @param Json $json
     * @param ResourceConnection $resource
     * @param Erli $erliHelper
     * @param Order $erliOrderHelper
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        Json $json,
        ResourceConnection $resource,
        Erli $erliHelper,
        Order $erliOrderHelper
    ) {
        $this->logger = $loggerInterface;
        $this->json = $json;
        $this->resource = $resource;
        $this->erliHelper = $erliHelper;
        $this->erliOrderHelper = $erliOrderHelper;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        //if (!$this->state->getAreaCode()) {
            //$this->state->setAreaCode(Area::AREA_FRONTEND);
        //}

        $inbox = $this->erliHelper->callCurlGet('inbox');

        if (!empty($inbox)) {

            $orderData = $this->json->unserialize($inbox['body']);

            if ($orderData) {

                foreach ($orderData as $data) {

                    $orderCreated[$data['payload']['id']] = $data['payload'];
                    $ids[] = $data['id'];

                }

                if (count($ids) > 0) {
                    $this->erliHelper->callCurlPost('inbox/mark-read', ['lastMessageId' => end($ids)]);
                }

                if (count($orderCreated) > 0) {

                    foreach ($orderCreated as $order) {
                        $this->erliOrderHelper->createOrder($order);
                    }

                }

            }

        }

        $problematicOrders = $this->getConnection()->fetchCol('SELECT order_id FROM ed_erli_problematic_orders;');

        if (count($problematicOrders) > 0) {

            foreach ($problematicOrders as $orderId) {

                $problematicOrder = $this->erliHelper->callCurlGet('orders/' . $orderId, 'order', $orderId);

                if (!empty($problematicOrder)) {

                    $responseStatus = $this->json->unserialize($problematicOrder['status']);

                    if ($responseStatus >= 200 && $responseStatus <= 299) {
                        $orderData = $this->json->unserialize($problematicOrder['body']);

                        if ($orderData) {
                            $this->erliOrderHelper->createOrder($orderData);
                        }

                    } else {

                        continue;

                    }

                }
            }

        }

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getConnection()
    {
        return $this->resource->getConnection();
    }
}

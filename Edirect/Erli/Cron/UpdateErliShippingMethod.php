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
use Edirect\Erli\Helper\Erli;
use Edirect\Erli\Model\ErliShippingMethod;
use Edirect\Erli\Model\ResourceModel\ErliShippingMethod\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Update Erli Shipping Method
 * Class CreateOrders
 */
class UpdateErliShippingMethod
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Erli
     */
    protected $erliHelper;

    /**
     * @var ErliShippingMethod
     */
    protected $erliShippingMethodModel;

    /**
     * @var CollectionFactory
     */
    protected $erliCollectionFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * UpdateErliShippingMethod constructor.
     * @param LoggerInterface $loggerInterface
     * @param Erli $erliHelper
     * @param ErliShippingMethod $erliShippingMethodModel
     * @param CollectionFactory $erliCollectionFactory
     * @param Json $json
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        Erli $erliHelper,
        ErliShippingMethod $erliShippingMethodModel,
        CollectionFactory $erliCollectionFactory,
        Json $json
    ) {
        $this->logger = $loggerInterface;
        $this->erliHelper = $erliHelper;
        $this->erliShippingMethodModel = $erliShippingMethodModel;
        $this->erliCollectionFactory = $erliCollectionFactory;
        $this->json = $json;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $erliShippingMethods = $this->erliHelper->callCurlGet('dictionaries/deliveryMethods');
        $shippingMethods = $this->json->unserialize($erliShippingMethods['body']);

        foreach ($shippingMethods as $method) {

            $methodId = $method['id'];

            if ($this->getIsMethodExist($methodId) == 0) {

                $this->erliShippingMethodModel
                    ->setMethodId($method['id'])
                    ->setMethodName($method['name'])
                    ->setCod(($method['cod']) ? 1 : 0)
                    ->setCreatedAt(date('Y-m-d H:i:s'))
                    ->save();

                $this->erliShippingMethodModel->unsetData();

            }

        }

        return $this;
    }

    public function getIsMethodExist($methodId)
    {
        $collection = $this->erliCollectionFactory->create();
        $collection->addFieldToFilter('method_id', $methodId);

        return $collection->getSize();
    }
}

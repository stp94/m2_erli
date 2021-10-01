<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 11.01.2021, 12:01, 2021
 * @author   biuro@edirect24.pl
 * @projekt   Erli
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Helper Class Product
 */
class Product extends AbstractHelper
{

    /**
     * @var Erli
     */
    protected $erliHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $action;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Product constructor.
     * @param Erli $erliHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Erli $erliHelper,
        LoggerInterface $logger,
        Action $action,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->erliHelper = $erliHelper;
        $this->action = $action;
        $this->storeManager =  $storeManager;
    }

    /**
     * @param $productId
     * @param $productData
     */
    public function createErliProduct($productId, $productData)
    {
        try {
            $query = 'products/'.$productId;
            $createErliProduct = $this->erliHelper->callCurlPost($query, $productData, 'product');

            return $createErliProduct['status'];

        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    public function updateErliProduct($productId, $productData)
    {
        try {
            $query = 'products/'.$productId;
            $updateErliProduct = $this->erliHelper->callCurlPatch($query, $productData, 'product', $productId);

            if ($updateErliProduct && empty($updateErliProduct['body'])) {
                $this->action->updateAttributes(
                    [$productId],
                    ['erli_update_flag' => 0],
                    $this->storeManager->getStore()->getId()
                );
            }

            return $updateErliProduct['body'];

        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param $productId
     */
    public function getErliProduct($productId)
    {
        try {
            $getProduct = $this->erliHelper->callCurlGet('products/' . $productId, 'product');

            return $getProduct['status'];

        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}

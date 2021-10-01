<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 19.01.2021, 15:38, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Model\Hooks;

use Edirect\Erli\Api\HooksInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Attribute\Source\Status as Status;

/**
 * Check Buy Ability from Erli
 * Class CheckBuyAbility
 */
class CheckBuyAbility implements HooksInterface
{

    /**
     * @var Request
     */
    protected $restRequest;

    /**
     * @var Product
     */
    protected $productActionObject;

    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var Json
     */
    protected $json;

    /**
     * CheckBuyAbility constructor.
     * @param Request $restRequest
     * @param Product $productActionObject
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param Json $json
     */
    public function __construct(
        Request $restRequest,
        Product $productActionObject,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        Json $json
    ) {
        $this->restRequest = $restRequest;
        $this->productActionObject = $productActionObject;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->json = $json;
    }

    /**
     * @return array
     */
    public function checkBuyAbility()
    {
        $response = [];
        $postData = $this->json->unserialize($this->restRequest->getContent());

        foreach ($postData as $key => $item) {
            $productData = $this->productActionObject->getAttributeRawValue($item['productId'], 'sku', 0);
            $status = $this->productActionObject->getAttributeRawValue($item['productId'], 'status', 0);

            $salableQty = $this->getSalableQuantityDataBySku->execute($productData['sku']);

            if ($status && $status == Status::STATUS_ENABLED) {
                if ($salableQty && count($salableQty) > 0) {
                    $response[] = ['productId' => $item['productId'], 'status' => 'active', 'stock' => (int)$salableQty[0]['qty']];
                } else {
                    $response[] = ['productId' => $item['productId'], 'status' => 'inactive'];
                }
            } else {
                $response[] = ['productId' => $item['productId'], 'status' => 'inactive'];
            }
        }

        return $response;
    }
}

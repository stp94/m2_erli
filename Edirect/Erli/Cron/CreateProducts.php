<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 18.01.2021, 09:49, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/


namespace Edirect\Erli\Cron;

use Edirect\Erli\Helper\Erli as HelperErli;
use Edirect\Erli\Helper\Data as HelperData;
use Edirect\Erli\Helper\Product as HelperProduct;
use Psr\Log\LoggerInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

/**
 * Cron to create Erli Products
 * Class CreateProducts
 */
class CreateProducts
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HelperErli
     */
    protected $helperErli;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var
     */
    protected $helperProduct;

    /**
     * Test constructor.
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        HelperErli $helperErli,
        HelperData $helperData,
        HelperProduct $helperProduct
    ) {
        $this->logger = $loggerInterface;
        $this->helperErli = $helperErli;
        $this->helperData = $helperData;
        $this->helperProduct = $helperProduct;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->createProducts();

        return $this;
    }

    /**
     * Create products in Erli method
     */
    public function createProducts()
    {

        $erliHelperData = $this->helperData;
        $erliHelperProduct = $this->helperProduct;

        // Create new product in erli

        $elriIntegrationProductsCollection = $erliHelperData->getErliIntegrationProductCollection();

        if (!empty($elriIntegrationProductsCollection) && count($elriIntegrationProductsCollection) > 0) {

            foreach ($elriIntegrationProductsCollection as $productId) {

                //check if no exists product in erli - STATUS 404
                $status = $erliHelperProduct->getErliProduct($productId);
                if ($status == "404") {

                    $productInfoData = $erliHelperData->getProductInfo($productId);

                    if (!empty($productInfoData)
                        && isset($productInfoData['images'])
                        && $productInfoData['stock'] > 0
                    ) {

                        $parsedProductErliData = [];
                        $parsedProductErliData['name'] = $productInfoData['name'];
                        $parsedProductErliData['description'] = $productInfoData['description'];

                        if (isset($productInfoData['ean'])) {
                            $parsedProductErliData['ean'] = $productInfoData['ean'];
                        }

                        $parsedProductErliData['sku'] = $productInfoData['sku'];
                        $parsedProductErliData['images'] = $productInfoData['images'];
                        $parsedProductErliData['price'] = $productInfoData['price'];

                        $parsedProductErliData['stock'] = $productInfoData['stock'];
                        $parsedProductErliData['status'] = $productInfoData['status'];
                        $parsedProductErliData['dispatchTime']['period'] = $productInfoData['erli_dispatch_period'];
                        $parsedProductErliData['dispatchTime']['unit'] = $productInfoData['erli_dispatch_unit'];

                        //set product packaging
                        $parsedProductErliData['packaging']['tags'] = [];
                        $productPriceListSelected = $erliHelperData->getProductErliPriceList($productId);

                        if (!empty($productPriceListSelected)) {
                            $parsedProductErliData['packaging']['tags'] = [$productPriceListSelected];

                            if ($this->helperErli->getPriceList()) {
                                if (!in_array($productPriceListSelected, $this->helperErli->getPriceList())) {
                                    $parsedProductErliData['packaging']['tags'] = [
                                        $this->helperErli->getPriceList()[0]
                                    ];
                                }
                            }
                        }

                        //externalCategories
                        if (!empty($productInfoData['categories'])) {
                            foreach ($productInfoData['categories'] as $categoryId => $categoryName) {
                                $parsedProductErliData['externalCategories'][]['breadcrumb'][] = [
                                    'id' => $categoryId,
                                    'name' => $categoryName
                                ];
                            }
                        } else {
                            $parsedProductErliData['externalCategories'] = [];
                        }

                        //externalAttributes
                        if (!empty($productInfoData['attributes'])) {
                            foreach ($productInfoData['attributes'] as $key => $attribute) {
                                $parsedProductErliData['externalAttributes'][] = [
                                    'id' => $attribute['code'],
                                    'name' => $attribute['name'],
                                    'type' => $attribute['erli_type'],
                                    'values' => $attribute['erli_values'],
                                    'index' => $attribute['id']
                                ];
                            }
                        } else {
                            $parsedProductErliData['externalAttributes'] = [];
                        }

                        //externalVariantGroup
                        if (!empty($productInfoData['erli_external_variant_group'])) {
                            foreach ($productInfoData['erli_external_variant_group'] as $parentId => $attributes) {
                                $parsedProductErliData['externalVariantGroup'] = [
                                    'id' => "$parentId",
                                    'source' => 'integration',
                                    'attributes' => $attributes
                                ];
                            }
                        }

                        //create product in erli
                        $erliHelperProduct->createErliProduct($productId, $parsedProductErliData);
                    }
                }
            }
        }
    }
}

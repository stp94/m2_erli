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

namespace Edirect\Erli\Plugin;

use Edirect\Erli\Helper\Erli;
use Psr\Log\LoggerInterface;
use Edirect\Erli\Helper\Product as HelperProduct;

/**
 * Plugin Class CatalogProductAttributeSavePlugin
 */
class CatalogProductAttributeSavePlugin
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
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    private $attributeHelper;

    protected $request;

    /**
     * @var
     */
    protected $helperProduct;

    public function __construct(
        LoggerInterface $logger,
        Erli $erliHelper,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        HelperProduct $helperProduct,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->logger = $logger;
        $this->erliHelper = $erliHelper;
        $this->attributeHelper = $attributeHelper;
        $this->helperProduct = $helperProduct;
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save $subject,
        $result
    ) {
        $productIds = $this->attributeHelper->getProductIds();
        $attributesData = $this->request->getParam('attributes', []);

        if (!empty($productIds) &&
            (isset($attributesData['erli_integration']) &&
                $attributesData['erli_integration'] == 0)
        ) {

            foreach ($productIds as $productId) {

                try {
                    $getProduct = $this->erliHelper->callCurlGet('products/' . $productId, 'product');

                    //check product exists in erli
                    if ($getProduct['status'] == 200 || $getProduct['status'] == 202) {

                        try {

                            $queryData = [
                                'status' => 'inactive',
                                'frozen' => ['status' => false],
                                'overrideFrozen' => true
                            ];

                            $this->helperProduct->updateErliProduct($productId, $queryData);

                        } catch (\Exception $e) {
                            $this->logger->critical($e->getMessage());
                        }

                    }

                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }

            }

        }

        return $result;
    }
}

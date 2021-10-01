<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 19.01.2021, 15:38, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product\Action;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Edirect\Erli\Helper\Erli;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Set Product Update Flag After Save
 * Class CatalogProductSaveAfter
 */
class CatalogProductSaveAfter implements ObserverInterface
{

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $catalogProductTypeConfigurable;

    /**
     * @var Erli
     */
    protected $erliHelper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * CatalogProductSaveBefore constructor.
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param Erli $erliHelper
     */
    public function __construct(
        Action $action,
        StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        Erli $erliHelper,
        Json $json
    ) {
        $this->action = $action;
        $this->storeManager =  $storeManager;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->erliHelper = $erliHelper;
        $this->json = $json;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        //set product erli update flag if product was edited
        $product = $observer->getProduct();

        if (!$product->isObjectNew()) {
            if ($product->getErliIntegration() == 1) {

                // get children of configurable product and set update flag
                if ($product->getTypeId() == ProductTypeConfigurable::TYPE_CODE) {

                    $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());

                    if (!empty($childrenIds[0])) {
                        $this->action->updateAttributes(
                            $childrenIds[0],
                            ['erli_update_flag' => 1],
                            $this->storeManager->getStore()->getId()
                        );
                    }

                    $this->disableProductInErli($product->getId());
                }

                //disable simple product in erli
                if ($product->getStatus() == 2) {
                    $this->disableProductInErli($product->getId());
                }

            } else {
                $this->disableProductInErli($product->getId());
            }
        }

        return $this;
    }

    public function disableProductInErli($productId)
    {
        $getProduct = $this->erliHelper->callCurlGet('products/' . $productId, 'product');

        //check product exists in erli
        if ($getProduct['status'] == 200) {

            $getProductDataJson = $this->json->unserialize($getProduct['body']);
            $erliProductStatus = $getProductDataJson['status'];

            //disable product in erli
            if ($erliProductStatus == 'active') {
                $deactiveErliProductStatus = $this->erliHelper->callCurlPatch('products/' . $productId, ['status' => 'inactive'], 'product', $productId);
            }

        }
    }
}

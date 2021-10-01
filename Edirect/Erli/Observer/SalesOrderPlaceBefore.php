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

namespace Edirect\Erli\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Edirect\Erli\Helper\Erli;

/**
 * Update Erli Product Stock After Order Place
 * Class SalesOrderPlaceBefore
 */
class SalesOrderPlaceBefore implements ObserverInterface
{

    /**
     * @var Product
     */
    protected $productActionObject;

    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var Erli
     */
    protected $erliHelper;

    /**
     * SalesOrderPlaceBefore constructor.
     * @param Product $productActionObject
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param Erli $erliHelper
     */
    public function __construct(
        Product $productActionObject,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        Erli $erliHelper
    ) {
        $this->productActionObject = $productActionObject;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->erliHelper = $erliHelper;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $productId = $item->getProductId();
            $erliIntegration = $this->productActionObject->getAttributeRawValue($productId, 'erli_integration', 0);

            if ($erliIntegration == 1) {
                $sku = $item->getSku();
                $salableQty = $this->getSalableQuantityDataBySku->execute($sku);

                if ($salableQty && count($salableQty) > 0) {
                    $qtyData = ['stock' => (int) $salableQty[0]['qty']];
                    $guery = 'products/'.$productId;
                    $this->erliHelper->callCurlPatch($guery, $qtyData, 'product', $productId);
                }
            }

        }

        return $this;
    }
}

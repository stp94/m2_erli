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

/**
 * Set Product Update Flag Before Save
 * Class CatalogProductSaveBefore
 */
class CatalogProductSaveBefore implements ObserverInterface
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
     * CatalogProductSaveBefore constructor.
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     */
    public function __construct(
        Action $action,
        StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
    ) {
        $this->action = $action;
        $this->storeManager =  $storeManager;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
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
                $product->setErliUpdateFlag(1);

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
                }
            }
        }

        return $this;
    }
}

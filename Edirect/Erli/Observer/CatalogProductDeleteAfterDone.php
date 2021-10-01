<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 26.03.2021, 11:22, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Observer;

use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Edirect\Erli\Helper\Erli;

/**
 * Observer Class CatalogProductDeleteAfterDone
 */
class CatalogProductDeleteAfterDone implements ObserverInterface
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
     * @var Json
     */
    protected $json;

    /**
     * CatalogProductDeleteAfterDone constructor.
     * @param LoggerInterface $logger
     * @param Erli $erliHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Erli $erliHelper
    ) {
        $this->logger = $logger;
        $this->erliHelper = $erliHelper;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();

        if ($productId) {
            $this->archiveProductInErli($productId);
        }

        return $this;
    }

    public function archiveProductInErli($productId)
    {

        try {

            $getProduct = $this->erliHelper->callCurlGet('products/' . $productId, 'product');

            //check if the product exists in erli and archive it
            if ($getProduct['status'] == 200 || $getProduct['status'] == 202) {

                try {

                    $this->erliHelper->callCurlPatch(
                        'products/' . $productId,
                        ['archived' => 'true'],
                        'product',
                        $productId
                    );

                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}

<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 07, styczeń, 10:54, 2021
 * @author   biuro@edirect24.pl
 * @projekt   Erli
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;
use Edirect\Erli\Helper\Data;
use Edirect\Erli\Model\Log;

class Order extends AbstractHelper
{

    const ERLI_PAYMENT_METHOD_CODE = 'erli';
    
    const CASHONDELIVERY_PAYMENT_METHOD_CODE = 'cashondelivery';

    const ERLI_ORDER_STATUS_PENDING = 'pending';

    const ERLI_ORDER_STATUS_PURCHASED = 'purchased';

    const ERLI_ORDER_STATUS_PURCHASED_COD = 'purchased_cod';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var QuoteManagement
     */
    protected $quoteManager;

    /**
     * @var QuoteFactory
     */
    protected $quoteModel;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $erliHelper;

    /**
     * @var Log
     */
    protected $logModel;

    /**
     * Order constructor.
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     * @param QuoteManagement $quoteManager
     * @param QuoteFactory $quoteModel
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param BuilderInterface $transactionBuilder
     * @param Json $json
     * @param ResourceConnection $resource
     * @param Data $erliHelper
     * @param Log $logModel
     */
    public function __construct(
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepository,
        QuoteManagement $quoteManager,
        QuoteFactory $quoteModel,
        InvoiceService $invoiceService,
        Transaction $transaction,
        BuilderInterface $transactionBuilder,
        Json $json,
        ResourceConnection $resource,
        Data $erliHelper,
        Log $logModel
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->quoteManager = $quoteManager;
        $this->quoteModel = $quoteModel;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->transactionBuilder = $transactionBuilder;
        $this->json = $json;
        $this->resource = $resource;
        $this->erliHelper = $erliHelper;
        $this->logModel = $logModel;
    }

    /**
     * @param $orderData
     * @return $this
     */
    public function createOrder($orderData)
    {
        $shippingMethod = $this->getMagentoShippingMethod($orderData['delivery']['typeId']);

        if ($shippingMethod) {

            try {

                //$store = $this->storeManager->getStore();
                $store = $this->storeManager->getDefaultStoreView();
                $cod = $orderData['delivery']['cod'];
                $address = $this->getOrderAddressFormat($orderData['user']['deliveryAddress']);

                if (isset($orderData['user']['invoiceAddress'])) {
                    $orderData['user']['invoiceAddress']['firstName'] = isset($orderData['user']['invoiceAddress']['firstName']) ? $orderData['user']['invoiceAddress']['firstName'] : $orderData['user']['deliveryAddress']['firstName'];
                    $orderData['user']['invoiceAddress']['lastName'] = isset($orderData['user']['invoiceAddress']['lastName']) ? $orderData['user']['invoiceAddress']['lastName'] : $orderData['user']['deliveryAddress']['lastName'];
                    $orderData['user']['invoiceAddress']['companyName'] = isset($orderData['user']['invoiceAddress']['companyName']) ? $orderData['user']['invoiceAddress']['companyName'] : '';
                    $orderData['user']['invoiceAddress']['nip'] = isset($orderData['user']['invoiceAddress']['nip']) ? $orderData['user']['invoiceAddress']['nip'] : '';
                    $orderData['user']['invoiceAddress']['phone'] = $orderData['user']['deliveryAddress']['phone'];
                    $invoiceAddress = $this->getOrderAddressFormat($orderData['user']['invoiceAddress']);
                } else {
                    $invoiceAddress = $this->getOrderAddressFormat($orderData['user']['deliveryAddress']);
                }

                $quote = $this->quoteModel->create();
                $quote->setStore($store);

                foreach ($orderData['items'] as $item) {
                    $product = $this->productRepository->getById((int)$item['externalId']);

                    if ($product && $product->getId()) {
                        $product->setPrice($item['unitPrice'] / 100);
                        $product->setBasePrice($item['unitPrice'] / 100);
                        $quote->addProduct($product, (int)($item['quantity']));
                    }
                }

                $shippingAddress = $quote->getShippingAddress()->addData($address);
                $paymentMethod = $this->getOrderPaymentMethod($orderData);
                $shippingAddress->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setAmount($orderData['delivery']['price'] / 100)
                    ->setShippingDescription($orderData['delivery']['name'])
                    ->setShippingMethod($shippingMethod);

                $quote->setIsMultiShipping(false);
                $quote->setCurrency();
                $quote->setCustomerIsGuest(true);
                $quote->setCustomerGroupId(0);
                $quote->setCustomerEmail($orderData['user']['email']);
                $quote->setCustomerFirstname($orderData['user']['deliveryAddress']['firstName']);
                $quote->setCustomerLastname($orderData['user']['deliveryAddress']['lastName']);
                $quote->setCreatedAt(date_format(date_create($orderData['created']), "Y-m-d H:i:s"));
                $quote->setUpdatedAt(date_format(date_create($orderData['created']), "Y-m-d H:i:s"));
                $quote->setErliId($orderData['id']);
                $quote->getBillingAddress()->addData($invoiceAddress);
                $quote->getShippingAddress()->addData($address);
                $quote->setPaymentMethod($paymentMethod);
                $quote->save();
                $quote->getPayment()->importData(['method' => $paymentMethod]);
                $quote->collectTotals()->save();
                $order = $this->quoteManager->submit($quote);

                $shippingAmount = $order->getShippingAmount();
                $order->setShippingAmount($orderData['delivery']['price'] / 100);
                $order->setBaseShippingAmount($orderData['delivery']['price'] / 100);
                $order->setGrandTotal($order->getGrandTotal() - $shippingAmount + ($orderData['delivery']['price'] / 100));

                if (isset($orderData['comment']) && $orderData['comment']) {
                    $order->addCommentToStatusHistory($orderData['comment'])->save();
                }

                if (isset($orderData['delivery']['pickupPlace']) && $orderData['delivery']['pickupPlace']) {
                    $order->addCommentToStatusHistory(implode('<br />', $orderData['delivery']['pickupPlace']))->save();
                }

                $order->setErliId($orderData['id']);
                $order
                    ->setState("pending")
                    ->setStatus($this->erliHelper->getOrderStatus(Order::ERLI_ORDER_STATUS_PENDING));

                $order->save();

                if ($orderData['status'] == 'purchased' && $order->canInvoice()) {
                    $this->createInvoice($order, $cod);
                }

                if ($paymentMethod == Order::ERLI_PAYMENT_METHOD_CODE) {
                    $this->createTransaction($order, $orderData['payment']['id']);
                }

                $this->getConnection()->delete('ed_erli_problematic_orders', ['order_id = ?' => $orderData['id']]);

            } catch (\Exception $e) {

                $this->logger->critical('Error message', ['exception' => $e]);
                $this->getConnection()->insertOnDuplicate('ed_erli_problematic_orders', ['order_id' => $orderData['id'], 'created_at' => date('Y-m-d H:i:s')], ['order_id']);

                $this->logModel
                    ->setObjectName('order')
                    ->setObjectId($orderData['id'])
                    ->setMethodName('GET')
                    ->setFunctionName('inbox - create_order')
                    ->setResponseStatus(200)
                    ->setResponseMessage('OK')
                    ->setResponseAdditionalMessage($e->getMessage())
                    ->setCreatedAt(date('Y-m-d H:i:s'))
                    ->save();

                $this->logModel->unsetData();

            }

        } else {

            $this->getConnection()->insertOnDuplicate('ed_erli_problematic_orders', ['order_id' => $orderData['id'], 'created_at' => date('Y-m-d H:i:s')], ['order_id']);

            $this->logModel
                ->setObjectName('order')
                ->setObjectId($orderData['id'])
                ->setMethodName('GET')
                ->setFunctionName('inbox - create_order')
                ->setResponseStatus(200)
                ->setResponseMessage('OK')
                ->setResponseAdditionalMessage('Brak mapowania metody dostawy')
                ->setCreatedAt(date('Y-m-d H:i:s'))
                ->save();

            $this->logModel->unsetData();

        }

        return $this;
    }

    /**
     * @param $address
     * @return array
     */
    public function getOrderAddressFormat($address)
    {
        return [
            'firstname' => $address['firstName'],
            'lastname' => $address['lastName'],
            'street' => $address['address'],
            'postcode' => $address['zip'],
            'city' => $address['city'],
            'country_id' => strtoupper($address['country']),
            'telephone' => $address['phone'],
            'company' => isset($address['companyName']) ? $address['companyName'] : '',
            'taxvat' => isset($address['nip']) ? $address['nip'] : ''
        ];
    }

    /**
     * @param $order
     * @return string
     */
    public function getOrderPaymentMethod($order)
    {
        if (array_key_exists('payment', $order)) {

            return Order::ERLI_PAYMENT_METHOD_CODE;

        }

        return Order::CASHONDELIVERY_PAYMENT_METHOD_CODE;
    }

    /**
     * @param $order
     * @param $transactionId
     */
    public function createInvoiceAndTransaction($order, $transactionId)
    {
        try {

            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $payment = $order->getPayment();
            $transaction = $this->transactionBuilder
                ->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder($transaction, $transactionId);
            $payment->save();
            $order->setState("processing")->setStatus("processing");
            $order->save();

        } catch (\Exception $e) {

            $this->logger->critical('Error message', ['exception' => $e]);

        }
    }

    /**
     * @param $order
     * @param $cod
     */
    public function createInvoice($order, $cod)
    {
        try {

            if ($cod) {
                $status = $this->erliHelper->getOrderStatus(Order::ERLI_ORDER_STATUS_PURCHASED_COD);
            } else {
                $status = $this->erliHelper->getOrderStatus(Order::ERLI_ORDER_STATUS_PURCHASED);
            }

            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $order
                ->setState("processing")
                ->setStatus($status)
                ->save();

        } catch (\Exception $e) {

            $this->logger->critical('Error message', ['exception' => $e]);

        }
    }

    /**
     * @param $order
     * @param $transactionId
     */
    public function createTransaction($order, $transactionId)
    {
        try {

            $payment = $order->getPayment();
            $transaction = $this->transactionBuilder
                ->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder($transaction, $transactionId);
            $payment->save();
            $order->save();

        } catch (\Exception $e) {

            $this->logger->critical('Error message', ['exception' => $e]);

        }
    }

    /**
     * @param $erliMethod
     * @return mixed|null
     */
    public function getMagentoShippingMethod($erliMethod)
    {
        $mappingMethods = $this->erliHelper->getShippingMethodMapping();
        $mappingMethodsUnSerialize = $this->json->unserialize($mappingMethods);

        foreach ($mappingMethodsUnSerialize as $method) {

            if ($method['erli_shipping_method'] == $erliMethod) {

                return $method['magento_shipping_method'].'_'.$method['magento_shipping_method'];

            }

        }

        return null;
    }

    /**
     * @param $erliMethod
     * @return mixed|null
     */
    public function getErliShippingMethod($magentoMethod)
    {
        $mappingMethods = $this->erliHelper->getShippingMethodMapping();
        $mappingMethodsUnSerialize = $this->json->unserialize($mappingMethods);

        foreach ($mappingMethodsUnSerialize as $method) {

            if ($method['magento_shipping_method'] == $magentoMethod) {

                return $method['erli_shipping_method'];

            }

        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function getConnection()
    {
        return $this->resource->getConnection();
    }
}

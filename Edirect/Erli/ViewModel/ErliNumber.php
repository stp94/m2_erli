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

namespace Edirect\Erli\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * ViewModel Class ErliNumber
 */
class ErliNumber implements ArgumentInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    protected $order;

    /**
     * Settlements constructor.
     * @param LoggerInterface $logger
     * @param Http $request
     * @param OrderRepositoryInterface $order
     */
    public function __construct(
        LoggerInterface $logger,
        Http $request,
        OrderRepositoryInterface $order
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getErliNumber()
    {
        $order = $this->order->get($this->request->getParam('order_id'));

        return $order->getData('erli_id');
    }
}

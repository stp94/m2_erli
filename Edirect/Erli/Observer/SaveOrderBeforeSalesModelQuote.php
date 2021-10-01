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
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;

/**
 * Save Order Before
 * Class SaveOrderBeforeSalesModelQuote
 */
class SaveOrderBeforeSalesModelQuote implements ObserverInterface
{

    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * SaveOrderBeforeSalesModelQuote constructor.
     * @param Copy $objectCopyService
     */
    public function __construct(
        Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');
        $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);

        return $this;
    }
}

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

namespace Edirect\Erli\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Set Product Full Synchronization Cron
 * Class Connection
 */
class SetProductUpdateCron extends Action
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $productActionModel;

    /**
     * SetProductUpdateCron constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param CollectionFactory $productCollection
     * @param \Magento\Catalog\Model\Product\Action $productActionModel
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        CollectionFactory $productCollection,
        \Magento\Catalog\Model\Product\Action $productActionModel
    ) {
        $this->logger = $logger;
        $this->productCollection = $productCollection;
        $this->productActionModel = $productActionModel;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $productCollection = $this->productCollection->create();
            $productCollection->addFieldToFilter('erli_integration', 1);
            $productIds = $productCollection->getAllIds();
            $this->productActionModel->updateAttributes($productIds, ['erli_update_flag' => 1], 0);
            $this->messageManager->addSuccessMessage(__('Cron Job has been successfully added to the queue'));
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $this->_redirect('adminhtml/system_config/edit/section/erli');
    }
}

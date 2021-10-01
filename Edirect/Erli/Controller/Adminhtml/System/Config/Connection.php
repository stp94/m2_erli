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

use Edirect\Erli\Helper\Erli;
use Edirect\Erli\Helper\Product;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Erli Api Test Connection
 * Class Connection
 */
class Connection extends Action
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
     * @var Product
     */
    protected $productHelper;

    /**
     * Connection constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Erli $erliHelper
     * @param Product $productHelper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Erli $erliHelper,
        Product $productHelper
    ) {
        $this->logger = $logger;
        $this->erliHelper = $erliHelper;
        $this->productHelper = $productHelper;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $connection = $this->erliHelper->callCurlGet('hooks');
            if ($connection['status'] == 200) {
                $this->messageManager->addSuccessMessage(__('Connection Success'));
            } else {
                $this->messageManager->addErrorMessage(
                    __('Connection Error. Please check Api Url, Api Key and try again.')
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $this->_redirect('adminhtml/system_config/edit/section/erli');
    }
}

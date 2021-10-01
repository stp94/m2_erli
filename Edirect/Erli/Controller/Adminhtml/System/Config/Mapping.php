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

use Edirect\Erli\Cron\UpdateErliShippingMethod;
use Edirect\Erli\Model\ResourceModel\ErliShippingMethod\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\Config\Factory;
use Psr\Log\LoggerInterface;

/**
 * Erli Shipping Method Mapping
 * Class Connection
 */
class Mapping extends Action
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var UpdateErliShippingMethod
     */
    protected $updateErliShippingMethod;

    /**
     * @var CollectionFactory
     */
    protected $erliShippingMethod;

    /**
     * @var Factory
     */
    protected $configFactory;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param UpdateErliShippingMethod $updateErliShippingMethod
     * @param CollectionFactory $erliShippingMethod
     * @param Factory $configFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        UpdateErliShippingMethod $updateErliShippingMethod,
        CollectionFactory $erliShippingMethod,
        Factory $configFactory
    ) {
        $this->logger = $logger;
        $this->updateErliShippingMethod = $updateErliShippingMethod;
        $this->erliShippingMethod = $erliShippingMethod;
        $this->configFactory = $configFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $this->updateErliShippingMethod->execute();
            $collection = $this->erliShippingMethod->create();
            $i = 0;
            
            foreach ($collection as $item) {
                $values['_'.time().'_'.$i] = ['erli_shipping_method' => $item->getMethodId(), 'magento_shipping_method' => ''];
                $i++;
            }

            $configData = [
                'section' => 'erli',
                'website' => null,
                'store'   => null,
                'groups'  => [
                    'shipping_method' => [
                        'fields' => [
                            'mapping' => [
                                'value' => $values,
                            ],
                        ],
                    ],
                ],
            ];
            $configModel = $this->configFactory->create(['data' => $configData]);
            $configModel->save();

            $this->messageManager->addSuccessMessage(__('Shipping Method Mapping Success'));
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $this->_redirect('adminhtml/system_config/edit/section/erli');
    }
}

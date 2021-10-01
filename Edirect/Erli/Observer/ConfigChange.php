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
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Edirect\Erli\Helper\Erli;

/**
 * Change Hooks Configuration
 * Observer Class ConfigChange
 */
class ConfigChange implements ObserverInterface
{
    const XML_PATH_ERLI_HOOKS = 'erli/general/hooks';

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Erli
     */
    private $erliHelper;

    /**
     * ConfigChange constructor.
     * @param WriterInterface $configWriter
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     * @param Erli $erliHelper
     */
    public function __construct(
        WriterInterface $configWriter,
        Json $json,
        StoreManagerInterface $storeManager,
        Erli $erliHelper
    ) {
        $this->configWriter = $configWriter;
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->erliHelper = $erliHelper;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $hooksData = $this->erliHelper->callCurlGet('hooks');
        $hooks = $this->json->unserialize($hooksData['body']);
        
        if (empty($hooks)) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
            $this->erliHelper->callCurlPut('hooks/checkBuyability', ['hookName' => 'checkBuyability', 'url' => $baseUrl . 'rest/V1/hooks/checkBuyAbility']);
        } else {
            $hookNames = [];

            foreach ($hooks as $hook) {
                $hookNames[] = $hook['hookName'];
            }

            $this->configWriter->save(ConfigChange::XML_PATH_ERLI_HOOKS, implode(',', $hookNames));
        }

        return $this;
    }
}

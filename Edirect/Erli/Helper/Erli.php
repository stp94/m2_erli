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

use Edirect\Erli\Model\Log;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Helper Class Erli
 */
class Erli extends AbstractHelper
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Log
     */
    protected $logModel;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadataInterface;

    /**
     * @var ResourceInterface
     */
    protected $moduleResource;

    /**
     * Erli constructor.
     * @param Data $helper
     * @param Log $logModel
     * @param Curl $curl
     * @param Json $json
     * @param ProductMetadataInterface $productMetadataInterface
     * @param ResourceInterface $moduleResource
     */
    public function __construct(
        Data $helper,
        Log $logModel,
        Curl $curl,
        Json $json,
        ProductMetadataInterface $productMetadataInterface,
        ResourceInterface $moduleResource
    ) {
        $this->helper = $helper;
        $this->logModel = $logModel;
        $this->curl = $curl;
        $this->json = $json;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->moduleResource = $moduleResource;
    }

    /**
     * @param $query
     * @param null $objectName
     * @param null $objectId
     * @return array
     */
    public function callCurlGet($query, $objectName = null, $objectId = null)
    {
        $result = [];
        $productName = $this->productMetadataInterface->getName();
        $productVersion = $this->productMetadataInterface->getVersion();
        $moduleVersion = $this->moduleResource->getDbVersion(Data::MODULE_NAME);
        $userAgent = $productName . ' v' . $productVersion . ' (' . Data::MODULE_NAME . ' v' . $moduleVersion . ')';
        $apiUrl = $this->helper->getApiUrl();
        $apiKey = $this->helper->getApiKey();
        $verifyHost = $this->helper->getVerifyHost() ? 2 : 0;
        $verifyPeer = $this->helper->getVerifyHost() ? 1 : 0;
        $url = $apiUrl . $query;
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'User-Agent' => $userAgent,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $this->curl->setHeaders($headers);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, $verifyHost);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, $verifyPeer);

        if ($this->helper->getKeepAlive()) {
            $this->curl->setOption(CURLOPT_TCP_KEEPALIVE, $this->helper->getKeepAlive());
            $this->curl->setOption(CURLOPT_TCP_KEEPIDLE, $this->helper->getKeepIdle());
            $this->curl->setOption(CURLOPT_TCP_KEEPINTVL, $this->helper->getKeepIntvl());
        }

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->curl->get($url);
            $responseStatus = $this->curl->getStatus();
            $responseBody = $this->curl->getBody();
            $result['body'] = $responseBody;
            $result['status'] = $responseStatus;

            if (!in_array($responseStatus, [429, 502, 503, 504])) {
                break;
            }

            usleep(2000000 ** $attempt);
        }

        $responseAdditionalMessage = '';
        
        if ($objectName == 'product' && $responseStatus == 404) {
            $responseAdditionalMessage = __("The product does not exist in erli");
        }

        if ($objectName == 'product' && ($responseStatus >= 200 && $responseStatus < 300)) {
            $responseAdditionalMessage = __("The product exist in erli");
        }

        $responseMessage = ($responseStatus >= 200 && $responseStatus < 300) ? 'OK' : $responseBody;

        if ($query != 'delivery/priceLists') { //don't save log if price list is getting
            $this->logModel
                ->setObjectName($objectName)
                ->setObjectId($objectId)
                ->setMethodName('GET')
                ->setFunctionName($query)
                ->setResponseStatus($responseStatus)
                ->setResponseMessage($responseMessage)
                ->setResponseAdditionalMessage($responseAdditionalMessage)
                ->setCreatedAt(date('Y-m-d H:i:s'))
                ->save();

            $this->logModel->unsetData();
        }

        return $result;
    }

    /**
     * @param $query
     * @param $postData
     * @param null $objectName
     * @param null $objectId
     * @return mixed
     */
    public function callCurlPost($query, $postData, $objectName = null, $objectId = null)
    {
        $productName = $this->productMetadataInterface->getName();
        $productVersion = $this->productMetadataInterface->getVersion();
        $moduleVersion = $this->moduleResource->getDbVersion(Data::MODULE_NAME);
        $userAgent = $productName . ' v' . $productVersion . ' (' . Data::MODULE_NAME . ' v' . $moduleVersion . ')';
        $apiUrl = $this->helper->getApiUrl();
        $apiKey = $this->helper->getApiKey();
        $verifyHost = $this->helper->getVerifyHost() ? 2 : 0;
        $verifyPeer = $this->helper->getVerifyHost() ? 1 : 0;
        $url = $apiUrl . $query;
        $dataString = $this->json->serialize($postData);
        
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'User-Agent' => $userAgent,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($dataString)
        ];
        $this->curl->setHeaders($headers);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, $verifyHost);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, $verifyPeer);

        if ($this->helper->getKeepAlive()) {
            $this->curl->setOption(CURLOPT_TCP_KEEPALIVE, $this->helper->getKeepAlive());
            $this->curl->setOption(CURLOPT_TCP_KEEPIDLE, $this->helper->getKeepIdle());
            $this->curl->setOption(CURLOPT_TCP_KEEPINTVL, $this->helper->getKeepIntvl());
        }

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->curl->post($url, $dataString);
            $responseStatus = $this->curl->getStatus();
            $responseBody = $this->curl->getBody();
            $result['body'] = $responseBody;
            $result['status'] = $responseStatus;

            if (!in_array($responseStatus, [429, 502, 503, 504])) {
                break;
            }

            usleep(2000000 ** $attempt);
        }

        $responseAdditionalMessage = '';
        if ($objectName == 'product' && $responseStatus == 100) {
            $responseAdditionalMessage = __("Product create");
        }

        $responseMessage = ($responseStatus >= 200 && $responseStatus < 300) ? 'OK' : $responseBody;
        $this->logModel
            ->setObjectName($objectName)
            ->setObjectId($objectId)
            ->setMethodName('POST')
            ->setFunctionName($query)
            ->setResponseStatus($responseStatus)
            ->setResponseMessage($responseMessage)
            ->setResponseAdditionalMessage($responseAdditionalMessage)
            ->setCreatedAt(date('Y-m-d H:i:s'))
            ->save();

        $this->logModel->unsetData();

        return $result;
    }

    /**
     * @param $query
     * @param $postData
     * @param null $objectName
     * @param null $objectId
     * @return array
     */
    public function callCurlPatch($query, $postData, $objectName = null, $objectId = null)
    {
        $result = [];
        $productName = $this->productMetadataInterface->getName();
        $productVersion = $this->productMetadataInterface->getVersion();
        $moduleVersion = $this->moduleResource->getDbVersion(Data::MODULE_NAME);
        $userAgent = $productName . ' v' . $productVersion . ' (' . Data::MODULE_NAME . ' v' . $moduleVersion . ')';
        $apiUrl = $this->helper->getApiUrl();
        $apiKey = $this->helper->getApiKey();
        $verifyHost = $this->helper->getVerifyHost() ? 2 : 0;
        $verifyPeer = $this->helper->getVerifyHost() ? 1 : 0;
        $url = $apiUrl . $query;
        $dataString = $this->json->serialize($postData);

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'User-Agent' => $userAgent,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($dataString)
        ];
        $this->curl->setHeaders($headers);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, $verifyHost);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, $verifyPeer);

        if ($this->helper->getKeepAlive()) {
            $this->curl->setOption(CURLOPT_TCP_KEEPALIVE, $this->helper->getKeepAlive());
            $this->curl->setOption(CURLOPT_TCP_KEEPIDLE, $this->helper->getKeepIdle());
            $this->curl->setOption(CURLOPT_TCP_KEEPINTVL, $this->helper->getKeepIntvl());
        }

        $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'PATCH');

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->curl->post($url, $dataString);
            $responseStatus = $this->curl->getStatus();
            $responseBody = $this->curl->getBody();
            $result['body'] = $responseBody;
            $result['status'] = $responseStatus;

            if (!in_array($responseStatus, [429, 502, 503, 504])) {
                break;
            }

            usleep(2000000 ** $attempt);
        }

        $responseAdditionalMessage = '';
        if ($objectName == 'product' && $responseStatus == 100) {
            $responseAdditionalMessage = __("Product update");
        }

        $responseMessage = ($responseStatus >= 200 && $responseStatus < 300) ? 'OK' : $responseBody;
        $this->logModel
            ->setObjectName($objectName)
            ->setObjectId($objectId)
            ->setMethodName('PATCH')
            ->setFunctionName($query)
            ->setResponseStatus($responseStatus)
            ->setResponseMessage($responseMessage)
            ->setResponseAdditionalMessage($responseAdditionalMessage)
            ->setCreatedAt(date('Y-m-d H:i:s'))
            ->save();

        $this->logModel->unsetData();

        return $result;
    }

    /**
     * @param $query
     * @param $postData
     * @return array
     */
    public function callCurlPut($query, $postData)
    {
        $result = [];
        $productName = $this->productMetadataInterface->getName();
        $productVersion = $this->productMetadataInterface->getVersion();
        $moduleVersion = $this->moduleResource->getDbVersion(Data::MODULE_NAME);
        $userAgent = $productName . ' v' . $productVersion . ' (' . Data::MODULE_NAME . ' v' . $moduleVersion . ')';
        $apiUrl = $this->helper->getApiUrl();
        $apiKey = $this->helper->getApiKey();
        $verifyHost = $this->helper->getVerifyHost() ? 2 : 0;
        $verifyPeer = $this->helper->getVerifyHost() ? 1 : 0;
        $url = $apiUrl . $query;
        $dataString = $this->json->serialize($postData);

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'User-Agent' => $userAgent,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($dataString)
        ];
        $this->curl->setHeaders($headers);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, $verifyHost);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, $verifyPeer);

        if ($this->helper->getKeepAlive()) {
            $this->curl->setOption(CURLOPT_TCP_KEEPALIVE, $this->helper->getKeepAlive());
            $this->curl->setOption(CURLOPT_TCP_KEEPIDLE, $this->helper->getKeepIdle());
            $this->curl->setOption(CURLOPT_TCP_KEEPINTVL, $this->helper->getKeepIntvl());
        }

        $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->curl->post($url, $dataString);
            $responseStatus = $this->curl->getStatus();
            $responseBody = $this->curl->getBody();
            $result['body'] = $responseBody;
            $result['status'] = $responseStatus;

            if (!in_array($responseStatus, [429, 502, 503, 504])) {
                break;
            }

            usleep(2000000 ** $attempt);
        }

        $responseMessage = ($responseStatus >= 200 && $responseStatus < 300) ? 'OK' : $responseBody;
        $this->logModel
            ->setObjectName('hook')
            ->setObjectId(null)
            ->setMethodName('PUT')
            ->setFunctionName($query)
            ->setResponseStatus($responseStatus)
            ->setResponseMessage($responseMessage)
            ->setResponseAdditionalMessage(null)
            ->setCreatedAt(date('Y-m-d H:i:s'))
            ->save();

        $this->logModel->unsetData();

        return $result;
    }

    public function getPriceList()
    {

        try {

            $query = 'delivery/priceLists';
            $priceList = $this->callCurlGet($query);

            if ($priceList['status'] == 200) {
                return $this->json->unserialize($priceList['body']);
            }

            return false;

        } catch (\Exception $e) {

            $this->logger->critical('Error message', ['exception' => $e]);
            
        }
    }
}

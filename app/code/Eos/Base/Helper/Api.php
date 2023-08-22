<?php
namespace Eos\Base\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Api
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ApiHelper constructor.
     *
     * @param Curl $curl
     * @param Json $jsonSerializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Curl $curl,
        Json $jsonSerializer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the API credentials for the specified environment (e.g., test, production).
     *
     * @param string $environment
     * @return array
     */
    public function getSfCredentials()
    {
        $scope = $this->scopeConfig; 
        $environment = $scope->getValue(
            "api_credentials/environment_settings/environment"
          );
        $path = "api_credentials/sf_{$environment}_environment/";
        
        return [
            'environment' => $environment,
            'customer_code' => $scope->getValue($path . "customer_code"),
            'app_url' => $scope->getValue($path . "app_url"),
            'app_key' => $scope->getValue($path . "app_key"),
            'app_secret' => $scope->getValue($path . "app_secret"),
            'app_aes_secret' => $scope->getValue($path . "app_aes_secret"),
            'auth_code' => $scope->getValue($path . "auth_code")
        ];
    }

    /**
     * Make an API GET request
     *
     * @param string $url
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function makeApiGetRequest($url, $params)
    {
        $query = http_build_query($params);
        $fullUrl = $url . '?' . $query;

        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init($fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $this->jsonSerializer->unserialize($response);
    }

    public function makeApiPostRequest($url, $headers, $bodyEncoded)
    {
        $headersString = array_map(function ($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($headers), $headers);
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersString);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyEncoded);

        $response = curl_exec($ch);        
        curl_close($ch);

        return $response;
    }
}

<?php
namespace Eos\Base\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Eos\Base\Model\ResourceModel\SfToken\CollectionFactory as SfTokenCollectionFactory;
use Eos\Base\Helper\Api as ApiHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;

class SfToken extends AbstractModel
{
    
    /**
     * @var SfTokenCollectionFactory
     */
    protected $sfTokenCollectionFactory;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * SfToken constructor.
     *
     * @param Context $context
     * @param Registry $registry // Add the registry argument here
     * @param SfTokenCollectionFactory $sfTokenCollectionFactory
     * @param ApiHelper $apiHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SfTokenCollectionFactory $sfTokenCollectionFactory,
        ApiHelper $apiHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null
    ) {
        $this->sfTokenCollectionFactory = $sfTokenCollectionFactory;
        $this->apiHelper = $apiHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }


    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Eos\Base\Model\ResourceModel\SfToken');
    }

    /**
     * Set data from API response
     *
     * @param array $data
     * @return $this
     */
    public function setApiData(array $data)
    {
        $environment = $this->apiHelper->getSfCredentials()['environment'];

        $this->setData('apiConnection', $environment ?? null);
        $this->setData('apiResultCode', $data['apiResultCode'] ?? null);
        $this->setData('apiErrorMsg', $data['apiErrorMsg'] ?? null);
        $this->setData('expireIn', $data['expireIn'] ?? null);
        $this->setData('accessToken', $data['accessToken'] ?? null);
        return $this;
    }

    /**
     * Get the latest active record with apiResultCode == 0
     *
     * @return $this|null
     * @throws LocalizedException
     */
    public function getLatestActiveRecord()
    {
        $environment = $this->apiHelper->getSfCredentials()['environment'];
        /** @var AbstractCollection $sfTokenCollection */
        $sfTokenCollection = $this->sfTokenCollectionFactory->create();
        $sfTokenCollection
            ->addFieldToFilter('apiResultCode', 0)
            ->addFieldToFilter('apiConnection', $environment)
            ->setOrder('apiTimestamp', 'DESC')
            ->setPageSize(1);

        // Check if there are any records found
        if ($sfTokenCollection->getSize()) {
            // Get the latest record (the first item) from the collection
            $latestRecord = $sfTokenCollection->getFirstItem();

            // Set the data of the current model with the latest record data
            $this->setData($latestRecord->getData());

            return $this;
        }

        // If no valid record found, return null
        return null;
    }

    /**
     * Check if the access token is valid based on apiTimestamp and expireIn
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isAccessTokenValid()
{
    // Get the current timestamp
    $currentTimestamp = time();
    try {
        // Get the apiTimestamp and expireIn from the model data
        $apiTimestamp = $this->getData('apiTimestamp');
        $expireIn = (int) $this->getData('expireIn');

        if (!$apiTimestamp || !$expireIn) {
            // If apiTimestamp or expireIn is not set, throw an exception
            throw new LocalizedException(__('Missing apiTimestamp or expireIn.'));
        }

        // Calculate the expiration timestamp based on apiTimestamp and expireIn
        $apiTimestampUnix = strtotime($apiTimestamp);
        $expirationTimestamp = $apiTimestampUnix + $expireIn;
        // Compare the current timestamp with the expiration timestamp

        return $currentTimestamp < $expirationTimestamp;
    } catch (\Exception $e) {
        return false;
    }

}

    /**
     * Get the access token by making the API request.
     * If the token is expired or not available, a new token will be fetched.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        $apiCredentials = $this->apiHelper->getSfCredentials();
        $url = $apiCredentials['app_url'] . '/openapi/api/token';

        // Get the latest active record with apiResultCode == 0
        $latestActiveRecord = $this->getLatestActiveRecord();
        if ($latestActiveRecord && $latestActiveRecord->isAccessTokenValid()) {
            // If the latest active record is valid, return its access token
            return $latestActiveRecord->getData('accessToken');
        }

        // If the latest active record is expired or not available, fetch a new one
        try {
            $responseData = $this->apiHelper->makeApiGetRequest($url, [
                'appKey' => $apiCredentials['app_key'],
                'appSecret' => $apiCredentials['app_secret'],
            ]);
            $flattenedResponse = [
                'apiResultCode' => $responseData['apiResultCode'] ?? null,
                'apiErrorMsg' => $responseData['apiErrorMsg'] ?? null,
                'expireIn' => $responseData['apiResultData']['expireIn'] ?? null,
                'accessToken' => $responseData['apiResultData']['accessToken'] ?? null,
                'apiTimestamp' => $responseData['apiTimestamp'] ?? null,
            ];

            // Create a new instance of the model using the ObjectManagerInterface
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $newRecord = $objectManager->create(\Eos\Base\Model\SfToken::class);
            $newRecord->setApiData($flattenedResponse);
            $newRecord->save();

            return $flattenedResponse['accessToken'];
        } catch (\Exception $e) {
            // Handle the exception or log the error.
            error_log($e->getMessage());
            return null;
        }
    }

}

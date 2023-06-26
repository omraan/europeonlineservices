<?php

namespace Eos\BackPortal\Controller\UploadId;

use Eos\BackPortal\Controller\UploadId\CredentialRequestDto;
use Eos\Base\Model\UploadIdFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Request\Http; // Import the Http request class
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var UploadIdFactory
     */
    protected $_uploadId;

    /**
     * @var Http
     */
    protected $request; // Update the type hint to Http

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    public function __construct(
        Context                                          $context,
        Session                                          $customerSession,
        DateTime                                         $dateTime,
        UploadIdFactory                                  $uploadId,
        Http                                             $request, // Update the type hint to Http,
        \Magento\Framework\Filesystem                    $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory

    )
    {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_uploadId = $uploadId;
        $this->request = $request;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $active = 'prod';

        $checkword['test'] = 'fc34c561a34f';
        $checkword['prod'] = '01b2832ae2024a28';

        $url['test'] = 'http://osms.sit.sf-express.com:2080/osms/wbs/services/uploadUniversalIdentity.pub';
        $url['prod'] = 'https://osms.sf-express.com/osms/wbs/services/uploadUniversalIdentity.pub';

        $customerCode['test'] = 'OSMS_1';
        $customerCode['prod'] = 'OSMS_10840';

        $post = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();


        foreach ($post as $key => $value) {

            if ($key === "frontId" || $key === "backId") {
                $credentialRequestDto = new CredentialRequestDto();

                $credentialRequestDto->bno = $post['awb'];
                $credentialRequestDto->image = base64_encode(file_get_contents($this->getRequest()->getFiles($key === 'frontId' ? 'front-upload' : 'back-upload')['tmp_name']));
                $credentialRequestDto->fileName = $post['awb'] . '_' . $key . '.jpg';
                $credentialRequestDto->fileType = '001';
                $credentialRequestDto->cardId = $post['cardId'];
                $credentialRequestDto->postFlag = '0';


                $jsonData = json_encode($credentialRequestDto);
                $encryData = base64_encode($jsonData);
                $integrityStr = base64_encode(md5($jsonData . $checkword[$active]));

                $data = $encryData;
                $validateStr = $integrityStr;

                $map = [
                    "data" => $data,
                    "validateStr" => $validateStr,
                    "customerCode" => $customerCode[$active]
                ];

                $responseJson = $this->sendHttpPost($url[$active], json_encode($map));
                $decoded = json_decode($responseJson, true);

                // Create Order Record
                $uploadIdModel = $this->_uploadId->create();
                $uploadIdModel->setData('customer_id', $this->_customerSession->getCustomer()->getId());
                $uploadIdModel->setData('image_type', "jpg");
                $uploadIdModel->setData('awb_code', $post['awb']);
                $uploadIdModel->setData('code', $decoded['code']);
                $uploadIdModel->setData('result', $decoded['result']);
                $uploadIdModel->setData('message', $decoded['message']);

                $uploadIdModel->save();

                $resultRedirect->setPath('portal/uploadid/confirm');


            }
        }

        return $resultRedirect;
    }

    function sendHttpPost($url, $data)
    {
        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

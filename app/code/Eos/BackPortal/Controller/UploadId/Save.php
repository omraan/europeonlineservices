<?php

namespace Eos\BackPortal\Controller\UploadId;

use Eos\Base\Model\UploadIdFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

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

    public function __construct(
        Context $context,
        Session $customerSession,
        DateTime $dateTime,
        UploadIdFactory $uploadId
    ) {
        $this->_customerSession = $customerSession;
        $this->_dateTime = $dateTime;
        $this->_uploadId = $uploadId;
        parent::__construct($context);
    }
    public function execute()
    {
        $active = 'prod';

        //API Key
        $checkword['test'] = 'fc34c561a34f';
        $checkword['prod'] = '01b2832ae2024a28';

        $url['test'] = 'http://osms.sit.sf-express.com:2080/osms/wbs/services/uploadIdentityService.pub';
        $url['prod'] = 'https://osms.sf-express.com/osms/wbs/services/uploadIdentityService.pub';

        $customerCode['test'] = 'OSMS_1';
        $customerCode['prod'] = 'OSMS_10840';

        $post = $this->getRequest()->getParams();


        //$this->_customerSession->getCustomer()->getId()

        $resultRedirect = $this->resultRedirectFactory->create();

        foreach($post as $key => $value) {

            if($key === "frontId" || $key === "backId" ) {

                $imageType = $key;

                if($value !== "") {

                    $ch = curl_init();
                    $data_array = array(
                        "name" => $this->_customerSession->getCustomer()->getName(),
                        'bno' => $post['awb'],
                        'image' => $value
                    );
                    $dataEncode = json_encode($data_array);

                    //base64 Encryption
                    $data = base64_encode($dataEncode);

                    //Generating the validation string
                    $validateStr = base64_encode(md5(utf8_encode($dataEncode) . $checkword[$active], false));

                    $headers = [
                        "Content-Type" => "application/json"

                    ];

                    $postArray = array(
                        'data' => $data,
                        'validateStr' => $validateStr,
                        'customerCode' => $customerCode[$active]
                    );

                    $postFields = http_build_query($postArray, '', '&');

                    //  $postFields = "data=" . $data . "&validateString=" . $validateStr . "&customerCode=OSMS_10840";

                    curl_setopt($ch, CURLOPT_URL, $url[$active]);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                    curl_setopt($ch, CURLOPT_VERBOSE, 1);
                    curl_setopt($ch, CURLOPT_HTTP_CONTENT_DECODING, true);


                    $resp = curl_exec($ch);

                    if($e = curl_error($ch)){
                        $this->messageManager->addErrorMessage(__('Something went wrong. Please make sure that the format of images are .jpeg or .png. If this does not work, please contact us by mailing to info@europeonlineservices.com'));

                        $resultRedirect->setPath('orders/uploadid/create');
                    }else {
                        curl_close($ch);

                        $decoded = json_decode($resp, true);

                        // Create Order Record
                        $uploadIdModel = $this->_uploadId->create();
                        $uploadIdModel->setData('customer_id', $this->_customerSession->getCustomer()->getId());
                        $uploadIdModel->setData('image_type', $imageType);
                        $uploadIdModel->setData('awb_code', $post['awb']);
                        $uploadIdModel->setData('code', $decoded['code']);
                        $uploadIdModel->setData('result', $decoded['result']);
                        $uploadIdModel->setData('message', $decoded['message']);

                        $uploadIdModel->save();

                        $resultRedirect->setPath('portal/uploadid/confirm');

                    }
                }
            }

        }

        return $resultRedirect;
    }
}

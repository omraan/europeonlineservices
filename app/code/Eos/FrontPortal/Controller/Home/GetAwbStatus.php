<?php

namespace Eos\FrontPortal\Controller\Home;
use Magento\Framework\App\Action\Context;


class GetAwbStatus extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        Context $context

    ) {
        parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();

        $xml = '<?xml version="1.0"?>
                    <Request service="RouteService" lang="en">
                      <Head>OSMS_10840</Head>
                      <Body>
                        <Route tracking_type="1" tracking_number="' . $post['awb_code'] . '"/>
                      </Body>
                  </Request>';

        //API Key
        $checkword = '01b2832ae2024a28';
        //base64 Encryption
        $data = base64_encode($xml);

        //Generating the validation string
        $validateStr = base64_encode(md5($xml . $checkword, false));

        //request URL
        $pmsLoginAction = 'https://osms.sf-express.com/osms/services/OrderWebService?wsdl';

        try {
            $client = new \SoapClient($pmsLoginAction);
            $client->__setLocation($pmsLoginAction);
            $result = $client->sfexpressService(['data' => $data, 'validateStr' => $validateStr, 'customerCode' => 'OSMS_10840']);

            $data = json_decode(json_encode($result), true);
            $simpleXml = new \SimpleXMLElement($data['Return']);
            $resultArray = $simpleXml->xpath('/Response/Body/RouteResponse/Route');
            echo json_encode($resultArray);
        } catch (Exception $e) {
            exit($e);
        }

    }
}

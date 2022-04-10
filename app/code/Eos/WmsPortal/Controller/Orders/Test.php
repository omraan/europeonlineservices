<?php

namespace Eos\WmsPortal\Controller\Orders;

use Eos\Base\Helper\Email;
use Magento\Framework\App\Action\Context;

class Test extends \Magento\Framework\App\Action\Action
{


    /** @var Email */

    protected $helperEmail;

    // Instantiating the Context object is no longer required
    public function __construct(
        Context $context,
        Email $helperEmail
    ) {
        // Calling parent::__construct() is also no longer needed
        $this->helperEmail = $helperEmail;
        parent::__construct($context);
    }

    public function execute()
    {
        try {

            // template variables pass here
            $templateVars = [
                'msg' => 'test',
                'msg1' => 'test1'
            ];

            $this->helperEmail->sendTestEmail(2, 'omraan.timmerarends@europeonlineservices.com',$templateVars);

        } catch (\Exception $e) {
            echo "doest not work";
        }
    }
}

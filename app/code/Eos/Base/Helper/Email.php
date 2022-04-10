<?php
namespace Eos\Base\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;

class Email extends AbstractHelper
{
    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    /** @var CustomerRepositoryInterface */
    protected $_customerRepositoryInterface;

    /** @var AddressFactory */
    protected $_addressFactory;

    public function __construct(
        Context                     $context,
        TransportBuilder            $transportBuilder,
        StoreManagerInterface       $storeManager,
        StateInterface              $state,
        CustomerRepositoryInterface $customerRepositoryInterface,
        AddressFactory $_addressFactory
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_addressFactory = $_addressFactory;

        parent::__construct($context);
    }

    public function sendCustomerEmail($templateId, $customer_id, $templateVars)
    {
        // this is an example and you can change template id,fromEmail,toEmail,etc as per your need.
        $fromEmail = 'noreply@europeonlineservices.com';  // sender Email id
        $fromName = 'Europe Online Services';             // sender Name
        $customer = $this->_customerRepositoryInterface->getById($customer_id);
        $toEmail = $customer->getEmail();
        $address = $this->_addressFactory->create()->load($customer->getDefaultBilling());

        $templateVars['customer_email'] = $customer->getEmail();
        $templateVars['customer_firstname'] = $customer->getFirstname();
        $templateVars['customer_lastname'] = $customer->getLastname();
        $templateVars['customer_street'] = str_replace("\n", " ", $address->getData('street'));
        $templateVars['customer_city'] = $address->getData('city');
        $templateVars['customer_postcode'] = $address->getData('postcode');
        $templateVars['customer_country'] = $address->getData('country');
        $templateVars['date_today'] = date("m-d-Y");

        try {


            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    public function sendErrorEmail($templateId, $templateVars)
    {
        // this is an example and you can change template id,fromEmail,toEmail,etc as per your need.
        $fromEmail = 'noreply@europeonlineservices.com';  // sender Email id
        $fromName = 'Europe Online Services';             // sender Name
        $toEmail = 'omraan.timmerarends@europeonlineservices.com';

        try {
            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

}

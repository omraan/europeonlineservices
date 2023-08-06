<?php

namespace Eos\Base\ViewModel;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\SessionFactory;

class CustomerViewModel extends BaseViewModel 
{
    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    public function __construct(
        SessionFactory $customerSession,
        AddressRepositoryInterface $addressRepository
    ) {
        parent::__construct($customerSession);
        $this->addressRepository = $addressRepository;
    }

    public function getCustomerAddress(): ?AddressInterface
    {
        $billingId = $this->customerSession->create()->getCustomer()->getDefaultBilling();
        if ($billingId) {
            try {
                return $this->addressRepository->getById($billingId);
            } catch (\Exception $e) {
                // Handle the exception if needed
            }
        }
        return null;
    }

    // Other common methods can also be defined here
}
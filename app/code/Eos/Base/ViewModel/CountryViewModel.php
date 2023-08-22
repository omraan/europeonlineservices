<?php

namespace Eos\Base\ViewModel;

use Eos\Base\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

class CountryViewModel extends BaseViewModel
{
    private $countryCollectionFactory;

    protected $countryCollection;

    public function __construct(
        CountryCollectionFactory $countryCollectionFactory
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    public function getCountries()
    {
        $this->countryCollection = $this->countryCollectionFactory->create();
        return $this;
    }
    public function getReceiverCountries($lang='en')
    {
        $this->countryCollection = $this->countryCollectionFactory->create();
        $this->filterByLang($lang)->filterByType('r');
        return $this;
    }
    public function getSenderCountries($lang='en')
    {
        $this->countryCollection = $this->countryCollectionFactory->create();
        $this->filterByLang($lang)->filterByType('s');
        return $this;
    }

    public function filterById($id)
    {
        $this->countryCollection->addFieldToFilter('entity_id', ['eq' => $id]);
        return $this;
    }

    public function filterByCountryCode($code)
    {
        $this->countryCollection->addFieldToFilter('country_code', ['eq' => $code]);
        return $this;
    }

    public function filterByType($type)
    {
        $this->countryCollection->addFieldToFilter('country_type', ['eq' => $type]);
        return $this;
    }

    public function filterByLang($lang)
    {
        $this->countryCollection->addFieldToFilter('country_lang', ['eq' => $lang]);
        return $this;
    }

    public function sortCountries()
    {
        $this->countryCollection->setOrder('country_title', 'ASC');
    }

    public function getFirstItem()
    {
        return $this->countryCollection->getFirstItem();
    }

    public function getItems()
    {
        $this->sortCountries();
        return $this->countryCollection->getItems();
    }

    public function getSize()
    {
        return $this->countryCollection->getSize();
    }
}
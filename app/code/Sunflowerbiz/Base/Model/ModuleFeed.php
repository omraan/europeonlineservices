<?php

namespace Sunflowerbiz\Base\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Zend\Http\Client\Adapter\Curl as CurlClient;
use Zend\Http\Response as HttpResponse;
use Zend\Uri\Http as HttpUri;
use Magento\Framework\Json\DecoderInterface;
use SimpleXMLElement;
use Magento\Framework\Locale\Resolver;

/**
 * Sunflowerbiz Base admin module feed model
 */
class ModuleFeed 
{
	
    const EXTENSIONS_PATH = 'sfbase_extensions';
    const URL_FEEDS  = 'https://www.sunflowerbiz.com/index.php?route=feed/m2';
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * @var Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var Magento\Framework\Module\Manager
     */
    protected $_moduleManager;
    /**
     * @var CurlClient
     */
    protected $curlClient;
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;/**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem;
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

  /**
     * @var Resolver
     */
    private $localeResolver;
 
	
	 /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    protected $_notifierPool;
	
	 /**
     * @var \Magento\Framework\UrlInterface  
     */
    protected $urlBuilder;
	 /**
     * @var \Magento\Framework\UrlInterface  
     */
    protected $backendConfig;

  
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		\Magento\Framework\Notification\NotifierInterface $notifierPool,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        \Sunflowerbiz\Base\Model\Serializer $serializer,
        DecoderInterface $jsonDecoder,
		Resolver $localeResolver,
		CurlClient $curl,
        array $data = []
    ) {
        $this->_backendAuthSession  = $backendAuthSession;
        $this->_moduleList = $moduleList;
        $this->_moduleManager = $moduleManager;
        $this->_productMetadata = $productMetadata;
	   	$this->_notifierPool = $notifierPool;
	   	$this->urlBuilder = $urlBuilder;
	   	$this->backendConfig = $backendConfig;
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->moduleReader = $moduleReader;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
        $this->jsonDecoder = $jsonDecoder;
		$this->localeResolver = $localeResolver;
    }

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
       
		$this->_feedUrl = self::URL_FEEDS.'&l='.(__("Extensions Information")=="插件信息"?"cn":"en");

        $urlInfo = parse_url($this->urlBuilder->getBaseUrl());
        $domain = isset($urlInfo['host']) ? $urlInfo['host'] : '';

        $url = $this->_feedUrl . '&h=' . urlencode($domain);

        $modulesParams = [];
        foreach($this->getAllSunflowerbizModules() as $key => $module) {
            $key = str_replace('Sunflowerbiz_', '', $key);
            $modulesParams[] = $key . ',' . $module['setup_version'];
        }
		

        if (count($modulesParams)) {
			$url .= '&m='. base64_encode(implode(';', $modulesParams));
        }
		$ed = $this->_productMetadata->getEdition();
       	$url .= '&p='.$ed;
        return $url;
    }
	
	 /**
     * Get Sunflowerbiz config info
     *
     * @return $this
     */
    public function getConfigData($field)
    {
        return $this->backendConfig->getValue($field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;
    }

    /**
     * Get Sunflowerbiz extension info
     *
     * @return $this
     */
    public function getAllSunflowerbizModules()
    {
        $modules = [];
        foreach($this->_moduleList->getAll() as $moduleName => $module) {
            if (strpos($moduleName, 'Sunflowerbiz_') !== false && $this->_moduleManager->isEnabled($moduleName) ) {
                $modules[$moduleName] = $module;
            }
        }
        return $modules;
    }
	 /**
     * Get Sunflowerbiz getModuleList info
     *
     * @return $this
     */
    public function getModuleList()
    {
        return $this->_moduleList;
    }


   
    /**
     * Returns the cURL client that is being used.
     *
     * @return CurlClient
     */
    public function getCurlClient()
    {
        if ($this->curlClient === null) {
            $this->curlClient = new CurlClient();
        }

        return $this->curlClient;
    }
	
	/**
     * Retrieve update frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return 86400;
    }
	
	/**
     * Retrieve top notify frequency
     *
     * @return int
     */
    public function DisplayFrequency()
    {
		$time = time();
        $frequency = $this->getFrequency();
        if ( ($this->getFrequency() + $this->getLastUpdate() > $time)
        ) {
            return false;
        }
        return true;
    }
	
	 /**
     * @return string
     */
    public function getXML($reload=false)
    {
		$html="";
		$xml =$this->cache->load(self::EXTENSIONS_PATH);
		
		if($reload)
		$xml=false;
	
		if (!empty($xml)  && stristr($xml,'rss')) {
			return $this->serializer->unserialize($xml);
		}
		
		 try {
            $curlClient = $this->getCurlClient();

            $location = $this->getFeedUrl();
			
            $uri = new HttpUri($location);

            $curlClient->setOptions([
                'timeout'   => 8
            ]);

            $curlClient->connect($uri->getHost(), $uri->getPort());
            $curlClient->write('GET', $uri, 1.0);
            $data = HttpResponse::fromString($curlClient->read());

            $curlClient->close();
			$xml=$data->getContent();
			
			if (!empty($xml) && stristr($xml,'rss')) {
                $this->cache->save($this->serializer->serialize($xml), self::EXTENSIONS_PATH);
				$this->setLastUpdate();
            }

        } catch (\Exception $e) {
            return false;
        }


        return $xml;
    }
	 /**
     * Read info about extension from composer json file
     * @param $moduleCode
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getModuleInfo($moduleCode)
    {
        try {
            $dir = $this->moduleReader->getModuleDir('', $moduleCode);
            $file = $dir . '/composer.json';

            $string = $this->filesystem->fileGetContents($file);
            $json = $this->jsonDecoder->decode($string);
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $json = [];
        }

        return $json;
    }
	
	
	 /**
     * Retrieve SimpleXMLElement
     *
     * @return 
     */
    public function getXMLobj()
    {
		$xml=$this->getXML();
		$feedXml  = new SimpleXMLElement($xml);
        return $feedXml;
    }
	
	 /**
     * Retrieve data
     *
     * @return int
     */
    public function getAuthData($field)
    {
       return $this->_backendAuthSession->getData($field);
    }
	
	 /**
     * Retrieve data
     *
     * @return int
     */
    public function setAuthData($field,$value)
    {
        return $this->_backendAuthSession->setData($field,$value);
    }

	
	 /**
     * Retrieve last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->serializer->unserialize($this->cache->load('sunflowerbiz_admin_notifications_lastcheck'));
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate()
    {
        $this->cache->save($this->serializer->serialize(time()), 'sunflowerbiz_admin_notifications_lastcheck');
        return $this;
    }
}

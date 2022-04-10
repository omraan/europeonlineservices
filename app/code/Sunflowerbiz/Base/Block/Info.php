<?php


namespace Sunflowerbiz\Base\Block;

use Magento\Framework\App\State;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Zend\Http\Client\Adapter\Curl as CurlClient;
use Zend\Http\Response as HttpResponse;
use Zend\Uri\Http as HttpUri;
use Magento\Framework\Json\DecoderInterface;
use SimpleXMLElement;
use Magento\Framework\Locale\Resolver;

class Info extends \Magento\Config\Block\System\Config\Form\Fieldset
{
	
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $_layoutFactory;
    
    /**
     * @var \Sunflowerbiz\Base\Model\Serializer
     */
    protected $serializer;
    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $cronFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader
     */
    private $reader;
    /**
     * @var CurlClient
     */
    protected $curlClient;
	
	 /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;
	/**
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
     * @var \Sunflowerbiz\Base\Model\ModuleFeed
     */
    private $_modulefeed;


    /**
     * Info constructor.
     *
     * @param \Magento\Backend\Block\Context                               $context
     * @param \Magento\Backend\Model\Auth\Session                          $authSession
     * @param \Magento\Framework\View\Helper\Js                            $jsHelper
     * @param \Magento\Framework\View\LayoutFactory                        $layoutFactory
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList              $directoryList
     * @param \Magento\Framework\App\DeploymentConfig\Reader               $reader
     * @param \Magento\Framework\App\ResourceConnection                    $resourceConnection
     * @param \Magento\Framework\App\ProductMetadataInterface              $productMetadata
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $cronFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\DeploymentConfig\Reader $reader,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        \Sunflowerbiz\Base\Model\Serializer $serializer,
        \Sunflowerbiz\Base\Model\ModuleFeed $modulefeed,
        DecoderInterface $jsonDecoder,
		Resolver $localeResolver,
		CurlClient $curl,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);

        $this->_moduleList    = $moduleList;
        $this->_layoutFactory = $layoutFactory;
        $this->_scopeConfig   = $context->getScopeConfig();
        $this->cronFactory = $cronFactory;
        $this->directoryList = $directoryList;
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $productMetadata;
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->moduleReader = $moduleReader;
        $this->serializer = $serializer;
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->jsonDecoder = $jsonDecoder;
		$this->localeResolver = $localeResolver;
		$this->_modulefeed = $modulefeed;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $html .= $this->getContent($element);

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
	


    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $layout = $this->_layoutFactory->create();

            $this->_fieldRenderer = $layout->createBlock(
                \Magento\Config\Block\System\Config\Form\Field::class
            );
        }

        return $this->_fieldRenderer;
    }
	
	 /**
     * @return string
     */
    private function getContent($fieldset)
    {
		$html="";
		$modules = $this->_moduleList->getNames();
		$dispatchResult = new \Magento\Framework\DataObject($modules);
        $modules = $dispatchResult->toArray();
        sort($modules);
		$installedmodule=array();
        foreach ($modules as $moduleName) {
            if (strstr($moduleName, 'Sunflowerbiz') === false || $moduleName === 'Sunflowerbiz_Base' ) 
                continue;
            $installedmodule[]=$moduleName;
        }
		
		$xml=$this->_modulefeed->getXML(true);
		
		 $feedXml  = new SimpleXMLElement($xml);
		  if ($feedXml && $feedXml->channel->extraCss && $feedXml->channel->extraCss) {
		  	$html.='<div><style>'.$feedXml->channel->extraCss.'</style></div>';
		  }

		  if ($feedXml && $feedXml->channel->topHtml && $feedXml->channel->topHtml) {
		  	$html.='<div class="sf_extension_tophtml">'.html_entity_decode($feedXml->channel->topHtml).'</div>';
		  }
		  
		$installhtml="";
		
		$allExtension=[];
		 if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
				foreach ($feedXml->channel->item as $item) {
					$scode = (string)$item->code;
					$allExtension[$scode]=$item;
			}
		}
		$needUpdate=false;
		foreach($installedmodule as $code){	
					$module=$this->_modulefeed->getModuleInfo($code);
					if (!is_array($module) ||
					   !array_key_exists('version', $module) ||
					   !array_key_exists('description', $module)
					) {
						continue;
					}
							
					$currentVer = $module['version'];
					$hasNewVersion=false;
					
					
					$item=isset($allExtension[strtolower($code)])?$allExtension[strtolower($code)]:false;
					$link=$extra=$nversion="";
					$title=$code;
					if($item){
						$title=$item->title;
						$link=$item->link;
						$nversion=$item->version;
						$extra=$item->extra;
					}
					if(!empty($nversion) && version_compare($currentVer, $nversion, '<')){
					$hasNewVersion=true;
					$needUpdate=true;
					}
					
					$installhtml.='<div class="sf_extension_item '.($hasNewVersion?'hasNewVersion':'noNewVersion').'" ><div class="name"><a href="'.$link.'" target="_blank">'.$title.'</a>'.((isset($extra)&&$extra!="")?'<div class="itemextra">'.$extra.'</div>':'' ).'</div><div class="cversion"><span>'.__('Installed Version: ').'</span>'.$currentVer.'</div><div class="c2nversion"></div><div class="nversion">'.($hasNewVersion?'<span>'.__('Lastest Version: ').'</span>'.$nversion.'</div><div class="update"><a href="'.$link.'/?new" class="newversion " target="_blank">'.__("See what's New").'</a></div>':'<span>'.__('Lastest Version: ').'</span></div>'.$currentVer).'</div>';
		}	
		
		if($needUpdate){
		$html.='<div class="sf_update_account"><a href="https://www.sunflowerbiz.com/index.php?route=account/download" target="_blank">'.__("Get updates at your Sunflowerbiz.com account").'</a></div>';
		$html.=$installhtml;
		}else
		$html.='<div class="sf_noupdate_account">'.__("All installed extensions are the latest versions.").'</div>';
				
		 
		 
		  
		 if ($feedXml && $feedXml->channel->bottomHtml && $feedXml->channel->bottomHtml) {
		  	$html.='<div class="sf_extension_tophtml">'.html_entity_decode($feedXml->channel->bottomHtml).'</bottom>';
		  }
		  
		return $html;
		
	}

    /**
     * @param AbstractElement $fieldset
     * @return string
     */
    private function getSystemTime($fieldset)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2', '>=')) {
            $time = $this->resourceConnection->getConnection()->fetchOne("select now()");
        } else {
            $time = $this->_localeDate->date()->format('H:i:s');
        }
        return $this->getFieldHtml($fieldset, 'mysql_current_date_time', __("Current Time"), $time);
    }

    /**
     * @param $userInfo
     * @param $fieldset
     * @return string
     */
    private function fieldHtml($userInfo, $fieldset)
    {
        $label = __("Server User");

        return $this->getFieldHtml(
            $fieldset,
            'magento_user',
            $label,
            $userInfo
        );
    }

  
    /**
     * @param AbstractElement $fieldset
     * @param string $fieldName
     * @param string $label
     * @param string $value
     * @return string
     */
    private function getFieldHtml($fieldset, $fieldName, $label = '', $value = '')
    {
        $field = $fieldset->addField($fieldName, 'label', [
            'name'  => 'dummy',
            'label' => $label,
            'after_element_html' => $value,
        ])->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}

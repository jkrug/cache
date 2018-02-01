<?php

class base_html_cache
{

    //ToDo: Make configarable in the backend.
    protected $_aCachableControllers = array(
                                                'start',
                                                'alist',
                                                'details',
                                                'content'
                                            );

    protected $_cacheBackend = null;

    public function __construct( $oCacheBackend )
    {
        $this->_cacheBackend = $oCacheBackend;
    }

    /**
     * checks for a valid cache and if found, outputs it and skips the other rendering
     */
    public function processCache()
    {
        if( !$this->isCachableRequest() )
        {
            return;
        }

        $key = $this->getCacheKey();

        $sContent = $this->_cacheBackend->getCache($key);

        if(is_string($sContent))
        {
            exit($sContent);
        }
    }

    /**
     * Minify the html output
     *
     * @param $sValue string
     * @return string
     */
    protected function _minifyHtml( $sValue )
    {
        $aSearch = array( '/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s' );
        $aReplace = array( ' ', ' ' );

        $sMinified = preg_replace( $aSearch, $aReplace, $sValue );

        return $sMinified;
    }

    /**
     * Adds the default OXID version Tags.
     * Please keep this function with respect for OXID!
     *
     * @param $sOutput
     * @return full content string
     */
    public function addCacheVersionTags( $sOutput )
    {
        $oConf = oxRegistry::getConfig();
        // DISPLAY IT
        $sVersion  = $oConf->getVersion();
        $sEdition  = $oConf->getFullEdition();
        $sCurYear  = date("Y");
        $sShopMode = "";

        // SHOW ONLY MAJOR VERSION NUMBER
        $aVersion = explode('.', $sVersion);
        $sMajorVersion = reset($aVersion);


        // Replacing only once per page
        $sOutput = str_ireplace("</head>", "</head>\n  <!-- OXID eShop {$sEdition}, Version {$sMajorVersion}{$sShopMode}, Shopping Cart System (c) OXID eSales AG 2003 - {$sCurYear} - http://www.oxid-esales.com -->", ltrim($sOutput));

        return $sOutput;
    }

    /**
     * Create the Cache
     *
     * @param $sContent fully rendered content
     */
    public function createCache($sContent)
    {
        if( !$this->isCachableRequest() )
        {
            return;
        }
        $sContent = $this->_minifyHtml( $sContent );

        $sContent = $this->addCacheVersionTags($sContent);

        if (false == oxRegistry::getConfig()->isUtf()) {
            $sCharset = oxRegistry::getLang()->translateString('charset');

            $sContent = mb_convert_encoding($sContent, 'UTF-8', $sCharset);
        }

        $key = $this->getCacheKey();

        $this->_cacheBackend->setCache($key, $sContent);

    }

    /**
     * Check if this request could be cached.
     *
     * @return bool
     */
    public function isCachableRequest()
    {
        if( !in_array( $this->getClassName(), $this->_aCachableControllers) )
        {
            return false;
        }
        if( $this->sFunction )
        {
            return false;
        }
        $oUser = oxNew('oxuser');
        if ($oUser->loadActiveUser() !== false)
        {
            return false;
        }
        $oConf = oxRegistry::getConfig();
        
        // check for oxid version
        if (version_compare('4.7.0', $oConf->getVersion(), '<')) {
        	$partial = $oConf->getRequestParameter('renderPartial');
        }else{
        	$partial = $oConf->getParameter('renderPartial');
        }

        if(!empty($partial))
        {
            return false;
        }

        if($this->_hasBasketItems()){
            return false;
        }

        return true;
    }

    public function getClassName()
    {
        if(isset($this->sClassName))
        {
            return $this->sClassName;
        }
        $oConf = oxRegistry::getConfig();
        $oActView = $oConf->getActiveView();
        $sClassName = $oActView->getClassName();

       $this->sClassName = $sClassName;
       return $this->sClassName;
    }

    /**
     * I guess we should refactor this.
     * Anway the extending classes inject data via this. :-/
     *
     * @deprecated
     * @param $name
     * @param $value
     */
    public function __set( $name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Calclulate the Cache Key
     *
     * @return string cache Key
     */
    public function getCacheKey()
    {
        //ToDo: Change this for caching widgets
        $oUtilsServer = oxRegistry::get( "oxUtilsServer" );
        $requestUrl = $oUtilsServer->getServerVar( "REQUEST_URI" );
        $key = md5($requestUrl);

        return $key;
    }

    /**
     * Check if there are items in the basket which will lead to a non cachable request.
     * //ToDo: this could be skipped for caching most of the widgets. right?!
     *
     * @return bool
     */
    protected function _hasBasketItems(){
        $oBasket = oxRegistry::getSession()->getBasket();
        if($oBasket && $oBasket->getProductsCount() > 0){
            return true;
        }

        return false;
    }

}

<?php

/**
 * This file is part of a free OXID eShop module.
 * It is Open Source - feel free to use it! But PLEASE guys:
 * Respect the author and keep this note.
 *
 * Version:    2.0
 * Author:     Joscha Krug <support@makaira.io>
 * Author URI: https://www.makaira.io
 */

class base_html_cache
{

    //ToDo: Make configarable in the backend.
    //ToDo: make widgets configurable separate to skip some of them with full basket.
    protected $_aCachableControllers = array(
        //'oxwcategorytree', # For widgets we need to find a solution for oxstyle and oxscript or skip that in your theme.
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
    public function getCacheContent()
    {
        if( !$this->isCachableRequest() )
        {
            return false;
        }

        $key = $this->getCacheKey();

        $sContent = $this->_cacheBackend->getCache($key);

        if(is_string($sContent))
        {
            return $sContent;
        }

        return false;
    }

    /**
     * Minify the html output
     *
     * ToDo: Make configurable
     *
     * @param $sValue string
     * @return string
     */
    protected function _minifyHtml( $sValue )
    {
        return $sValue;
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
        $key = $this->getClassName() . '_' . md5($requestUrl);

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

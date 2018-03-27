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

    //ToDo: make widgets configurable separate to skip some of them with full basket.
    protected $_aCachableControllers = array();

    protected $_cacheBackend = null;

    public function __construct( $oCacheBackend )
    {
        $this->_cacheBackend = $oCacheBackend;
        $this->_aCachableControllers = $this->getCachableControllers();
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

        //Check if any filter from makaira module has been set!
        if ( $this->sClassName == 'alist' ) {
            //OXID standard session filter
            $aSessionFilter = oxRegistry::getSession()->getVariable( 'session_attrfilter' );
            $sActCat = oxRegistry::getConfig()->getRequestParameter( 'cnid' );
            $aAttrFilter = array_filter($aSessionFilter[$sActCat]);
            if(!empty($aAttrFilter)) {
                return false;
            }
            //Makaira filter
            //we need to use method_exists as there is a bug in oxmodule::isActive() prior OXID 4.7.11
            if(method_exists(oxRegistry::get( 'oxViewConfig' ), 'getAggregationFilter')) {
                $oxModule = oxNew( 'oxModule' );
                $oxModule->load( 'makaira/connect' );
                if ( $oxModule->isActive() ) {
                    $aFilter = array_filter( oxRegistry::get( 'oxViewConfig' )->getAggregationFilter() );
                    if ( ! empty( $aFilter ) ) {
                        return false;
                    }
                }
            }
        }
        //END Filter check

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
        
       	$partial = $oConf->getRequestParameter('renderPartial');

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

    /**
     * Returns cachable controllers according to backend settings
     *
     * @return array
     */
    protected function getCachableControllers() {
        return oxRegistry::getConfig()->getShopConfVar('aCachedClasses',null,'module:jkrug/cache');
    }

}

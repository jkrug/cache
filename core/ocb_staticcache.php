<?php

class ocb_staticcache
{

    protected $_aCachableControllers = array(
                                                'start',
                                                'alist',
                                                'details',
                                                'content'
                                            );

    protected $_sCacheDir = null;

    public function processCache()
    {
        if( !$this->isCachableRequest() )
        {
            return;
        }

        $sCachePath = $this->getCacheFileName();

        if(!is_file($sCachePath))
        {
            return;
        }

        $sCacheData = file_get_contents($sCachePath);


        $aCacheData = json_decode($sCacheData, true);
        if (false == oxRegistry::getConfig()->isUtf()) {
            $sCharset = oxRegistry::getLang()->translateString('charset');
            foreach ($aCacheData as $sIndex => $scontent) {
                $aCacheData[$sIndex] = mb_convert_encoding($scontent, $sCharset, 'UTF-8');
            }
        }

        $iCacheTime     = $aCacheData['timestamp'];
        $content        = $aCacheData['content'];
        $iCacheLifetime = oxRegistry::getConfig()->getShopConfVar('iCacheLifetime',null,'module:ocb_staticcache');
        if( time() < $iCacheTime+$iCacheLifetime )
        {
            exit($content);
        }
        unlink( $sCachePath );
    }

    protected function _minifyHtml( $sValue )
    {
        $aSearch = array( '/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s' );
        $aReplace = array( ' ', ' ' );

        $sMinified = preg_replace( $aSearch, $aReplace, $sValue );

        return $sMinified;
    }

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

    public function buildCache($sContent)
    {
        if( !$this->isCachableRequest() )
        {
            return;
        }
        $sContent = $this->_minifyHtml( $sContent );

        $aCacheData                 = array();
        $aCacheData['controller']   = $this->getClassName();
        $aCacheData['content']      = $this->addCacheVersionTags($sContent);
        $aCacheData['timestamp']    = time();

        if (false == oxRegistry::getConfig()->isUtf()) {
            $sCharset = oxRegistry::getLang()->translateString('charset');

            foreach ($aCacheData as $sIndex => $scontent) {
                $aCacheData[$sIndex] = mb_convert_encoding($scontent, 'UTF-8', $sCharset);
            }
        }

        $sCacheData     = json_encode($aCacheData);

        $sCacheFileName = $this->getCacheFileName();

        file_put_contents($sCacheFileName, $sCacheData);

    }

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

    public function __set( $name, $value)
    {
        $this->$name = $value;
    }

    public function getCacheFileName()
    {
        $oUtilsServer = oxRegistry::get( "oxUtilsServer" );
        $requestUrl = $oUtilsServer->getServerVar( "REQUEST_URI" );
        $sFileName = md5($requestUrl);
        $sPath = $this->_getStaticCachePath();

        return $sPath . $sFileName . '.json';
    }

    protected function _hasBasketItems(){
        $oBasket = oxRegistry::getSession()->getBasket();
        if($oBasket && $oBasket->getProductsCount() > 0){
            return true;
        }

        return false;
    }

    protected function _getStaticCachePath(){
        if(!$this->_sCacheDir){
            $myConfig = oxRegistry::getConfig();

            //check for the Smarty dir
            $sCompileDir = $myConfig->getConfigParam('sCompileDir');
            $sCacheDir = $sCompileDir . "/ocb_cache/";
            if (!is_dir($sCacheDir)) {
                @mkdir($sCacheDir);
            }
            $this->_sCacheDir = $sCacheDir;
        }

        return $this->_sCacheDir;
    }
}

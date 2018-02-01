<?php
/**
 * Created by PhpStorm.
 * User: jkrug
 * Date: 16.01.18
 * Time: 16:44
 */

class file_backend
{
    public function __construct()
    {
        //ToDo: Initialize settings
    }

    public function setCache($key, $value)
    {
        $fullFileName = $this->getCacheFileName($key);

        file_put_contents($fullFileName, $value);

    }

    public function getCache($key)
    {
        $sCachePath = $this->getCacheFileName($key);

        if(!is_file($sCachePath))
        {
            return false;
        }

        $sCacheData = file_get_contents($sCachePath);

        if (false == oxRegistry::getConfig()->isUtf()) {
            $sCharset = oxRegistry::getLang()->translateString('charset');
            $sCacheData = mb_convert_encoding($sCacheData, $sCharset, 'UTF-8');
        }

        if( $this->_isTimestampValid($key) )
        {
            return $sCacheData;
        }
        $this->purgeCacheByKey($key);

        return false;

    }

    public function purgeCacheByKey($key)
    {
        $sCachePath = $this->getCacheFileName($key);

        if(is_file($sCachePath))
        {
            unlink( $sCachePath );
        }
    }

    public function flushFullCache()
    {
        //ToDo: implement function
    }

    public function getCacheFileName($key)
    {
        $sPath = $this->_getStaticCachePath();

        return $sPath . $key . '.json';
    }

    private function _getStaticCachePath(){
        if(!$this->_sCacheDir){
            $myConfig = oxRegistry::getConfig();

            //check for the Smarty dir
            $sCompileDir = $myConfig->getConfigParam('sCompileDir');
            $sCacheDir = $sCompileDir . "/jkrug_cache/";
            if (!is_dir($sCacheDir)) {
                @mkdir($sCacheDir);
            }
            $this->_sCacheDir = $sCacheDir;
        }

        return $this->_sCacheDir;
    }

    private function _isTimestampValid( $key )
    {
        $iCacheLifetime = oxRegistry::getConfig()->getShopConfVar('iCacheLifetime',null,'module:jkrug/cache');
        $this->_iCacheTime     = filemtime($this->_getStaticCachePath($key));


        if( time() < $this->_iCacheTime+$iCacheLifetime )
        {
            return true;
        }

        return false;
    }


}
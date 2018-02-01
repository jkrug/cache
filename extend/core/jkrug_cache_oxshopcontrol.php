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

class jkrug_cache_oxshopcontrol extends jkrug_cache_oxshopcontrol_parent
{

    protected function _process( $sClass, $sFunction, $aParams = null, $aViewsChain = null )
    {
        if(!isset($this->oCache))
        {
            //ToDo: Read from configuration, when there is more then one backend available
            $cacheBackendType = 'file_backend';

            $cacheBackend = oxRegistry::get($cacheBackendType);

            $this->oCache = oxNew('base_html_cache',$cacheBackend);
        }
        $this->oCache->sClassName = $sClass;
        $this->oCache->sFunction = $sFunction;

        parent::_process( $sClass, $sFunction, $aParams, $aViewsChain );
    }

    protected function _render($oViewObject)
    {
        if(!isAdmin() && isset($this->oCache))
        {
            $oCache = $this->oCache;
            $sCachedContent = $oCache->getCacheContent();

            if($sCachedContent !== false)
            {
                return $sCachedContent;
            }
        }

        $sContent = parent::_render($oViewObject);

        if(!isAdmin() && isset($this->oCache))
        {
            $this->oCache->createCache( $sContent );
        }

        return $sContent;
    }
}

<?php

class jkrug_cache_oxshopcontrol extends jkrug_cache_oxshopcontrol_parent
{
    
    protected function _process( $sClass, $sFunction, $aParams = null, $aViewsChain = null )
    {
        if(!isAdmin())
        {
            //ToDo: Read from configuration, when there is more then one backend available
            $cacheBackendType = 'file_backend';

            $cacheBackend = oxRegistry::get($cacheBackendType);

            $oCache = oxNew('base_html_cache',$cacheBackend);
            $oCache->sClassName = $sClass;
            $oCache->sFunction = $sFunction;
            $oCache->processCache();
        }
        parent::_process( $sClass, $sFunction, $aParams = null, $aViewsChain = null );
    }
    
}

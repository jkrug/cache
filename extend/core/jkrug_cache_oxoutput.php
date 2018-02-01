<?php

class jkrug_cache_oxoutput extends jkrug_cache_oxoutput_parent
{
    
    public function process($sValue, $sClassName)
    {
        $sValue = parent::process($sValue, $sClassName);
        
        if(!isAdmin())
        {
            //ToDo: Read from configuration, when there is more then one backend available
            $cacheBackendType = 'file_backend';

            $cacheBackend = oxRegistry::get($cacheBackendType);

            $oCache = oxNew('base_html_cache',$cacheBackend);
            $oCache->createCache( $sValue );
        }
        
        return $sValue;
    }
    
}
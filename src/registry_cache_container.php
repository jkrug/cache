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

class registry_cache_container
{
    /**
     * Magic getter to  return cached values.
     *
     * @param $name string
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    /**
     * Magic setter to save stuff.
     *
     * @param $name string
     * @param $value string
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param $sVarName
     * @param null $sShopId
     * @param string $sModule
     * @return mixed|object
     */
    public function getShopConfVar( $sVarName, $sShopId = null, $sModule = '' )
    {
        $name = $sVarName . $sShopId . $sModule;

        if (isset($this->$name)) {
            return $this->$name;
        }

        $res = oxRegistry::getConfig()->getShopConfVar( $sVarName, $sShopId, $sModule);

        $this->$name = $res;

        return $res;
    }

}


<?php

/**
 * This file is part of a free OXID eShop module.
 * It is Open Source - feel free to use it! But PLEASE guys:
 * Respect the author and keep the stuff correct.
 *
 * Version:    2.0
 * Author:     Joscha Krug <support@makaira.io>
 * Author URI: https://www.makaira.io
 */

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';
 
/**
 * Module information
 */
$aModule = array(
    'id'           => 'jkrug/cache',
    'title'        => 'Joscha Krug :: OXID Cache',
    'description'  => 'Get a better performance by caching some stuff and tweaking the performance.',
    // ToDo: Add Image
    'thumbnail'    => 'jkrug.jpg',
    'version'      => '2.0',
    'author'       => 'Joscha Krug',
    'url'          => 'https://www.makaira.io',
    'email'        => 'jk@makaira.io',
    'extend'       => array(
        'oxshopcontrol' => 'jkrug/cache/extend/core/jkrug_cache_oxshopcontrol',
        //ToDo: must be activated to cache widgets!
        //'oxwidgetcontrol' => 'jkrug/cache/extend/core/jkrug_cache_oxshopcontrol',
    ),
    'files'        => array(
        'base_html_cache'           => 'jkrug/cache/src/base_html_cache.php',
        'file_backend'              => 'jkrug/cache/src/backends/file_backend.php',
        'registry_cache_container'  => 'jkrug/cache/src/registry_cache_container.php'
    ),
    'settings'       => array(
        array(
            'group'     => 'main',
            'name'      => 'iCacheLifetime',
            'type'      => 'str',
            'value'     => '10'
        ),
        array(
            'group'     => 'main',
            'name'      => 'aCachedClasses',
            'type'      => 'arr',
            'value'     => array('alist','start','details','content')
        ),
    )
);

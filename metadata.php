<?php

/**
 * This file is part of a OXID Cookbook project
 *
 * Version:    1.0
 * Author:     Joscha Krug <krug@marmalade.de>
 * Author URI: http://www.marmalade.de
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
        'oxoutput'      => 'jkrug/cache/extend/core/jkrug_cache_oxoutput',
    ),
    'files'        => array(
        'base_html_cache' => 'jkrug/cache/src/base_html_cache.php',
        'file_backend'    => 'jkrug/cache/src/backends/file_backend.php'
    ),
    'settings'       => array(
        array(
            'group'     => 'main',
            'name'      => 'iCacheLifetime',
            'type'      => 'str',
            'value'     => '10'
        ),
    )
);

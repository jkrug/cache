Joscha Krug :: OXID Cache
=============================
A caching module to help tweaking the performance of your OXID eShop.

This module is a new and better version of the module described in the OXID Cookbook with the name ocb_staticcache.

Please keep in mind. This is ways more simple then the OXID eShop high performance option.
If you have a large shop and need a reverse proxy cache think of that well maintained solution.
The performance when really skipping the full application will be MUCH better and will keep your servers on nicer temperatures. 

If you need help, feel free to ask me or my team from [ScaleCommerce](https://scale.sc).

If you have questions to the [Makaira](https://www.makira.io/) project, we're also happy to help you.

What it does
------------

It implements some methods to make OXID eShop faster:
- a fullpage or widget cache
- a base implementation for the backends (file for single server, Redis (planned) for distributed setups)
- _planned:_ a hook so the cache can be invalidated on Makaira touches
- _planned:_ a cache class to reduce queries by saving stuff temporarily in the OXID Registry
- _planned:_ an option to retrieve the full data from Makaira so you don't need SQL queries anymore to build the product objects.

Installation
------------

1.    Copy the module to you modules folder to get <shoproot>/modules/jkrug/cache/
1.    create a empty vendormetadata.php in <shoproot>/modules/jkrug/
1.    Activate module in the OXID backend.
1.    Configure the module in the OXID backend.

Usage of the registry cache
---------------------------

I've seen soooo many modules and shops doing tons of queries that are executed hundreds or even thousands of
times on one page grabbing the same data again and again.
Since OXID uses the registry pattern, it is PRETTY simple to reduce those queries with keeping them in the memory.

So let me give you a view real life examples:

Number 1: oxConfig
------------------

At least in older versions of OXID eShop (maybe one could check that?) a `oxConfig->getShopConfVar()` hammers to the database all the time! No matter if you already 
retrieved the same configuration parameter already.
In most cases your configuriation will not change during a page load. Right?

So instead of doing:

```
// First call to the DB
$myVar = oxRegistry::getConfig->getShopConfVar('myVarName', null, 'oxmodule:jkrug/cache');
// Second call to the DB
$myVar = oxRegistry::getConfig->getShopConfVar('myVarName', null, 'oxmodule:jkrug/cache');
```
simply do this:

```
// First call to the DB
$myVar = oxRegistry::get('registry_cache_container')->getShopConfVar('myVarName', null, 'oxmodule:jkrug/cache');
// Get it from the memory - no more DB call.
$myVar = oxRegistry::get('registry_cache_container')->getShopConfVar('myVarName', null, 'oxmodule:jkrug/cache');
```

As always you should keep the downsides in mind. Doing this will make your module depending on this one.
To avaoid that you could of cause use a copy of the container class in your own module.

Number 2: Loading objects just once
-----------------------------------

Let's assume, you want to show a category page on all products also the logo and the name of the manufacturer.

What you would do is maybe something like this:

```
    // Extend the oxarticle objects and add these methods
    
    public function getManufacturerIconUrl()
    {
        $oManufacturer = oxNew('oxmanufacturer');
        $oManufacturer->load($this->getManufacturerId());
        return $oManufacturer->getIconUrl();
    }
    
    public function getManufacturerName()
    {
        $oManufacturer = oxNew('oxmanufacturer');
        $oManufacturer->load($this->getManufacturerId());
        return $oManufacturer->getTitle();
    }
```

But this means, that for each product in the list, you would create a new manufacturer object, 
load all its values from the database, and execute the `getIconUrl` method on it.
You would do that in every case. Even if you did all that for the same manufacturer in exactly the product before.

Here you could also use the container class to keep the data, once you've calculated the url for a manufacturer:

```
    // Again extending the product so there is no change in your template.
    
    // Instantiate the cache object directly in the constructor
    public function __construct()
    {
        parent::__construct();
        $this->cache = oxRegistry::get('registry_cache_container');
    }
    
    // Create the object, get thedata and cache it in the container.
    private function _createCache()
    {   
        $oManufacturer = oxNew('oxmanufacturer');
        $oManufacturer->load($this->getManufacturerId());
        $key = $this->getManufacturerId();
        $this->cache->{$key . '_link'} = $oManufacturer->getLink();
        $this->cache->{$key . '_icon'} = $oManufacturer->getIconUrl();
        $this->cache->{$key . '_title'} = $oManufacturer->getTitle();
    }
    
    // Same output like before
    public function getManufacturerIconUrl()
    {
        // First check if we could get the data from the cache
        if(!$this->cache->{$this->getManufacturerId().'_icon'})
        {
          // If the cache is not filled for that manufacturer yet, do so now.
          $this->_createCache();
        }

        return $this->cache->{$this->getManufacturerId().'_icon'};
    }
    
    // Same output like before
    public function getManufacturerName()
    {
        if(!$this->cache->{$this->getManufacturerId().'_title'})
        {
          $this->_createCache();
        }

        return $this->cache->{$this->getManufacturerId().'_title'};
    }
```

You see, that it is really simple to implement and saves you a lot of database calls.

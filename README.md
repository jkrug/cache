Joscha Krug :: OXID Cache
=============================
A caching module to help tweaking the performance of your OXID eShop.

This module is a new and better version of the module described in the OXID Cookbook with the name ocb_staticcache.

Please keep in mind. This is ways more simple then the OXID eShop high performance option.
If you have a large shop and need a reverse proxy cache think of that well maintained solution.

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
2.    create a empty vendormetadata.php in <shoproot>/modules/jkrug/
2.    Activate module in the OXID backend.
3.    Configure the module in the OXID backend.
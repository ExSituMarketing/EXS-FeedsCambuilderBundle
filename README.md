# EXS-FeedsCambuilderBundle

## Install

Require the bundle from packagist
```
$ composer require exs/feeds-cambuilder-bundle
```

Enable the bundle in AppKernel
```php
<?php
...
class AppKernel extends Kernel
{
    ...
    public function registerBundles()
    {
        $bundles = array(
            ...
            new EXS\FeedsCambuilderBundle\EXSFeedsCambuilderBundle(),
        );
    }
    ...
}
```

## Config

Some configuration is avaible to manage the cache.

```yml
# Default values
exs_feeds_cambuilder:
    cache_ttl: 300
    memcached_host: 'localhost'
    memcached_port: 11211
```

## Usage

```php
// Returns 100 performer Ids by default.
$performerIds = $container
    ->get('exs_feeds_cambuilder.feeds_reader')
    ->getLivePerformers()
;

// Can specify the number of performers to return
$performerIds = $container
    ->get('exs_feeds_cambuilder.feeds_reader')
    ->getLivePerformers(20)
;
```

A command is also available if you want to force refresh the cache.

```bash
$ app/console feeds:cambuilder:refresh-live-performers --env=prod --no-debug

// Can specify number of performers and cache lifetime
$ app/console feeds:cambuilder:refresh-live-performers --limit=500 --ttl=3600 --env=prod --no-debug
```

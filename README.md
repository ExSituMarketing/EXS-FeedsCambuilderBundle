# EXS-FeedsCambuilderBundle

[![Build Status](https://travis-ci.org/ExSituMarketing/EXS-FeedsCambuilderBundle.svg?branch=master)](https://travis-ci.org/ExSituMarketing/EXS-FeedsCambuilderBundle)

## Installation

This bundle uses [PHP's native Memcached objects](http://php.net/manual/en/class.memcached.php).

**Make sure the memcached module is enabled in your PHP's installation.**

Require the bundle using composer
```
$ composer require exs/feeds-cambuilder-bundle
```

Enable the bundle in AppKernel
```php
<?php
// app/AppKernel.php

class AppKernel extends Kernel
{
    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new EXS\FeedsCambuilderBundle\EXSFeedsCambuilderBundle(),
        );
    }
}
```

## Configuration

Some configuration is available to manage the cache.

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

A command is also available if you want to force refresh the memcached record.

```bash
$ app/console feeds:cambuilder:refresh-live-performers --env=prod --no-debug

// Can specify number of performers and cache lifetime
$ app/console feeds:cambuilder:refresh-live-performers --limit=500 --ttl=3600 --env=prod --no-debug
```

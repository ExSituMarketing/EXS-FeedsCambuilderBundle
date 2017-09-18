# EXS-FeedsCambuilderBundle

## Config

```yml
# Default values
exs_feeds_cambuilder:
    cache_ttl: 120
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

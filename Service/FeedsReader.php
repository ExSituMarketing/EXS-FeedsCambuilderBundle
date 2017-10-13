<?php

namespace EXS\FeedsCambuilderBundle\Service;

use GuzzleHttp\Client;

/**
 * Class FeedsReader
 *
 * @package EXS\FeedsCambuilderBundle\Service
 */
class FeedsReader
{
    /**
     * @var \Memcached
     */
    private $memcached;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var int
     */
    private $defaultTtl;

    /**
     * FeedsReader constructor.
     *
     * @param \Memcached $memcached
     * @param Client     $httpClient
     * @param int        $defaultTtl
     */
    public function __construct(\Memcached $memcached, Client $httpClient, $defaultTtl = 300)
    {
        $this->memcached = $memcached;
        $this->httpClient = $httpClient;
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Returns an array of live performer ids.
     *
     * @param int $limit
     *
     * @return array
     */
    public function getLivePerformers($limit = 100)
    {
        if (
            (false === $performers = $this->memcached->get($this->getCacheKey($limit)))
            || empty($performers)
        ) {
            $performers = $this->refreshLivePerformers($limit);
        }

        return $performers;
    }

    /**
     * @param int $limit
     * @param int $ttl
     *
     * @return array
     */
    public function refreshLivePerformers($limit = 100, $ttl = null)
    {
        $performers = [];

        $body = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SMLQuery>
    <Options MaxResults="{limit}" />
    <AvailablePerformers QueryId="123" CountTotalResults="true" PageNum="1" Exact="true">
        <Include />
        <Constraints>
            <StreamType>live</StreamType>
        </Constraints>
    </AvailablePerformers>
</SMLQuery>
XML;

        try {
            $response = $this->httpClient->post('http://affiliate.streamate.com/SMLive/SMLResult.xml', [
                'headers' => ['Content-Type' => 'text/xml'],
                'body' => str_replace('{limit}', (int)$limit, $body),
                'timeout' => 10.0,
                'http_errors' => false,
            ]);

            if (200 === $response->getStatusCode()) {
                $responseContent = $response->getBody()->getContents();

                $content = new \SimpleXMLElement($responseContent);

                foreach ($content->xpath('AvailablePerformers/Performer') as $performer) {
                    $performers[] = (string)$performer['Id'];
                }
            }

            $this->memcached->set($this->getCacheKey($limit), $performers, $ttl ?: $this->defaultTtl);
        } catch (\Exception $e) {
            $performers = [];
        }

        return $performers;
    }

    /**
     * Requests live performers from AWE api, extracts the performer ids and set the result in cache.
     *
     * @param int $limit
     *
     * @return string
     */
    private function getCacheKey($limit)
    {
        return sprintf('CamBuilder%dLiveIds', $limit);
    }
}

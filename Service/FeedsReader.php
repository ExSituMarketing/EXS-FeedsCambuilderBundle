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
    private $cacheTtl;

    /**
     * FeedsReader constructor.
     *
     * @param \Memcached $memcached
     * @param Client     $httpClient
     * @param int        $cacheTtl
     */
    public function __construct(\Memcached $memcached, Client $httpClient, $cacheTtl = 120)
    {
        $this->memcached = $memcached;
        $this->httpClient = $httpClient;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getLivePerformers($limit = 100)
    {
        $cacheKey = $this->getCacheKey($limit);

        if (
            (false === $performers = $this->memcached->get($cacheKey))
            || empty($performers)
        ) {
            $performers = $this->refreshLivePerformers($limit);

            $this->memcached->set($cacheKey, $performers, $this->cacheTtl);
        }

        return $performers;
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    private function refreshLivePerformers($limit)
    {
        $performerIds = [];

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

        $response = $this->httpClient->post('http://affiliate.streamate.com/SMLive/SMLResult.xml', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body' => str_replace('{limit}', (int)$limit, $body),
            'timeout' => 5.0,
            'http_errors' => false,
        ]);

        if (200 === $response->getStatusCode()) {
            $responseContent = $response->getBody()->getContents();

            $content = new \SimpleXMLElement($responseContent);

            foreach ($content->xpath('AvailablePerformers/Performer') as $performer) {
                $performerIds[] = (string)$performer['Id'];
            }
        }

        return $performerIds;
    }

    /**
     * @param int $limit
     *
     * @return string
     */
    private function getCacheKey($limit)
    {
        return sprintf('CamBuilder%dLiveIds', $limit);
    }
}

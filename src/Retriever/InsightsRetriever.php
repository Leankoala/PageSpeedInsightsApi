<?php

namespace Leankoala\PageSpeedInsights\Retriever;

use GuzzleHttp\Client;
use Leankoala\PageSpeedInsights\Result\Insights;
use Psr\Http\Message\UriInterface;

class InsightsRetriever
{
    const STRATEGY_MOBILE = 'mobile';
    const STRATEGY_DESKTOP = 'desktop';

    const API_URL = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=%s&strategy=%s';
    const WEB_URL = 'https://developers.google.com/speed/pagespeed/insights/?url=%s';

    private $apiKey;

    /**
     * @var Client
     */
    public $client;

    public function __construct($apiKey = null, Client $client = null)
    {
        if (!$client) {
            $client = new Client();
        }
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function getMobileInsights(UriInterface $uri)
    {
        return $this->getInsights($uri, self::STRATEGY_MOBILE);
    }

    public function getDesktopInsights(UriInterface $uri)
    {
        return $this->getInsights($uri, self::STRATEGY_DESKTOP);
    }

    public function getInsights(UriInterface $uri, $strategy)
    {
        try {

            $response = $this->client->get(sprintf(self::API_URL, urlencode((string)$uri), $strategy));
            $plainResult = (string)$response->getBody();
            $jsonResult = json_decode($plainResult, true);

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new RetrieverException($e->getResponse()->getBody());
        } catch (\Exception $e) {
            throw new RetrieverException($e->getMessage());
        }

        return new Insights($jsonResult);
    }

    public static function getWebUrl(UriInterface $uri)
    {
        return sprintf(self::WEB_URL, urlencode((string)$uri));
    }
}
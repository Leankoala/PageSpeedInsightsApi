<?php

namespace Leankoala\PageSpeedInsights\Retriever;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Leankoala\PageSpeedInsights\Result\Insights;
use Psr\Http\Message\UriInterface;

class InsightsRetriever
{
    const STRATEGY_MOBILE = 'mobile';
    const STRATEGY_DESKTOP = 'desktop';

    const API_URL = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=%s&strategy=%s';
    const WEB_URL = 'https://developers.google.com/speed/pagespeed/insights/?url=%s';

    const ERROR_REASON_INVALID_KEY = 'keyInvalid';

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

    private function getEndpoint(UriInterface $uri, $strategy)
    {
        $endpoint = sprintf(self::API_URL, urlencode((string)$uri), $strategy);

        if ($this->apiKey) {
            $endpoint .= '&key=' . $this->apiKey;
        }

        return $endpoint;
    }

    public function getInsights(UriInterface $uri, $strategy)
    {
        try {
            $endpoint = $this->getEndpoint($uri, $strategy);
            $response = $this->client->get($endpoint);
            $plainResult = (string)$response->getBody();
            $jsonResult = json_decode($plainResult, true);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new RetrieverException($e->getResponse()->getBody());
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $plainResult = (string)$response->getBody();
            $jsonResult = json_decode($plainResult, true);

            if (array_key_exists('error', $jsonResult)) {
                if ($jsonResult['error']['errors'][0]['reason'] == self::ERROR_REASON_INVALID_KEY) {
                    throw new InvalidApiKeyExeption('Invalid API Key.');
                }
            }

            throw new RetrieverException($e->getMessage());
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

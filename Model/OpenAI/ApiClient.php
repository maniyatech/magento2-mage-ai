<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Model\OpenAI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    /**
     * Default timeout for API requests (in seconds)
     */
    private const DEFAULT_REQUEST_TIMEOUT = 60;

    /**
     * Guzzle HTTP client instance
     *
     * @var Client
     */
    protected Client $client;

    /**
     * ApiClient constructor
     *
     * @param string $baseUrl
     * @param string $token
     */
    public function __construct(string $baseUrl, string $token)
    {
        $config = [
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ];

        $this->client = new Client($config);
    }

    /**
     * Send a POST request to the specified URL with the given data
     *
     * @param string $url
     * @param array $data
     * @param array|null $options
     * @return ResponseInterface
     */
    public function post(string $url, array $data, ?array $options = []): ResponseInterface
    {
        try {
            return $this->client->post($url, $this->getPreparedOptions($options ?? [], $data));
        } catch (BadResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Prepare Guzzle options with default timeouts and JSON payload
     *
     * @param array $options
     * @param array $data
     * @return array
     */
    protected function getPreparedOptions(array $options, array $data): array
    {
        $options[RequestOptions::JSON] = $data;

        if (!isset($options['timeout'])) {
            $options['timeout'] = self::DEFAULT_REQUEST_TIMEOUT;
        }

        if (!isset($options['connect_timeout'])) {
            $options['connect_timeout'] = self::DEFAULT_REQUEST_TIMEOUT;
        }

        return $options;
    }
}

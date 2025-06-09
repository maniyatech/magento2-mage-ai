<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Model\CompletionRequest;

use ManiyaTech\MageAI\ViewModel\MageAI;
use ManiyaTech\MageAI\Model\OpenAI\ApiClient;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use Laminas\Json\Decoder;
use Laminas\Json\Json;

/**
 * Abstract base class for handling AI completion requests.
 */
abstract class AbstractCompletion
{
    public const TYPE = '';
    protected const CUT_RESULT_PREFIX = '';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var ApiClient|null
     */
    protected ?ApiClient $apiClient = null;

    /**
     * AbstractCompletion constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Generate the payload for the API request.
     *
     * @param string $text
     * @return array
     */
    abstract public function getApiPayload(string $text): array;

    /**
     * Initialize the API client.
     *
     * @return ApiClient
     * @throws InvalidArgumentException
     */
    private function getClient(): ApiClient
    {
        $token = $this->scopeConfig->getValue(MageAI::XML_PATH_API_KEY);
        if (empty($token)) {
            throw new InvalidArgumentException('API token is missing');
        }

        if ($this->apiClient === null) {
            $this->apiClient = new ApiClient(
                $this->scopeConfig->getValue(MageAI::XML_PATH_API_BASE_URL),
                $token
            );
        }

        return $this->apiClient;
    }

    /**
     * Get the query string from parameters.
     *
     * @param array $params
     * @return string
     */
    public function getQuery(array $params): string
    {
        return $params['prompt'] ?? '';
    }

    /**
     * Perform the API query and return result.
     *
     * @param string $prompt
     * @return string
     * @throws LocalizedException
     */
    public function query(string $prompt): string
    {
        $payload = $this->getApiPayload(self::htmlToPlainText($prompt));
        $model = $this->scopeConfig->getValue(MageAI::XML_PATH_API_MODEL);

        $endpoint = strpos($model, 'gpt') !== false
            ? '/v1/chat/completions'
            : '/v1/completions';

        $result = $this->getClient()->post($endpoint, $payload);

        $this->validateResponse($result);

        return $this->convertToResponse($result->getBody());
    }

    /**
     * Validate the query input.
     *
     * @param string $prompt
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateRequest(string $prompt): void
    {
        if (empty($prompt) || strlen($prompt) < 10) {
            throw new InvalidArgumentException('Invalid query (must be at least 10 characters)');
        }
    }

    /**
     * Validate the API response.
     *
     * @param ResponseInterface $result
     * @return void
     * @throws LocalizedException
     */
    protected function validateResponse(ResponseInterface $result): void
    {
        $status = $result->getStatusCode();

        if ($status === 401) {
            throw new LocalizedException(__('API unauthorized. Token could be invalid.'));
        }

        if ($status >= 500) {
            throw new LocalizedException(__('Server error: %1', $result->getReasonPhrase()));
        }

        $data = Decoder::decode((string)$result->getBody(), Json::TYPE_ARRAY);

        if (isset($data['error'])) {
            throw new LocalizedException(__(
                '%1: %2',
                $data['error']['type'] ?? 'unknown',
                $data['error']['message'] ?? 'unknown'
            ));
        }

        if (!isset($data['choices'])) {
            throw new LocalizedException(__('No results were returned by the server'));
        }
    }

    /**
     * Convert API response stream to usable text.
     *
     * @param StreamInterface $stream
     * @return string
     */
    public function convertToResponse(StreamInterface $stream): string
    {
        $streamText = (string)$stream;
        $data = Decoder::decode($streamText, Json::TYPE_ARRAY);
        $choices = $data['choices'] ?? [];
        $textData = reset($choices);

        // GPT-style response
        $text = $textData['message']['content'] ?? ($textData['text'] ?? '');
        $text = trim($text, "\" \n\r\t");

        if (str_starts_with($text, static::CUT_RESULT_PREFIX)) {
            $text = substr($text, strlen(static::CUT_RESULT_PREFIX));
        }

        return $text;
    }

    /**
     * Return the type of the completion.
     *
     * @return string
     */
    public function getType(): string
    {
        return static::TYPE;
    }

    /**
     * Convert HTML content to clean plain text.
     *
     * @param string $html
     * @return string
     */
    public function htmlToPlainText(string $html): string
    {
        $plainText = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $plainText = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $plainText);
        $plainText = strip_tags($plainText);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $plainText = html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plainText = preg_replace('/\s+/u', ' ', $plainText);
        return trim($plainText);
    }
}

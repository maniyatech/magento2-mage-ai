<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Model\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use ManiyaTech\MageAI\ViewModel\MageAI as MageAIModel;
use Magento\Framework\Exception\LocalizedException;

class Completions
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var MageAIModel
     */
    protected $mageaiModel;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param MageAIModel $mageaiModel
     */
    public function __construct(
        Curl $curl,
        Json $json,
        MageAIModel $mageaiModel
    ) {
        $this->curl = $curl;
        $this->mageaiModel = $mageaiModel;
        $this->json = $json;
    }

    /**
     * Get curl object
     *
     * @return Curl
     */
    private function getCurlClient()
    {
        return $this->curl;
    }

    /**
     * Set API header
     *
     * @return void
     * @throws LocalizedException
     */
    private function setHeaders()
    {
        $token = $this->mageaiModel->getApiSecret();
        if (!$token) {
            throw new LocalizedException(__('API Secret not found. Please check configuration'));
        }
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
        $this->getCurlClient()->setHeaders($headers);
    }

    /**
     * Make API request
     *
     * @param string $payload
     * @return string
     * @throws LocalizedException
     */
    protected function makeRequest($payload)
    {
        $this->setHeaders();
        $baseUrl = $this->mageaiModel->getApiBaseUrl();
        $model = $this->mageaiModel->getModel();
        if (strpos($model, 'gpt') !== false) {
            $endpoint = '/v1/chat/completions';
        } else {
            $endpoint = '/v1/completions';
        }
        $this->getCurlClient()->post(
            $baseUrl . $endpoint,
            $payload
        );
        return $this->validateResponse();
    }

    /**
     * Retrieve API payload
     *
     * @param string $prompt
     * @param int $maxToken
     * @return string
     */
    protected function getPayload($prompt, $maxToken = false)
    {
        $model = $this->mageaiModel->getModel();
        $payload =  [
            'model' => $model,
            'n' => 1,
            'temperature' => 0.5,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ];

        if ($maxToken) {
            $payload['max_tokens'] = $maxToken;
        }

        if (strpos($model, 'gpt') !== false) {
            $payload['messages'] = [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant. Provide only the main generated content without any greetings, introductions, or explanations.' //phpcs:ignore
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];
        } else {
            $payload['prompt'] = $prompt;
        }

        return $this->json->serialize($payload);
    }

    /**
     * Verify API response
     *
     * @return string string
     * @throws LocalizedException
     */
    public function validateResponse()
    {
        if ($this->getCurlClient()->getStatus() == 401) {
            throw new LocalizedException(__('Unauthorized response. Please check token.'));
        }

        if ($this->getCurlClient()->getStatus() >= 500) {
            throw new LocalizedException(__('Server error'));
        }

        $response = $this->json->unserialize($this->getCurlClient()->getBody());

        if (isset($response['error'])) {
            throw new LocalizedException(__($response['error']['message'] ?? 'Unknown Error'));
        }

        if (!isset($response['choices'])) {
            throw new LocalizedException(__('No results found from API response'));
        }

        $content = '';
        if (isset($response['choices'][0]['text'])) {
            $content = $response['choices'][0]['text'];
        } elseif (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
        }

        return trim($content);
    }

    /**
     * Generate product description based on type
     *
     * @param ProductInterface $product
     * @param string $type
     * @return string
     * @throws LocalizedException
     */
    public function generateProductDescription($product, $type)
    {
        $payload = $this->getProductDescriptionPayload($product, $type);
        return $this->makeRequest($payload);
    }

    /**
     * Generate content with custom prompt
     *
     * @param string $prompt
     * @return string
     * @throws LocalizedException
     */
    public function generateCustomContent($prompt)
    {
        $payload = $this->getPayload($prompt);
        return $this->makeRequest($payload);
    }
    /**
     * Retrieve product description payload
     *
     * @param ProductInterface $product
     * @param string $type
     * @return string
     */
    public function getProductDescriptionPayload($product, $type)
    {
        if ($type == 'short') {
            $prompt = $this->mageaiModel->getShortDescriptionPrompt();
            $wordCount = $this->mageaiModel->getShortDescriptionWordCount();
        } else {
            $prompt = $this->mageaiModel->getDescriptionPrompt();
            $wordCount = $this->mageaiModel->getDescriptionWordCount();
        }

        $attribute = $this->mageaiModel->getProductAttribute();
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $productAttribute */
        $productAttribute = $product->getResource()->getAttribute($attribute);
        $attributeLabel = $productAttribute->getDefaultFrontendLabel();
        $attributeValue = $productAttribute->getFrontend()->getValue($product);

        $formattedPrompt = sprintf(
            $prompt,
            $wordCount,
            $attributeLabel,
            $attributeValue
        );

        $maxToken = $this->mageaiModel->getMaxToken($type);
        return $this->getPayload($formattedPrompt, $maxToken);
    }
}

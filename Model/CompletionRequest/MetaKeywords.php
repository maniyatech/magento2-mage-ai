<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Model\CompletionRequest;

use ManiyaTech\MageAI\Api\CompletionRequestInterface;
use ManiyaTech\MageAI\ViewModel\MageAI;

class MetaKeywords extends AbstractCompletion implements CompletionRequestInterface
{
    public const TYPE = 'meta_keywords';
    protected const CUT_RESULT_PREFIX = 'Meta Keywords: ';

    /**
     * Get JS UI component configuration
     *
     * @return array|null
     */
    public function getJsConfig(): ?array
    {
        return [
            'attribute_label' => 'Meta Keywords',
            'container' => 'product_form.product_form.search-engine-optimization.container_meta_keyword',
            'prompt_from' => 'product_form.product_form.content.container_description.description',
            'target_field' => 'product_form.product_form.search-engine-optimization.container_meta_keyword.meta_keyword', //phpcs:ignore
            'component' => 'ManiyaTech_MageAI/js/button',
        ];
    }

    /**
     * Prepare API payload for completion request
     *
     * @param string $text
     * @return array
     */
    public function getApiPayload(string $text): array
    {
        parent::validateRequest($text);
        $model = $this->scopeConfig->getValue(MageAI::XML_PATH_API_MODEL);
        $payload =  [
            "model" => $model,
            "n" => 1,
            "temperature" => 0.5,
            "max_tokens" => 100,
            "frequency_penalty" => 0,
            "presence_penalty" => 0
        ];
        $metaKeyPrompt = $this->scopeConfig->getValue(MageAI::XML_PATH_PROMPT_META_KEYWORDS);
        if (strpos($model, 'gpt') !== false) {
            $payload['messages'] = [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant.',
                ],
                [
                    'role' => 'user',
                    'content' => sprintf($metaKeyPrompt, $text),
                ],
            ];
        } else {
            $payload['prompt'] = sprintf($metaKeyPrompt, $text);
        }
        return $payload;
    }
}

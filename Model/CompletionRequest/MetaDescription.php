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

/**
 * Meta Description Completion Request
 */
class MetaDescription extends AbstractCompletion implements CompletionRequestInterface
{
    public const TYPE = 'meta_description';
    protected const CUT_RESULT_PREFIX = 'Meta Description: ';

    /**
     * Get JS UI component configuration
     *
     * @return array|null
     */
    public function getJsConfig(): ?array
    {
        return [
            'attribute_label' => 'Meta Description',
            'container' => 'product_form.product_form.search-engine-optimization.container_meta_description',
            'prompt_from' => 'product_form.product_form.content.container_description.description',
            'target_field' => 'product_form.product_form.search-engine-optimization.container_meta_description.meta_description', //phpcs:ignore
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
        $metaDescPrompt = $this->scopeConfig->getValue(MageAI::XML_PATH_PROMPT_META_DESCRIPTION);

        $payload = [
            'model' => $model,
            'n' => 1,
            'temperature' => 0.5,
            'max_tokens' => 255,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ];

        if (strpos($model, 'gpt') !== false) {
            $payload['messages'] = [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => sprintf($metaDescPrompt, $text)],
            ];
        } else {
            $payload['prompt'] = sprintf($metaDescPrompt, $text);
        }

        return $payload;
    }
}

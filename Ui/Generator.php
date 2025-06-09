<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Ui;

use ManiyaTech\MageAI\Model\CompletionConfig;
use Magento\Ui\Component\Container;

class Generator extends Container
{
    /**
     * Get Configuration
     *
     * @return array
     */

    public function getConfiguration(): array
    {
        $config = parent::getConfiguration();

        /** @var CompletionConfig $completionConfig */
        $completionConfig = $this->getData('completion_config');

        return array_merge(
            $config,
            $completionConfig->getConfig(),
            [
                'settings' => [
                    'serviceUrl' => $this->context->getUrl('mageai/generate'),
                    'validationUrl' => $this->context->getUrl('mageai/validate'),
                ]
            ]
        );
    }
}

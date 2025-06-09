<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Model;

use ManiyaTech\MageAI\Api\CompletionRequestInterface;
use Magento\Framework\App\RequestInterface;
use ManiyaTech\MageAI\ViewModel\MageAI;

class CompletionConfig
{
    /**
     * @var MageAI
     */
    private MageAI $mageAI;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var CompletionRequestInterface[]
     */
    private array $pool;

    /**
     * @param MageAI $mageAI
     * @param RequestInterface $request
     * @param array $pool
     */
    public function __construct(
        MageAI $mageAI,
        RequestInterface $request,
        array $pool
    ) {
        $this->mageAI = $mageAI;
        $this->request = $request;
        $this->pool = $pool;
    }

    /**
     * Check Module enable and Seo button enable for content generate
     *
     * @return array
     */
    public function getConfig(): array
    {
        $enabled = $this->mageAI->getConfig(MageAI::XML_PATH_IS_ENABLED);
        $seoBtnEnabled = $this->mageAI->getConfig(MageAI::XML_PATH_PROMPT_META_BTN_ENABLED);
        if (!$enabled || !$seoBtnEnabled) {
            return [
                'targets' => []
            ];
        }

        $allowedStores = $this->mageAI->getEnabledStoreIds();
        $storeId = (int) $this->request->getParam('store', '0');
        if (!in_array($storeId, $allowedStores)) {
            return [
                'targets' => []
            ];
        }

        $targets = [];

        foreach ($this->pool as $config) {
            $targets[$config->getType()] = $config->getJsConfig();
        }

        $targets = array_filter($targets);

        return [
            'targets' => $targets
        ];
    }

    /**
     * Check config type
     *
     * @param string $type
     * @return array
     */
    public function getByType(string $type): ?CompletionRequestInterface
    {
        foreach ($this->pool as $config) {
            
            if ($config->getType() === $type) {
                return $config;
            }
        }
        return null;
    }
}

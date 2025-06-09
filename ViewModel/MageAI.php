<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;

class MageAI implements ArgumentInterface
{
    public const XML_PATH_IS_ENABLED = 'mageai/general/enabled';
    public const XML_PATH_API_BASE_URL = 'mageai/api/base_url';
    public const XML_PATH_API_KEY = 'mageai/api/api_secret';
    public const XML_PATH_API_MODEL = 'mageai/api/model';
    public const XML_PATH_PRODUCT_ATTRIBUTE = 'mageai/product_description/attribute';
    public const XML_PATH_DESCRIPTION_PROMPT = 'mageai/product_description/description_prompt';
    public const XML_PATH_DESCRIPTION_WORD_COUNT = 'mageai/product_description/description_words_count';
    public const XML_PATH_SHORT_SHORT_DESCRIPTION_PROMPT = 'mageai/product_description/short_description_prompt';
    public const XML_PATH_SHORT_DESCRIPTION_WORD_COUNT = 'mageai/product_description/short_description_words_count';
    public const XML_PATH_PROMPT_META_BTN_ENABLED = 'mageai/product_seo_content/enabled';
    public const XML_PATH_PROMPT_META_TITLE       = 'mageai/product_seo_content/meta_title';
    public const XML_PATH_PROMPT_META_DESCRIPTION = 'mageai/product_seo_content/meta_description';
    public const XML_PATH_PROMPT_META_KEYWORDS    = 'mageai/product_seo_content/meta_keywords';

    /**
     * @var ScopeConfigInterface
     */
    public ScopeConfigInterface $scopeConfig;

    /**
     * @var EavConfig
     */
    public $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get config value
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check is extension is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get API base url
     *
     * @return string
     */
    public function getApiBaseUrl()
    {
        return $this->getConfig(self::XML_PATH_API_BASE_URL);
    }

    /**
     * Get API secret
     *
     * @return string
     */
    public function getApiSecret()
    {
        return $this->getConfig(self::XML_PATH_API_KEY);
    }

    /**
     * Get description prompt
     *
     * @return string
     */
    public function getDescriptionPrompt()
    {
        return $this->getConfig(self::XML_PATH_DESCRIPTION_PROMPT);
    }

    /**
     * Get number of description words
     *
     * @return int
     */
    public function getDescriptionWordCount()
    {
        return (int) $this->getConfig(self::XML_PATH_DESCRIPTION_WORD_COUNT);
    }

    /**
     * Get short description prompt
     *
     * @return string
     */
    public function getShortDescriptionPrompt()
    {
        return $this->getConfig(self::XML_PATH_SHORT_SHORT_DESCRIPTION_PROMPT);
    }

    /**
     * Get number of short description words
     *
     * @return int
     */
    public function getShortDescriptionWordCount()
    {
        return (int) $this->getConfig(self::XML_PATH_SHORT_DESCRIPTION_WORD_COUNT);
    }

    /**
     * Get max token
     *
     * @param string $type
     * @return float
     */
    public function getMaxToken($type)
    {
        if ($type == 'short') {
            $wordCount = $this->getShortDescriptionWordCount();
        } else {
            $wordCount = $this->getDescriptionWordCount();
        }
        return round($wordCount * 1.5);
    }

    /**
     * Get api model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->getConfig(self::XML_PATH_API_MODEL);
    }

    /**
     * Get product attribute code
     *
     * @return mixed
     */
    public function getProductAttribute()
    {
        return $this->getConfig(self::XML_PATH_PRODUCT_ATTRIBUTE);
    }

    /**
     * Get product attribute title using code
     *
     * @return mixed
     */
    public function getProductAttributeTitle()
    {
        $attributeCode = $this->getProductAttribute();
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);
        return $attribute->getStoreLabel();
    }

    /**
     * Get enabled (active) store IDs
     *
     * @return array
     */
    public function getEnabledStoreIds(): array
    {
        $enabledStoreIds = [0];

        foreach ($this->storeManager->getStores() as $store) {
            if ($store->isActive()) {
                $enabledStoreIds[] = (int) $store->getId();
            }
        }

        return $enabledStoreIds;
    }
}

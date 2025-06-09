<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Block\Adminhtml\Product;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\StoreRepositoryInterface;
use ManiyaTech\MageAI\ViewModel\MageAI;
use Magento\Framework\Serialize\Serializer\Json;

class Helper extends Template
{
    /**
     * @var MageAI
     */
    private MageAI $mageAI;

    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepository;

    /**
     * @var LocatorInterface
     */
    private LocatorInterface $locator;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * Helper constructor.
     *
     * @param Context $context
     * @param MageAI $mageAI
     * @param StoreRepositoryInterface $storeRepository
     * @param LocatorInterface $locator
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        MageAI $mageAI,
        StoreRepositoryInterface $storeRepository,
        LocatorInterface $locator,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mageAI = $mageAI;
        $this->storeRepository = $storeRepository;
        $this->locator = $locator;
        $this->json = $json;
    }

    /**
     * Returns component configuration as a serialized JSON string
     *
     * @return string
     */
    public function getComponentJsonConfig(): string
    {
        $config = [
            'serviceUrl' => $this->getUrl('ManiyaTech_MageAI/helper/validate'),
            'sku' => $this->locator->getProduct()->getSku(),
            'storeId' => $this->locator->getStore()->getId(),
            'stores' => $this->getStores()
        ];

        return $this->json->serialize($config);
    }

    /**
     * Returns store data for the current product, marking current store as selected
     *
     * @return array
     */
    public function getStores(): array
    {
        $selectedStoreId = (int) $this->locator->getStore()->getId();
        $storeIds = $this->mageAI->getEnabledStoreIds();

        $results = [];
        $first = null;

        foreach ($storeIds as $storeId) {
            $store = $this->storeRepository->getById($storeId);

            if ($selectedStoreId === $storeId) {
                $first = $store;
                continue;
            }

            $results[] = [
                'label' => $storeId === 0 ? __('Default Store View') : $store->getName(),
                'store_id' => $storeId,
                'selected' => false
            ];
        }

        if ($first) {
            array_unshift($results, [
                'label' => __('Current Store View'),
                'store_id' => $first->getId(),
                'selected' => true
            ]);
        }

        return $results;
    }

    /**
     * Renders HTML only if MageAI is enabled and the product SEO button is enabled
     *
     * @return string
     */
    public function toHtml(): string
    {
        $enabled = $this->mageAI->isEnabled();
        $productSeoBtn = $this->mageAI->getConfig(MageAI::XML_PATH_PROMPT_META_BTN_ENABLED);

        if (!$enabled || !$productSeoBtn) {
            return '';
        }

        return parent::toHtml();
    }
}

<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Controller\Adminhtml\Index;

use InvalidArgumentException;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use ManiyaTech\MageAI\ViewModel\MageAI as MageAIModel;
use ManiyaTech\MageAI\Model\Query\Completions;
use Magento\Catalog\Model\ProductFactory;

class Generate extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ManiyaTech_MageAI::generate';

    /**
     * @var JsonFactory
     */
    protected $resultJson;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var completions
     */
    protected $queryCompletion;

    /**
     * @var MageAIModel
     */
    protected $mageaiModel;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Generate constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJson
     * @param ProductRepositoryInterface $productRepository
     * @param Completions $queryCompletion
     * @param MageAIModel $mageaiModel
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJson,
        ProductRepositoryInterface $productRepository,
        Completions $queryCompletion,
        MageAIModel $mageaiModel,
        ProductFactory $productFactory
    ) {
        $this->resultJson = $resultJson;
        $this->productRepository = $productRepository;
        $this->queryCompletion = $queryCompletion;
        $this->mageaiModel = $mageaiModel;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Generate Content
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $response = ['error' => true, 'data' => 'unknown'];
        $isEnabled = $this->mageaiModel->isEnabled();
        if ($isEnabled) {
            try {
                $customPrompt = $this->getRequest()->getParam('custom_prompt');
                if ($customPrompt === 'false') {
                    $sku = $this->getRequest()->getParam('sku', false);
                    $title = $this->getRequest()->getParam('title', false);
                    if ($sku || $title) {
                        $type = $this->getRequest()->getParam('type');
                        $attributeCode = $this->mageaiModel->getProductAttribute();
                        if ($sku) {
                            $product = $this->productRepository->get($sku);
                        } else {
                            $product = $this->productFactory->create();
                            $product->setData($attributeCode, $title);
                        }
                        $data = $this->queryCompletion->generateProductDescription($product, $type);
                        $response = ['error' => false, 'data' => $data];
                    }
                } else {
                    $data = $this->queryCompletion->generateCustomContent($customPrompt);
                    $response = ['error' => false, 'data' => $data];
                }
            } catch (LocalizedException | InvalidArgumentException $e) {
                $response = ['error' => true, 'data' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'data' => $e->getMessage()];
            }
        }

        $resultJson = $this->resultJson->create();
        return $resultJson->setData($response);
    }
}

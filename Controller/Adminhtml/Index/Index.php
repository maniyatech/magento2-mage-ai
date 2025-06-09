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
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use ManiyaTech\MageAI\Model\CompletionConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class Index extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ManiyaTech_MageAI::generate';

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var CompletionConfig
     */
    private CompletionConfig $completionConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param CompletionConfig $completionConfig
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CompletionConfig $completionConfig,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->completionConfig = $completionConfig;
        $this->productRepository = $productRepository;
        $this->request = $request;
    }

    /**
     * Generate SEO field Data
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultPage = $this->jsonFactory->create();

         $type = $this->completionConfig->getByType(
             $this->getRequest()->getParam('type')
         );

        if ($type === null) {
            throw new LocalizedException(__('Invalid request parameters'));
        }

        try {
            $prompt = $this->getRequest()->getParam('prompt');
            $result = $type->query($prompt);

        } catch (LocalizedException | InvalidArgumentException $e) {
            $resultPage->setData([
                'error' => $e->getMessage()
            ]);
            return $resultPage;
        }

        $resultPage->setData([
            'result' => $result,'type' => $this->getRequest()->getParam('type')
        ]);

        return $resultPage;
    }
      /**
       * @inheritDoc
       */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}

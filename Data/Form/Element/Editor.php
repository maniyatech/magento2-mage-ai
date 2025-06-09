<?php
/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

namespace ManiyaTech\MageAI\Data\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use ManiyaTech\MageAI\ViewModel\MageAI as MageAIModel;

class Editor extends \Magento\Framework\Data\Form\Element\Editor
{
    public const ALLOWED_FIELDS_HTML_ID = [
        'product_form_description',
        'product_form_short_description'
    ];

    /**
     * @var MageAIModel
     */
    protected $mageAIModel;

    /**
     * Editor constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param MageAIModel $mageAIModel
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param Random|null $random
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        MageAIModel $mageAIModel,
        $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?Random $random = null,
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->mageAIModel = $mageAIModel;
        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $data,
            $serializer,
            $random,
            $secureRenderer
        );
    }

    /**
     * Return HTML button to toggling WYSIWYG
     *
     * @param bool $visible
     * @return string
     */
    protected function _getToggleButtonHtml($visible = true)
    {
        $html = parent::_getToggleButtonHtml($visible);
        $isEnabled = $this->mageAIModel->isEnabled();
        if ($isEnabled && in_array($this->getHtmlId(), self::ALLOWED_FIELDS_HTML_ID)) {
            $html .= $this->_getButtonHtml(
                [
                    'title' => $this->translate('Generate with MageAI'),
                    'class' => 'generate-mageai-btn',
                    'style' => $visible ? '' : 'display:none',
                    'id' => $this->getHtmlId() . '_mageai',
                ]
            );
            $html .= $this->_getButtonHtml(
                [
                    'title' => $this->translate('Advanced Generate with MageAI'),
                    'class' => 'advanced-generate-mageai-btn',
                    'style' => $visible ? '' : 'display:none',
                    'id' => $this->getHtmlId() . '_mageai',
                ]
            );
        }
        return $html;
    }
}

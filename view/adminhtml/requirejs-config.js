var config = {
    map: {
        '*': {
            'mageAiGenerate': 'ManiyaTech_MageAI/js/generate',
            'Magento_PageBuilder/template/form/element/html-code.html':
                'ManiyaTech_MageAI/template/html-code.html'
        }
    },
    config: {
        mixins: {
            'Magento_PageBuilder/js/form/element/html-code': {
                'ManiyaTech_MageAI/js/html-code-mixin': true
            }
        }
    }
};

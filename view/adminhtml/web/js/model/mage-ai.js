/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal'
], function ($, alert, modal) {
    'use strict';

    var mageAI = {
        options: {
            generateBtnSelector: '.generate-mageai-btn',
            advancedGenerateBtnSelector: '.advanced-generate-mageai-btn',
            advancedGenerateModalSelector: '#advanced-generate-modal',
            promptGenerateTextAreaSelector: '#mageai-custom-prompt',
            shortDescriptionFieldIdentifier: 'product_form_short_description_mageai'
        },

        /**
         * Opens a modal popup for advanced generation functionality.
         *
         * @param {string} targetField
         */
        clickAdvancedGenerateButton: function (targetField) {
            var self = this;

            if (!$(this.options.advancedGenerateModalSelector).data('mageModal')) {
                modal({
                    type: 'popup',
                    responsive: true,
                    title: $.mage.__('Custom Content Prompt'),
                    modalClass: 'mageai-generate-modal',
                    buttons: [{
                        text: $.mage.__('Generate with MageAI'),
                        class: 'action-default primary',
                        click: function () {
                            self.promptGenerateButtonClick(targetField);
                        }
                    }]
                }, $(this.options.advancedGenerateModalSelector));
            }

            $(this.options.advancedGenerateModalSelector).modal('openModal');
        },

        /**
         * Handles prompt generation button click.
         *
         * @param {string} targetField
         */
        promptGenerateButtonClick: function (targetField) {
            var customPrompt = $(this.options.promptGenerateTextAreaSelector).val().trim();

            if (this.validateCustomPrompt(customPrompt)) {
                this.generateContent(false, false, customPrompt)
                    .done((content) => {
                        if (content) {
                            this.updateDescription(content, targetField);
                        }
                    })
                    .fail((error) => {
                        console.error('Error generating content:', error);
                    });
            }
        },

        /**
         * Updates the description field with new content.
         *
         * @param {string} content
         * @param {HTMLElement|string} targetField
         */
        updateDescription: function (content, targetField) {
            var isPageBuilder = $(targetField).parent().attr('id') === 'buttonspagebuilder_html_form_html';

            if (isPageBuilder) {
                var descriptionField = $(targetField).parents().next('textarea');
                descriptionField.val(content).change();
            } else {
                var iframeBody = $(targetField).parent().parent().find('iframe').contents().find('body');
                var textareaField = $(targetField).parent().parent().find('textarea');
                iframeBody.html(content).change();
                textareaField.val(content).change();
            }
        },

        /**
         * Validates a custom prompt input.
         *
         * @param {string} prompt
         * @returns {boolean}
         */
        validateCustomPrompt: function (prompt) {
            if (!prompt) {
                alert({
                    title: $.mage.__('Validation Error'),
                    content: $.mage.__('Please enter a custom prompt.')
                });
                return false;
            }
            return true;
        },

        /**
         * Sends AJAX request to generate content.
         *
         * @param {string|false} sku
         * @param {string|false} type
         * @param {string|false} prompt
         * @returns {jQuery.Deferred}
         */
        generateContent: function (sku, type, prompt) {
            var deferred = $.Deferred();
            const productTitle = window.mageAIProductTitle || 'Field';
            const productAttributeCode = window.mageAIProductCode || '';

            const attributeElement = $(
                "input[name='product[" + productAttributeCode + "]'], " +
                "textarea[name='product[" + productAttributeCode + "]'], " +
                "select[name='product[" + productAttributeCode + "]']"
            );
            const attributeInputValue = attributeElement.val();

            if (!prompt && (!attributeInputValue || attributeInputValue.trim() === '')) {
                alert({
                    title: $.mage.__('Validation Error'),
                    content: $.mage.__(`${productTitle} is required.`)
                });
                return deferred.resolve(false);
            }

            $.ajax({
                url: window.mageAIAjaxUrl,
                type: 'POST',
                showLoader: true,
                data: {
                    form_key: FORM_KEY,
                    sku: sku,
                    title: attributeInputValue,
                    type: type,
                    custom_prompt: prompt
                },
                success: function (response) {
                    if (response.error === false) {
                        deferred.resolve(response.data);
                    } else {
                        alert({
                            title: $.mage.__('API Error'),
                            content: response.data
                        });
                        deferred.resolve(false);
                    }

                    if (prompt) {
                        $(mageAI.options.advancedGenerateModalSelector).modal('closeModal');
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    console.error('AJAX error:', errorThrown);
                    deferred.reject(errorThrown);
                }
            });

            return deferred.promise();
        }
    };

    return mageAI;
});
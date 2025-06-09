/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/button',
    'uiRegistry',
    'mage/url',
    'Magento_Ui/js/modal/alert'
], function ($, Button, registry, urlBuilder, alert) {
    'use strict';

    return Button.extend({
        defaults: {
            title: $.mage.__('Generate with MageAI'),
            error: '',
            displayArea: '',
            template: 'ManiyaTech_MageAI/button',
            elementTmpl: 'ManiyaTech_MageAI/button',
            modalName: null,
            actions: [{
                targetName: '${ $.name }',
                actionName: 'action'
            }]
        },

        /**
         * Action triggered by button click
         */
        action: function () {
            let prompt = $("input[name='product[name]']").val();

            // If product name is not present (on edit page), fallback to page title
            if (!prompt) {
                prompt = $("h1.page-title").text();
            }

            const payload = {
                form_key: window.FORM_KEY,
                prompt: prompt,
                type: this.settings.type
            };

            $.ajax({
                url: $('#openai_url').val(),
                type: 'POST',
                data: payload,
                showLoader: true
            }).done(function (response) {
                if (response.error) {
                    alert({
                        title: $.mage.__('Error'),
                        content: response.error
                    });
                    return;
                }
                response.result = response.result.replace(/^['"]|['"]$/g, '');
                switch (response.type) {
                    case 'meta_keywords':
                        $("textarea[name='product[meta_keyword]']")
                            .val(response.result)
                            .trigger('change');
                        break;
                    case 'meta_description':
                        $("textarea[name='product[meta_description]']")
                            .val(response.result)
                            .trigger('change');
                        break;
                    case 'meta_title':
                        $("input[name='product[meta_title]']")
                            .val(response.result)
                            .trigger('change');
                        break;
                }
            }).fail(function (xhr) {
                alert({
                    title: $.mage.__('Request Failed'),
                    content: xhr.responseText || $.mage.__('An unknown error occurred.')
                });
            });
        }
    });
});
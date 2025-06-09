/**
 * ManiyaTech
 *
 * @author        Milan Maniya
 * @package       ManiyaTech_MageAI
 */

define([
    'jquery',
    'uiComponent',
    'mageUtils',
    'uiRegistry',
    'uiLayout',
    'Magento_Ui/js/lib/spinner',
    'underscore'
], function ($, Component, utils, registry, layout, loader, _) {
    'use strict';

    return Component.extend({
        defaults: {
            targets: {},
            settings: {}
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();

            _.each(this.targets, (group, key) => {
                this.waitForContainer(group.container)
                    .then((containerComponent) => {
                        this.createComponents(key, group, containerComponent);
                    });
            });

            return this;
        },

        /**
         * Wait until container component is ready
         *
         * @param {String} componentName
         * @returns {Promise}
         */
        waitForContainer: function (componentName) {
            return new Promise((resolve) => {
                registry.get(componentName, (component) => {
                    if (component && component.elems && typeof component.elems.subscribe === 'function') {
                        component.elems.subscribe(() => resolve(component));
                    } else {
                        resolve(component); // fallback if no elems
                    }
                });
            });
        },

        /**
         * Dynamically load modal content component
         *
         * @param {String} parentName
         * @param {Object} groupConfig
         */
        initializeModalContent: function (parentName, groupConfig) {
            const contentName = parentName + '.content';

            if (!registry.has(contentName)) {
                layout([{
                    component: groupConfig.modal || 'ManiyaTech_MageAI/js/modal/default',
                    parent: parentName,
                    name: 'content'
                }]);
            }

            registry.get(contentName, (content) => {
                if (content && typeof content.init === 'function') {
                    content.init();
                }
            });
        },

        /**
         * Create modal and button components
         *
         * @param {String} type
         * @param {Object} groupConfig
         * @param {Object} parent
         */
        createComponents: function (type, groupConfig, parent) {
            const settings = Object.assign({}, this.settings, groupConfig, { type: type });
            const loaderInstance = loader.get('product_form.product_form');

            const modalName = this.name + '.' + type + '-modal';

            const modalTemplate = {
                parent: this.name,
                name: type + '-modal',
                component: 'Magento_Ui/js/modal/modal-component',
                config: {
                    isTemplate: true,
                    settings: settings,
                    loader: loaderInstance,
                    options: this.getModalOptions(type, groupConfig)
                }
            };

            const buttonConfig = {
                parent: parent.name,
                name: 'mageai-button-' + type,
                component: groupConfig.component,
                config: {
                    settings: settings,
                    modalName: modalName,
                    loader: loaderInstance
                }
            };

            layout([buttonConfig, modalTemplate]);
        },

        /**
         * Get modal options
         *
         * @param {String} type
         * @param {Object} groupConfig
         * @returns {Object}
         */
        getModalOptions: function (type, groupConfig) {
            const parent = this.name + '.' + type + '-modal';

            return {
                id: 'modal-' + type,
                title: $.mage.__('Generate with MageAI'),
                type: 'slide',
                opened: this.initializeModalContent.bind(this, parent, groupConfig),
                buttons: [
                    {
                        class: 'action primary',
                        text: $.mage.__('Generate'),
                        actions: [{
                            targetName: parent + '.content',
                            actionName: 'generate'
                        }]
                    },
                    {
                        class: 'action primary',
                        text: $.mage.__('Accept'),
                        actions: [{
                            targetName: parent + '.content',
                            actionName: 'saveAndClose'
                        }]
                    }
                ]
            };
        }
    });
});
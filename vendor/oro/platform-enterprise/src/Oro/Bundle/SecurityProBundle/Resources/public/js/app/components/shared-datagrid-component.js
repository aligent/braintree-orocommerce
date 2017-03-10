define(function(require) {
    'use strict';

    var SharedDatagridComponent;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var $ = require('jquery');
    var routing = require('routing');
    var mediator = require('oroui/js/mediator');
    var widgetManager = require('oroui/js/widget-manager');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var helper = require('orosecuritypro/js/app/helper/component-helper');

    /**
     * @exports SharedDatagridComponent
     */
    SharedDatagridComponent = BaseComponent.extend({
        options: {
            messages: {}
        },

        listen: {
            'datagrid:mass:frontend:execute:shared-datagrid mediator': 'onFrontMassAction',
            'datagrid:frontend:execute:shared-datagrid mediator': 'onFrontAction',
            'datagrid:shared-datagrid:add:data mediator': 'onSharedWithDatagridAdd',
            'datagrid:shared-datagrid:add:data-from-select2 mediator': 'onSelect2Add',
            'widget:shared-dialog:apply mediator': 'onShareDialogApply'
        },

        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            _.defaults(this.options.messages, {
                sharedSuccess: __('oro.securitypro.action.shared_success'),
                sharedError: __('oro.securitypro.action.shared_error'),
                forbiddenError: __('oro.securitypro.action.forbidden_error')
            });
        },

        onFrontMassAction: function(action) {
            var collection = action.datagrid.collection;
            collection.remove(helper.extractModelsFromGridCollection(action.datagrid), {silent: true});
            collection.trigger('reset', collection);
        },

        onFrontAction: function(action) {
            var collection = action.datagrid.collection;
            collection.remove(action.model, {silent: true});
            collection.trigger('reset', collection);
        },

        onSharedWithDatagridAdd: function(data) {
            widgetManager.getWidgetInstanceByAlias('shared-dialog', function(widget) {
                var grid = widget.pageComponent('shared-datagrid').grid;
                var changed = false;
                _.each(data.models, function(model) {
                    var id = JSON.stringify({
                        entityId: model.id,
                        entityClass: data.entityClass
                    });
                    if (!grid.collection.findWhere({id: id})) {
                        var newModel = {
                            id: id,
                            entity: model.get('entity'),
                            action_configuration: {
                                delete: false,
                                update: false
                            }
                        };
                        grid.collection.add(newModel);
                        changed = true;
                    }
                });
                if (changed) {
                    grid.collection.trigger('reset', grid.collection);
                    grid.trigger('layout:update');
                    grid.trigger('content:update');
                }
            });
        },

        onSelect2Add: function(data) {
            widgetManager.getWidgetInstanceByAlias('shared-dialog', function(widget) {
                var grid = widget.pageComponent('shared-datagrid').grid;
                if (!grid.collection.findWhere({id: data.id})) {
                    var model = {
                        id: data.id,
                        entity: data.entity,
                        action_configuration: {
                            delete: false,
                            update: false
                        }
                    };
                    grid.collection.add(model);
                    grid.collection.trigger('reset', grid.collection);
                    grid.trigger('layout:update');
                    grid.trigger('content:update');
                }
            });
        },

        onShareDialogApply: function() {
            var self = this;
            widgetManager.getWidgetInstanceByAlias('shared-dialog', function(widget) {
                var grid = widget.pageComponent('shared-datagrid').grid;

                var entitiesParam = [];
                _.each(grid.collection.models, function(model) {
                    entitiesParam.push(model.id);
                });

                $.ajax({
                    url: routing.generate(
                        'oropro_share_update',
                            {
                                'entityId': self.options.entityId,
                                '_widgetContainer': 'dialog',
                                'entityClass': self.options.entityClass
                            }
                        ),
                    type: 'POST',
                    data: {
                        oropro_share_form: {
                            entityClass: self.options.entityClass,
                            entityId: self.options.entityId,
                            entities: entitiesParam.join(';')
                        }
                    },
                    success: function() {
                        widgetManager.getWidgetInstanceByAlias('shared-dialog', function(widget) {
                            mediator.execute('showFlashMessage', 'success', self._getMessage('sharedSuccess'));
                            widget.remove();
                            mediator.execute('refreshPage');
                        });
                    },
                    error: function(e) {
                        widgetManager.getWidgetInstanceByAlias('shared-dialog', function(widget) {
                            if (e.status === 403) {
                                mediator.execute('showErrorMessage', self._getMessage('forbiddenError'), e);
                            } else {
                                mediator.execute('showErrorMessage', self._getMessage('sharedError'), e);
                            }
                            widget.remove();
                            mediator.execute('refreshPage');
                        });
                    }
                });
            });
        },

        _getMessage: function(labelKey) {
            return this.options.messages[labelKey];
        }
    });

    return SharedDatagridComponent;
});

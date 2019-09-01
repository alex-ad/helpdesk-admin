Ext.onReady(function() {
    function getACR(acr) {
        if ( acr === 'acrRead' ) {
            var acr = 0;
        } else if ( acr === 'acrWrite' ) {
            var acr = 1;
        } else {
            var acr = 0;
        }
        var c = document.cookie.replace(/(?:(?:^|.*;\s*)acr\s*\=\s*([^;]*).*$)|^.*$/, "$1");
        return ( c & acr === 1 ) ? true : false;
    }
// main table via tree-list : rosources
	var tblTree = [
		{
			xtype: 'treecolumn',
			text: 'ИТ-ресурсы > Услуги > Роли [> Каталоги > Права]',
			width: 600,
			flex: 1,
			sortable: true,
			dataIndex: 'name',
        }, {
			text: 'Владелец ресурса',
			flex: 1,
			dataIndex: 'ownname'
        }, {
			text: 'Ответственный за ресурс',
			flex: 1,
			dataIndex: 'visename'
        }
	];

// MODEL
// model : resources >> tree list
	Ext.define('modelTree', {
		extend: 'Ext.data.TreeModel',
		fields: [
			{name: 'name', type: 'string', persist: true},
			{name: 'ownname', type: 'string'},
			{name: 'visename', type: 'string'}
		],
		idProperty: 'id',
		autoLoad: true
	});

// model : resource >> combobox
    Ext.define('modelResList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'name', type: 'string' }
        ]
    });

// model : service >> combobox
    Ext.define('modelSrvList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'name', type: 'string' }
        ]
    });

// model : role >> combobox
    Ext.define('modelRolList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'name', type: 'string' }
        ]
    });

// model : ticket form
    Ext.define('modelTicketForm', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'id', type: 'integer' },
            { name: 'form', type: 'string' }
        ],
        idProperty: 'id',
    });

// model : role type list >> combobox
    Ext.define('modelRoleTypeList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'id', type: 'integer' },
            { name: 'name', type: 'string' }
        ]
    });

// model : user >> combobox
    Ext.define('modelUserList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'id', type: 'integer' },
            { name: 'name', type: 'string' },
            { name: 'company', type: 'string' },
            { name: 'division', type: 'string' },
            { name: 'func', type: 'string' }
        ],
        idProperty: 'id',
    });

// model : cat rw >> combobox
    Ext.define('modelCatRole', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'name', type: 'string' },
            { name: 'id', type: 'integer' }
        ]
    });

// model : cat rw >> combobox
    Ext.define('modelCatRW', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'id', type: 'integer' },
            { name: 'name', type: 'string' }
        ],
        idProperty: 'id',
    });

// STORE
// store : resource >> tree list
	var storeTree = Ext.create('Ext.data.TreeStore', {
		type: 'tree',
		folderSort: true,
		model: 'modelTree',
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.c_restree.php'
		}
	});

// store : resource >> combobox
    var storeResList = Ext.create('Ext.data.Store', {
        model: 'modelResList',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getResourceListAsName',
            reader: {
                type: 'json',
            }
        },
        autoLoad: true,
    });

// store : service >> combobox
    var storeSrvList = Ext.create('Ext.data.Store', {
        model: 'modelSrvList',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getServiceList',
            reader: {
                type: 'json',
            }
        },
        autoLoad: true,
    });

// store : role >> combobox
    var storeRolList = Ext.create('Ext.data.Store', {
        model: 'modelRolList',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getRoleList',
            reader: {
                type: 'json',
            }
        },
        autoLoad: true,
    });

// store : ticket form >> combobox
    var storeTicketForm = Ext.create('Ext.data.Store', {
        model: 'modelTicketForm',
        data: [
        	[0, 'Волгоград'],
        	[1, 'Курган']
		]
    });

// store : role type list >> combobox (0 - only one select; 1 - multiselect via checkbox)
    var storeRoleTypeList = Ext.create('Ext.data.Store', {
        model: 'modelRoleTypeList',
        data: [
            [0, 'Один вариант для выбора'],
            [1, 'Несколько вариантов для выбора']
        ]
    });

// store : user >> combobox
    var storeUserList = Ext.create('Ext.data.Store', {
        model: 'modelUserList',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getUserListAsWP',
            reader: {
                type: 'json',
                idProperty: 'id'
            },
        },
        autoLoad: true
    });

// store : cat rw >> combobox
    var storeCatRoleList = Ext.create('Ext.data.Store', {
        model: 'modelCatRole',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getCatRoleList',
            reader: {
                type: 'json',
                idProperty: 'id'
            }
        },
        autoLoad: true,
    });

// store : cat rw >> combobox
    var storeCatRW = Ext.create('Ext.data.Store', {
        model: 'modelCatRW',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getCatRWRoleList',
            reader: {
                type: 'json',
                idProperty: 'id'
            }
        },
        autoLoad: true,
    });

// 1st toolbar : export excel
	var toolbar1 = Ext.create('Ext.toolbar.Toolbar', {
		dock: 'top',
		items: [{
			text: 'Експорт в Excel',
			iconCls: 'fa fa-file-excel-o green',
			handler: function() {
				var elements = tree.getStore().getRange();
				var jsonData = [];
				var xlsColumns = ['ИТ-ресурс', 'ИТ-услуга', 'Роль', 'Владелец ресурса', 'Ответственный за ресурс'];
				for ( i=0; i<elements.length; i++ ) {
					for ( j=0; j<elements[i].data.children.length; j++ ) {
						for ( k=0; k<elements[i].data.children[j].children.length; k++ ) {
							var tmp = [];
							tmp.push(elements[i].data.name);
							tmp.push(elements[i].data.children[j].name);
							tmp.push(elements[i].data.children[j].children[k].name);
							tmp.push(elements[i].data.children[j].ownname);
							tmp.push(elements[i].data.children[j].visename);
							jsonData.push(tmp);
						}
					}
				}
				Ext.Ajax.request({
					url: '/saruman/modules/expexcel.php',
					params: {
						jsonData: Ext.encode(jsonData),
						reportName: 'Список ИТ-ресурсов',
						headersArray: Ext.encode(xlsColumns)
					},
					success: function (response) {
						var rObj = Ext.decode(response.responseText);
						window.location.href = '/saruman/modules/expexcel.php?fileKey=' + rObj.fileKey;
					}
				});
			}
		}]
	});

// 2nd toolbar : resource
    var toolbar2 = Ext.create('Ext.toolbar.Toolbar', {
        dock: 'top',
        listeners: {
            afterrender: function(obj, eOpts) {
                if ( !getACR('acrWrite') ) obj.hide();
            }
        },
        items: [{
            xtype: 'combobox',
            fieldLabel: 'ИТ-ресурс',
            itemId: 'textResName',
            width: 400,
            typeAhead: true,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            store: storeResList,
            validator: function(val) {
                if ( ( val.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) || ( val.length == 0 ) )
                    return true;
                else
                    return 'Разрешенные символы: а-я А-Я ёЁ a-z A-Z 0-9 ()"«»:;,_-+./\\ Пробел';
            }
        },{
            xtype: 'combobox',
            fieldLabel: 'Форма заявки',
            itemId: 'comboResForm',
            width: 250,
            typeAhead: true,
            editable: false,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'form',
            valueField: 'id',
            store: storeTicketForm,
            multiSelect: true,
            emptyText: 'Выберите вариант'
        },{
            itemId: 'btnResRename',
            iconCls: 'fa fa-pencil blue',
            hidden: true,
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.renameClick('Res');
            }
        },{
            itemId: 'btnResAdd',
            iconCls: 'fa fa-plus-circle green',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.addClick('Res');
            }
        },{
            itemId: 'btnResDel',
            hidden: true,
            iconCls: 'fa fa-minus-circle red',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.delClick('Res');
            }
        }]
    });

// 3d toolbar : service
    var toolbar3 = Ext.create('Ext.toolbar.Toolbar', {
        dock: 'top',
        listeners: {
            afterrender: function(obj, eOpts) {
                if ( !getACR('acrWrite') ) obj.hide();
            }
        },
        hidden: true,
        items: [{
            xtype: 'combobox',
            fieldLabel: 'ИТ-Услуга',
            itemId: 'textSrvName',
            width: 400,
            typeAhead: true,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            store: storeSrvList,
            validator: function(val) {
                if ( ( val.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) || ( val.length == 0 ) )
                    return true;
                else
                    return 'Разрешенные символы: а-я А-Я ёЁ a-z A-Z 0-9 ()"«»:;,_-+./\\ Пробел';
            }
        },{
            xtype: 'combobox',
            fieldLabel: 'Форма заявки',
            itemId: 'comboSrvForm',
            width: 250,
            typeAhead: true,
            editable: false,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'form',
            valueField: 'id',
            store: storeTicketForm,
            multiSelect: false,
            emptyText: 'Выберите вариант',
        },{
            xtype: 'combobox',
            fieldLabel: 'Владелец',
            itemId: 'comboSrvOwner',
            width: 400,
            typeAhead: true,
            editable: false,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            valueField: 'id',
            store: storeUserList,
            multiSelect: false,
            emptyText: 'Выберите вариант',
            tpl: [
                '<ul class="x-list-plain">',
                '<tpl for=".">',
                '<li class="x-boundlist-item">',
                '<strong>{name}</strong><br>{company}: {division}: {func}',
                '</li>',
                '</tpl>',
                '</ul>'
            ],
            validator: function(val) {
                if ( val.match(/^([А-Яа-яёЁ]{2,})(\s)([А-Яа-яёЁ]{2,})(\s)([А-Яа-яёЁ]{2,})$/gmiu) )
                    return true;
                else
                    return 'Это поле должно содержать только кириллические символы в формате "Фамилия Имя Отчество"';
            }
        },{
            xtype: 'combobox',
            fieldLabel: 'Ответственный',
            itemId: 'comboSrvVizier',
            width: 400,
            typeAhead: true,
            editable: false,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            valueField: 'id',
            store: storeUserList,
            multiSelect: false,
            emptyText: 'Выберите вариант',
            tpl: [
                '<ul class="x-list-plain">',
                '<tpl for=".">',
                '<li class="x-boundlist-item">',
                '<strong>{name}</strong><br>{company}: {division}: {func}',
                '</li>',
                '</tpl>',
                '</ul>'
            ],
            validator: function(val) {
                if ( val.match(/^([А-Яа-яёЁ]{2,})(\s)([А-Яа-яёЁ]{2,})(\s)([А-Яа-яёЁ]{2,})$/gmiu) )
                    return true;
                else
                    return 'Это поле должно содержать только кириллические символы в формате "Фамилия Имя Отчество"';
            }
        },{
            xtype: 'combobox',
            fieldLabel: 'Тип списка',
            itemId: 'comboTypeList',
            width: 250,
            typeAhead: true,
            editable: false,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            valueField: 'id',
            store: storeRoleTypeList,
            multiSelect: false,
            emptyText: 'Выберите вариант',
        },{
            itemId: 'btnSrvRename',
            iconCls: 'fa fa-pencil blue',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.renameClick('Srv');
            }
        },{
            itemId: 'btnSrvAdd',
            iconCls: 'fa fa-plus-circle green',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.addClick('Srv');
            }
        },{
            itemId: 'btnSrvDel',
            iconCls: 'fa fa-minus-circle red',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.delClick('Srv');
            }
        }]
    });

// 4nd toolbar : role
    var toolbar4 = Ext.create('Ext.toolbar.Toolbar', {
        dock: 'top',
        listeners: {
            afterrender: function(obj, eOpts) {
                if ( !getACR('acrWrite') ) obj.hide();
            }
        },
        hidden: true,
        items: [{
            xtype: 'combobox',
            fieldLabel: 'Роль доступа',
            itemId: 'textRolName',
            width: 400,
            typeAhead: true,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            store: storeRolList,
            validator: function(val) {
                if ( ( val.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) || ( val.length == 0 ) )
                    return true;
                else
                    return 'Разрешенные символы: а-я А-Я ёЁ a-z A-Z 0-9 ()"«»:;,_-+./\\ Пробел';
            }
        },{
            itemId: 'btnRolRename',
            iconCls: 'fa fa-pencil blue',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.renameClick('Rol');
            }
        },{
            itemId: 'btnRolAdd',
            iconCls: 'fa fa-plus-circle green',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.addClick('Rol');
            }
        },{
            itemId: 'btnRolDel',
            iconCls: 'fa fa-minus-circle red',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.delClick('Rol');
            }
        }]
    });

// 5th toolbar : ouop cat
    var toolbar5 = Ext.create('Ext.toolbar.Toolbar', {
        dock: 'top',
        listeners: {
            afterrender: function(obj, eOpts) {
                if ( !getACR('acrWrite') ) obj.hide();
            }
        },
        hidden: true,
        items: [{
            xtype: 'combobox',
            fieldLabel: 'Каталог ОУОП',
            itemId: 'textCatName',
            width: 400,
            typeAhead: true,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            valueField: 'id',
            store: storeCatRoleList,
            multiSelect: false,
            emptyText: 'Выберите вариант',
            validator: function(val) {
                if ( ( val.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) || ( val.length == 0 ) )
                    return true;
                else
                    return 'Разрешенные символы: а-я А-Я ёЁ a-z A-Z 0-9 ()«»:;,_-+./\\ Пробел';
            }
        },{
            xtype: 'combobox',
            fieldLabel: 'Права доступа',
            itemId: 'comboCatRW',
            width: 400,
            typeAhead: true,
            editable: false,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            valueField: 'id',
            store: storeCatRW,
            multiSelect: false,
            emptyText: 'Выберите вариант',
        },{
            itemId: 'btnCatRename',
            iconCls: 'fa fa-pencil blue',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.renameClick('Cat');
            }
        },{
            itemId: 'btnCatAdd',
            iconCls: 'fa fa-plus-circle green',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.addClick('Cat');
            }
        },{
            itemId: 'btnCatDel',
            iconCls: 'fa fa-minus-circle red',
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.delClick('Cat');
            }
        }]
    });

// Main TREE-PANEL
	var tree = Ext.create('Ext.tree.Panel', {
		extend: 'Ext.tree.Panel',
		xtype: 'tree-grid',
		renderTo: 'grid',
        viewConfig: {
            plugins: {
                ptype: 'treeviewdragdrop',
                sortOnDrop: true,
                containerScroll: true,
                appendOnly: true
            },
            listeners: {
                beforedrop: function(node, data, overModel, dropPosition, dropHandlers, eOpts) {
                    if ( !getACR('acrWrite') ) dropHandlers.cancelDrop();
                    var prevContainerType = data.records[0].parentNode.data.ntype;
                    var nextContainerType = overModel.data.ntype;
                    if ( prevContainerType !== nextContainerType ) dropHandlers.cancelDrop();
                },
                drop: function(node, data, overModel, dropPosition, eOpts) {
                    var params = {},
                        target = data.records[0].data;
                    params.elemId = target.elemId;
                    params.elemType = target.ntype;
                    params.prevParentId = target.elemParentId;
                    params.nextParentId = data.records[0].parentNode.data.elemId;
                    params.nextParentType = data.records[0].parentNode.data.ntype;
                    Ext.Ajax.request({
                        url: '/saruman/modules/ajax.c_resmove.php',
                        params: {
                            data: Ext.encode(params),
                        },
                        success: function(data) {
                            data = JSON.parse(data.responseText);
                            if ( data.success ) {
                                Ext.toast(data.msg, 'Успешно');
                                storeTree.getNodeById(target.id).set('elemParentId', params.nextParentId);
                                storeTree.commitChanges();
                                tree.reconfigure(storeTree);
                            } else {
                                Ext.toast(data.msg, 'ОШИБКА');
                            }

                        }
                    });
                }
            }
        },
		collapsible: false,
		useArrows: true,
		rootVisible: false,
		store: storeTree,
		multiSelect: false,
		reserveScrollbar: true,
		height: 786,
		minHeight: 250,
		minWidth: 600,
		syncRowHeight: false,
		fullscreen: true,
		dynamic: true,
        dockedItems: [toolbar1, toolbar2, toolbar3, toolbar4, toolbar5],
		columns: tblTree,
        selModel: {
            allowDeselect: true,
            listeners: {
                selectionchange: function(selModel, selection) {
                    var panel = selModel.view.up('');
                    panel.onSelectionChange.apply(panel, arguments);
                }
            }
        },
        onSelectionChange: function(selModel, selection) {
            var listResName = this.down('#textResName'),
                listSrvName = this.down('#textSrvName'),
                listRolName = this.down('#textRolName'),
                listCatName = this.down('#textCatName'),
                listResForm = this.down('#comboResForm'),
                listSrvForm = this.down('#comboSrvForm'),
                listRolType = this.down('#comboTypeList'),
                listSrvOwner = this.down('#comboSrvOwner'),
                listSrvVizier = this.down('#comboSrvVizier'),
                listCatRW = this.down('#comboCatRW'),
                selectedNode;
                this.down('#btnResDel').show();
                this.down('#btnResRename').show();
                this.down('#btnSrvDel').show();
                this.down('#btnSrvRename').show();
                this.down('#btnRolDel').show();
                this.down('#btnRolRename').show();
                this.down('#btnCatDel').show();
                this.down('#btnCatRename').show();
            if (selection.length) {
                selectedNode = selection[0].data;
                if ( selectedNode.ntype == 'res' ) {
                    toolbar2.show();
                    toolbar3.show();
                    toolbar4.hide();
                    toolbar5.hide();
                    this.down('#btnSrvDel').hide();
                    this.down('#btnSrvRename').hide();
                    listResName.setValue(selectedNode.name);
                    listResForm.setValue(selectedNode.form.split(','));
                } else if ( selectedNode.ntype == 'srv' ) {
                    toolbar2.hide();
                    toolbar3.show();
                    toolbar4.show();
                    toolbar5.hide();
                    this.down('#btnRolDel').hide();
                    this.down('#btnRolRename').hide();
                    listSrvName.setValue(selectedNode.name);
                    listSrvForm.setValue(selectedNode.form);
                    listRolType.setValue(selectedNode.role_list_type);
                    listSrvOwner.setValue(selectedNode.ownname_id);
                    listSrvVizier.setValue(selectedNode.visename_id);
                } else if ( selectedNode.ntype == 'rol' ) {
                    toolbar2.hide();
                    toolbar3.hide();
                    toolbar4.show();
                    toolbar5.show();
                    this.down('#btnCatDel').hide();
                    this.down('#btnCatRename').hide();
                    listRolName.setValue(selectedNode.name);
                } else if ( selectedNode.ntype == 'rup' ) {
                    toolbar2.hide();
                    toolbar3.hide();
                    toolbar4.show();
                    toolbar5.show();
                    this.down('#btnCatDel').hide();
                    this.down('#btnCatRename').hide();
                    listRolName.setValue(selectedNode.name);
                } else if ( selectedNode.ntype == 'cat' ) {
                    toolbar2.hide();
                    toolbar3.hide();
                    toolbar4.hide();
                    toolbar5.show();
                    listCatName.setValue(selectedNode.name);
                    listCatRW.setValue(selectedNode.crw);
                } else if ( selectedNode.ntype == 'crw' ) {
                    toolbar2.show();
                    toolbar3.hide();
                    toolbar4.hide();
                    toolbar5.hide();
                    this.down('#btnResDel').hide();
                    this.down('#btnResRename').hide();
                }
            } else {
                toolbar2.show();
                toolbar3.hide();
                toolbar4.hide();
                toolbar5.hide();
                this.down('#btnResDel').hide();
                this.down('#btnResRename').hide();
            }
        },
        addClick: function(btn='') {
		    var textName = '#text' + btn + 'Name';
            var target = this.selModel.getSelection()[0] || this.getRootNode(),
                inputField = this.down(textName),
                listResForm = this.down('#comboResForm'),
                listSrvForm = this.down('#comboSrvForm'),
                listRolType = this.down('#comboTypeList'),
                listSrvOwner = this.down('#comboSrvOwner'),
                listSrvVizier = this.down('#comboSrvVizier'),
                listCatRW = this.down('#comboCatRW'),
                params = {};
            params.elemName = inputField && inputField.getRawValue();
            params.elemType = btn.toLowerCase();
            params.elemId = target.data.elemId;
            params.elemParentId = target.data.elemParentId ? target.data.elemParentId : '';
            if ( isNaN(listSrvVizier.getValue()) === true ) {
                params.nameVizier = 0;
            } else {
                params.nameVizier = listSrvVizier.getValue();
            }
            params.nameOwner = listSrvOwner.getValue();
            params.formRes = listResForm.getValue() ? listResForm.getValue().join() : 0;
            params.formSrv = listSrvForm.getValue();
            params.roleType = listRolType.getValue();
            params.catRW = listCatRW.getValue();
            if ( params.elemName ) {
                if ( params.elemName.length > 1 ) {
                    if ( !params.elemName.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) {
                        Ext.Msg.alert('Ошибка', 'В имени присутствуют запрещенные символы');
                        return;
                    }
                    if ( btn === 'Srv' ) {
                        if ( params.nameOwner === null || params.formSrv === null || params.roleType === null || (params.elemParentId === '' && !params.elemId) ) {
                            Ext.Msg.alert('Внимание', "Заполните все необходимые поля: 'Форма заявки', 'Владелец', ['Ответственный'], 'Тип списка'");
                            return true;
                        }
                    } else if ( btn === 'Res' ) {
                        if ( params.formRes === 0 ) {
                            Ext.Msg.alert('Внимание', "Заполните все необходимые поля: 'Форма заявки',");
                            return true;
                        }
                    } else if ( btn === 'Cat' ) {
                        if ( params.catRW === null ) {
                            Ext.Msg.alert('Внимание', "Заполните все необходимые поля: 'Права доступа'");
                            return true;
                        }
                    }
                    Ext.Ajax.request({
                        url: '/saruman/modules/ajax.c_resadd.php',
                        params: {
                            data: Ext.encode(params),
                        },
                        success: function(data) {
                            data = JSON.parse(data.responseText);
                            console.log(data);
                            if ( data.success ) {
                                if ( params.elemType === 'srv' ) {//new service
                                    var parentId = ( params.elemParentId === '' ) ? params.elemId.substr(4) : params.elemParentId.substr(4);
                                    var parentNode = ( target.data.parentId === 'root' ) ? target.data.id : target.data.parentId;
                                    storeTree.getNodeById(parentNode).set('leaf', false);
                                    storeTree.getNodeById(parentNode).appendChild({
                                        name:  params.elemName,
                                        form: params.formSrv,
                                        role_list_type: params.roleType,
                                        ownname_id: params.nameOwner,
                                        ownname: listSrvOwner.getRawValue(),
                                        visename_id: params.nameVizier,
                                        visename: listSrvVizier.getRawValue(),
                                        elemParentId: 'res_' + parentId,
                                        ntype: 'srv',
                                        elemId: 'srv_'+data.elemId,
                                        srv_id: data.elemId,
                                        children: null,
                                        loaded: true
                                    });
                                    storeSrvList.reload();
                                } else if ( params.elemType === 'rol' ) {//new role
                                    var parentId = ( params.elemParentId.substr(0,3) === 'srv' ) ? params.elemParentId.substr(4) : params.elemId.substr(4);
                                    var parentNode = ( target.data.ntype === 'srv' ) ? target.data.id : target.data.parentId;
                                    storeTree.getNodeById(parentNode).set('leaf', false);
                                    storeTree.getNodeById(parentNode).appendChild({
                                        name:  params.elemName,
                                        elemId: 'rol_'+data.elemId,
                                        elemParentId: 'srv_' + parentId,
                                        ntype: 'rol',
                                        leaf: true,
                                        childern: null,
                                        loaded: true
                                    });
                                    storeRolList.reload();
                                } else if ( params.elemType === 'res' ) {//new resource
                                    storeTree.getRootNode().appendChild({
                                        name:  params.elemName,
                                        ntype: 'res',
                                        elemId: 'res_'+data.elemId,
                                        res_id: data.elemId,
                                        form: params.formRes,
                                        children: null,
                                        loaded: true
                                    });
                                    storeResList.reload();
                                } else if ( params.elemType === 'cat' ) {//new ouop-cat
                                    if ( target.data.ntype === 'cat' ) {
                                        var pid = target.data.parentId;
                                        var parentId = storeTree.getNodeById(pid).get('elemParentId').substr(4);
                                        var parentNode = storeTree.getNodeById(pid).get('parentId');
                                    } else {
                                        var parentId = ( params.elemId.substr(0,3) === 'cat' ) ? params.elemParentId.substr(4) : params.elemId.substr(4);
                                        var parentNode = ( (target.data.ntype === 'rup') || (target.data.ntype === 'rol') ) ? target.data.id : target.data.parentId;
                                    }
                                    storeTree.getNodeById(parentNode).set('ntype', 'rup');
                                    storeTree.getNodeById(parentNode).appendChild({
                                        name:  params.elemName,
                                        ntype: 'cat',
                                        crw: params.catRW,
                                        elemId: 'cat_'+data.elemId,
                                        elemParentId: 'rup_' + parentId,
                                        expanded: true,
                                        cls: 'ouop-cat',
                                    }).appendChild({
                                        name: data.elemName,
                                        crw: params.catRW,
                                        elemId: 'crw_'+params.catRW,
                                        elemParentId: 'cat_'+data.elemId,
                                        leaf: true,
                                        cls: 'ouop-cat-role',
                                        ntype: 'crw',
                                        children: null,
                                        loaded: true
                                    });
                                    storeCatRoleList.reload();
                                }
                                Ext.toast(data.msg, 'Успешно');
                                storeTree.commitChanges();
                                tree.reconfigure(storeTree);
                            } else {
                                Ext.Msg.alert('Ошибка', data.msg);
                            }
                        }
                    });
                } else {
                    Ext.toast('Длина имени должна быть более двух символов', 'Ошибка');
                }
            }
        },
        renameClick: function(btn='') {
            var textName = '#text' + btn + 'Name';
            var target = this.selModel.getSelection()[0] || this.getRootNode(),
                inputField = this.down(textName),
                listResForm = this.down('#comboResForm'),
                listSrvForm = this.down('#comboSrvForm'),
                listRolType = this.down('#comboTypeList'),
                listSrvOwner = this.down('#comboSrvOwner'),
                listSrvVizier = this.down('#comboSrvVizier'),
                listCatRW = this.down('#comboCatRW'),
                params = {};
            params.elemName = inputField && inputField.getRawValue();
            params.elemType = target.data.ntype ? target.data.ntype : '';
            params.elemId = target.data.elemId;
            params.elemParentId = target.data.elemParentId ? target.data.elemParentId : '';
            if ( (isNaN(listSrvVizier.getValue()) === true) || (listSrvVizier.getValue() === null) ) {
                params.nameVizier = 0;
            } else {
                params.nameVizier = listSrvVizier.getValue();
            }
            params.nameOwner = listSrvOwner.getValue();
            params.formRes = listResForm.getValue() ? listResForm.getValue().join() : 0;
            params.formSrv = listSrvForm.getValue();
            params.roleType = listRolType.getValue();
            params.catRW = listCatRW.getValue();
            if ( params.elemName ) {
                if ( ( params.elemName.length > 1 ) && ( params.elemType.length > 0 ) ) {
                    if ( !params.elemName.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) {
                        Ext.Msg.alert('Ошибка', 'В имени присутствуют запрещенные символы');
                        return;
                    }
                    Ext.Ajax.request({
                        url: '/saruman/modules/ajax.c_resrnm.php',
                        params: {
                            data: Ext.encode(params),
                        },
                        success: function(data) {
                            data = JSON.parse(data.responseText);
                            if ( data.success ) {
                                Ext.toast(data.msg, 'Успешно');
                                if ( params.elemType === 'res' ) {
                                    storeTree.getNodeById(target.id).set('name', params.elemName);
                                    storeResList.reload();
                                } else if ( (params.elemType === 'rol') || (params.elemType === 'rup') ) {
                                    storeTree.getNodeById(target.id).set('name', params.elemName);
                                    storeRolList.reload();
                                } else if ( params.elemType === 'srv' ) {
                                    storeTree.getNodeById(target.id).set({
                                        name: params.elemName,
                                        ownname_id: params.nameOwner,
                                        ownname: listSrvOwner.getRawValue(),
                                        visename_id: listSrvVizier.getValue(),
                                        visename: listSrvVizier.getRawValue(),
                                        form: params.formSrv,
                                        role_list_type: params.roleType
                                    });
                                    storeSrvList.reload();
                                } else if ( params.elemType === 'cat' ) {
                                    storeTree.getNodeById(target.id).set({
                                        name: params.elemName,
                                        crw: params.catRW,
                                        elemId: params.elemId
                                    });
                                    storeTree.getNodeById(target.id).child().set({
                                        name: listCatRW.getRawValue(),
                                        crw: params.catRW,
                                        elemid: 'crw_' + params.catRW
                                    });
                                    storeCatRoleList.reload();
                                }
                                storeTree.commitChanges();
                                tree.reconfigure(storeTree);
                            } else {
                                Ext.Msg.alert('Ошибка', data.msg);
                            }
                        }
                    });
                } else {
                    Ext.toast('Длина имени должна быть более двух символов', 'Ошибка');
                }
            }
        },
        delClick: function(btn='') {
            var textName = '#text' + btn + 'Name';
            var target = this.selModel.getSelection()[0] || this.getRootNode(),
                inputField = this.down(textName),
                params = {};
            params.elemType = target.data.ntype ? target.data.ntype : '';
            params.elemName = inputField && inputField.getRawValue();
            params.elemId = target.data.elemId;
            params.elemParentId = target.data.elemParentId ? target.data.elemParentId : '';
            if ( params.elemName ) {
                if ( (params.elemType.length > 0) && (params.elemType !== 'crw') ) {
                    Ext.MessageBox.show({
                        title: 'Удаление',
                        msg: 'Вы действительно хотите безвозвратно удалить со всеми вложениями выбранный элемент?',
                        buttons: Ext.MessageBox.OKCANCEL,
                        icon: Ext.MessageBox.WARNING,
                        fn: function(btn) {
                            if ( btn == 'ok' ) {
                                Ext.Ajax.request({
                                    url: '/saruman/modules/ajax.c_resdel.php',
                                    params: {
                                        data: Ext.encode(params),
                                    },
                                    success: function(data) {
                                        data = JSON.parse(data.responseText);
                                        if ( data.success ) {
                                            target.remove();
                                            storeResList.reload();
                                            storeSrvList.reload();
                                            storeRolList.reload();
                                            storeCatRoleList.reload();
                                            Ext.toast(data.msg, 'Успешно');
                                            storeTree.commitChanges();
                                            tree.reconfigure(storeTree);
                                        } else {
                                            Ext.Msg.alert('Ошибка', data.msg);
                                        }
                                    }
                                });
                            }
                        }
                    });
                } else {
                    Ext.toast('Выберите элемент для удаления', 'Ошибка');
                }
            }
        }
	});
});
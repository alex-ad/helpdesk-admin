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
	var tblStaff = [
		{
			xtype: 'treecolumn',
			text: 'Штатная структура',
			width: 400,
			flex: 1,
			sortable: true,
			dataIndex: 'name',
        }
	];

	Ext.define('modelStaff', {
		extend: 'Ext.data.TreeModel',
		fields: [
			{name: 'name', type: 'string'}
		],	
		autoLoad: true
	});

    Ext.define('modelCompanyList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'name', type: 'string' }
        ]
    });

    Ext.define('modelDivisionList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'name', type: 'string' }
        ]
    });

    Ext.define('modelFunctionList', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'name', type: 'string' }
        ]
    });
	
	var storeStaff = Ext.create('Ext.data.TreeStore', {
		type: 'tree',
		folderSort: true,
		model: 'modelStaff',
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.c_stafftree.php'
		},
	});

    var storeCompanyList = Ext.create('Ext.data.Store', {
        model: 'modelCompanyList',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getCompanyListAsName',
            reader: {
                type: 'json',
            }
        },
        autoLoad: true,
    });

    var storeDivisionList = Ext.create('Ext.data.Store', {
        model: 'modelDivisionList',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getDivisionListAsName',
            reader: {
                type: 'json',
            }
        },
        autoLoad: true,
    });

    var storeFunctionList = Ext.create('Ext.data.Store', {
        model: 'modelFunctionList',
        proxy: {
            type: 'ajax',
            url: '/saruman/modules/ajax.request.php?func=getFunctionListAsName',
            reader: {
                type: 'json',
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
			handler: function () {
				var elements = tree.getStore().getRange();
				var jsonData = [];
				var xlsColumns = ['Организация', 'Подразделение', 'Должность'];
				for ( i=0; i<elements.length; i++ ) {
					for ( j=0; j<elements[i].data.children.length; j++ ) {
						for ( k=0; k<elements[i].data.children[j].children.length; k++ ) {
							var tmp = [];
							tmp.push(elements[i].data.name);
							tmp.push(elements[i].data.children[j].name);
							tmp.push(elements[i].data.children[j].children[k].name);
							jsonData.push(tmp);
						}
					}
				}
				Ext.Ajax.request({
					url: '/saruman/modules/expexcel.php',
					params: {
						jsonData: Ext.encode(jsonData),
						reportName: 'Штатная структура',
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

// 2nd toolbar : organization
    var toolbar2 = Ext.create('Ext.toolbar.Toolbar', {
        dock: 'top',
        items: [{
            xtype: 'combobox',
            fieldLabel: 'Организация',
            itemId: 'textOrgName',
            width: 500,
            typeAhead: true,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            store: storeCompanyList,
            listeners: {
                afterrender: function(obj, eOpts) {
                   if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            validator: function(val) {
                if ( ( val.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) || ( val.length == 0 ) )
                    return true;
                else
                    return 'Разрешенные символы: а-я А-Я ёЁ a-z A-Z 0-9 ()"«»:;,_-+./\\ Пробел';
            }
        },{
            itemId: 'btnOrgRename',
            hidden: true,
            iconCls: 'fa fa-pencil blue',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.renameClick('Org');
        }
        },{
            itemId: 'btnOrgAdd',
            iconCls: 'fa fa-plus-circle green',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.addClick('Org');
            }
        },{
            itemId: 'btnOrgDel',
            hidden: true,
            iconCls: 'fa fa-minus-circle red',
            listeners: {
            afterrender: function(obj, eOpts) {
                if ( !getACR('acrWrite') ) obj.hide();
            }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.delClick('Org');
            }
        }]
    });

// 3rd toolbar : division
    var toolbar3 = Ext.create('Ext.toolbar.Toolbar', {
        dock: 'top',
        hidden: true,
        items: [{
            xtype: 'combobox',
            fieldLabel: 'Подразделение',
            itemId: 'textDivName',
            width: 500,
            typeAhead: true,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            store: storeDivisionList,
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            validator: function(val) {
                if ( ( val.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) || ( val.length == 0 ) )
                    return true;
                else
                    return 'Разрешенные символы: а-я А-Я ёЁ a-z A-Z 0-9 ()"«»:;,_-+./\\ Пробел';
            }
        },{
            itemId: 'btnDivRename',
            iconCls: 'fa fa-pencil blue',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.renameClick('Div');
            }
        },{
            itemId: 'btnDivAdd',
            iconCls: 'fa fa-plus-circle green',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.addClick('Div');
            }
        },{
            itemId: 'btnDivDel',
            iconCls: 'fa fa-minus-circle red',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.delClick('Div');
            }
        }]
    });

// 4th toolbar : function
    var toolbar4 = Ext.create('Ext.toolbar.Toolbar', {
        dock: 'top',
        hidden: true,
        items: [{
            xtype: 'combobox',
            fieldLabel: 'Должность',
            itemId: 'textFncName',
            width: 500,
            typeAhead: true,
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            store: storeFunctionList,
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            validator: function(val) {
                if ( ( val.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) || ( val.length == 0 ) )
                    return true;
                else
                    return 'Разрешенные символы: а-я А-Я ёЁ a-z A-Z 0-9 ()"«»:;,_-+./\\ Пробел';
            }
        },{
            itemId: 'btnFncRename',
            iconCls: 'fa fa-pencil blue',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.renameClick('Fnc');
            }
        },{
            itemId: 'btnFncAdd',
            iconCls: 'fa fa-plus-circle green',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.addClick('Fnc');
            }
        },{
            itemId: 'btnFncDel',
            iconCls: 'fa fa-minus-circle red',
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.hide();
                }
            },
            handler: function(button) {
                var panel = button.up('treepanel');
                panel.delClick('Fnc');
            }
        }]
    });
	
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
                        url: '/modules/ajax.c_staffmove.php',
                        params: {
                            data: Ext.encode(params),
                        },
                        success: function(data) {
                            data = JSON.parse(data.responseText);
                            if ( data.success ) {
                                Ext.toast(data.msg, 'Успешно');
                                storeStaff.getNodeById(target.id).set('elemParentId', params.nextParentId);
                                storeStaff.commitChanges();
                                tree.reconfigure(storeStaff);
                            } else {
                                Ext.toast(data.msg, 'ОШИБКА');
                            }

                        }
                    });
                }
            }
		},
		collapsable: false,
		useArrows: true,
		rootVisible: false,
		store: storeStaff,
		multiSelect: false,
		reserveScrollbar: true,
		height: 786,
		minHeight: 250,
		minWidth: 600,
		syncRowHeight: false,
		fullscreen: true,
		dynamic: true,
        dockedItems: [toolbar1, toolbar2, toolbar3, toolbar4],
		columns: tblStaff,
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
            var listOrgName = this.down('#textOrgName'),
            	listDivName = this.down('#textDivName'),
            	listFncName = this.down('#textFncName'),
                selectedNode;
                this.down('#btnOrgDel').show();
                this.down('#btnOrgRename').show();
                this.down('#btnDivDel').show();
                this.down('#btnDivRename').show();
                this.down('#btnFncDel').show();
                this.down('#btnFncRename').show();
            if (selection.length) {
                selectedNode = selection[0].data;
                if ( selectedNode.ntype == 'company' ) {
                    toolbar2.show();
                    toolbar3.show();
                    toolbar4.hide();
                    this.down('#btnDivDel').hide();
                    this.down('#btnDivRename').hide();
                    listOrgName.setValue(selectedNode.name);
				} else if ( selectedNode.ntype == 'division' ) {
                    toolbar2.hide();
                    toolbar3.show();
                    toolbar4.show();
                    this.down('#btnFncDel').hide();
                    this.down('#btnFncRename').hide();
                    listDivName.setValue(selectedNode.name);
				} else if ( selectedNode.ntype == 'function' ) {
                    toolbar2.hide();
                    toolbar3.hide();
                    toolbar4.show();
                    listFncName.setValue(selectedNode.name);
				}
			} else {
                toolbar2.show();
                toolbar3.hide();
                toolbar4.hide();
                this.down('#btnOrgDel').hide();
                this.down('#btnOrgRename').hide();
			}
        },
        addClick: function(btn='') {
            var target = this.selModel.getSelection()[0] || this.getRootNode(),
                textName = '#text' + btn + 'Name',
                inputField = this.down(textName),
				params = {};
            params.elemName = inputField && inputField.getRawValue();
            params.elemType = btn.toLowerCase();
            params.elemId = target.data.elemId;
            params.elemParentId = target.data.elemParentId ? target.data.elemParentId : '';
            if ( params.elemName ) {
                if ( params.elemName.length > 1 ) {
                    if ( !params.elemName.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) {
                        Ext.Msg.alert('Ошибка', 'В имени присутствуют запрещенные символы');
                        return;
                    }
                    Ext.Ajax.request({
                        url: '/saruman/modules/ajax.c_staffadd.php',
                        params: {
                            data: Ext.encode(params),
                        },
                        success: function(data) {
                            data = JSON.parse(data.responseText);
                            if ( data.success ) {
                                if ( params.elemType === 'div' ) {//new division
                                    var parentId = ( params.elemParentId === '' ) ? params.elemId.substr(4) : params.elemParentId.substr(4);
                                    var parentNode = ( target.data.parentId === 'root' ) ? target.data.id : target.data.parentId;
                                    storeStaff.getNodeById(parentNode).set('leaf', false);
                                    storeStaff.getNodeById(parentNode).appendChild({
                                        name:  params.elemName,
                                        comp_id: parentId,
                                        divId: data.elemId,
                                        elemParentId: 'org_'+parentId,
                                        ntype: 'division',
                                        elemId: 'div_'+data.elemId,
                                        children: null,
                                        loaded: true
                                    });
                                    storeDivisionList.reload();
                                } else if ( params.elemType === 'fnc' ) {//new function
                                    var parentId = (target.data.ntype === 'division') ? params.elemId.substr(4) : target.data.parentId;
                                    var parentNode = (target.data.ntype === 'division') ? target.data.id : target.data.parentId;
                                    storeStaff.getNodeById(parentNode).set('leaf', false);
                                    storeStaff.getNodeById(parentNode).appendChild({
                                        name:  params.elemName,
                                        div_id: parentId,
                                        elemId: 'fnc_'+data.elemId,
                                        elemParentId: 'div_'+parentId,
                                        ntype: 'function',
                                        loaded: true,
                                        leaf: true
                                    });
                                    storeFunctionList.reload();
                                } else if ( params.elemType === 'org' ) {//new company
                                    storeStaff.getRootNode().appendChild({
                                        name:  params.elemName,
                                        ntype: 'company',
                                        elemId: 'org_'+data.elemId,
                                        compId: data.elemId,
                                        children: null,
                                        loaded: true
                                    });
                                    storeCompanyList.reload();
                                }
                                Ext.toast(data.msg, 'Успешно');
                                storeStaff.commitChanges();
                                tree.reconfigure(storeStaff);
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
            var target = this.selModel.getSelection()[0] || this.getRootNode(),
                textName = '#text' + btn + 'Name',
                inputField = this.down(textName),
                params = {};
            params.elemName = inputField && inputField.getRawValue();
            params.elemType = btn.toLowerCase();
            params.elemId = target.data.elemId;
            params.elemParentId = target.data.elemParentId ? target.data.elemParentId : '';
            if ( params.elemName ) {
                if ( params.elemName.length > 1 ) {
                    if ( !params.elemName.match(/^[а-яА-ЯёЁa-zA-Z0-9()"«»:;,_\-\+\.\/\\ ]+$/gmiu) ) {
                        Ext.Msg.alert('Ошибка', 'В имени присутствуют запрещенные символы');
                        return;
                    }
                    Ext.Ajax.request({
                        url: '/saruman/modules/ajax.c_staffrnm.php',
                        params: {
                            data: Ext.encode(params),
                        },
                        success: function(data) {
                            data = JSON.parse(data.responseText);
                            if ( data.success ) {
                                storeStaff.getNodeById(target.id).set('name', params.elemName);
                                //var record = storeStaff.getNodeById(target.id);
                                //tree.getSelectionModel().select(record);
                                Ext.toast(data.msg, 'Успешно');
                                if ( params.elemType === 'org' ) {
                                    storeCompanyList.reload();
                                } else if ( params.elemType === 'div' ) {
                                    storeDivisionList.reload();
                                } else if ( params.elemType === 'fnc' ) {
                                    storeFunctionList.reload();
                                }
                                storeStaff.commitChanges();
                                tree.reconfigure(storeStaff);
                            } else {
                                Ext.Msg.alert('Ошибка', data.msg);
                            }
                        }
                    });
                } else {
                    Ext.toast('Длина имени должна быть более одного символа', 'Ошибка');
                }
            }
        },
        delClick: function(btn='') {
            var target = this.selModel.getSelection()[0] || this.getRootNode(),
                textName = '#text' + btn + 'Name',
                inputField = this.down(textName),
                params = {};
            params.elemType = btn.toLowerCase();
            params.elemName = inputField && inputField.getRawValue();
            params.elemId = target.data.elemId;
            params.elemParentId = target.data.elemParentId ? target.data.elemParentId : '';
            if ( params.elemName ) {
                Ext.MessageBox.show({
                    title: 'Удаление',
                    msg: 'Вы действительно хотите безвозвратно удалить со всеми вложениями выбранный элемент?',
                    buttons: Ext.MessageBox.OKCANCEL,
                    icon: Ext.MessageBox.WARNING,
                    fn: function(btn) {
                        if ( btn == 'ok' ) {
                            Ext.Ajax.request({
                                url: '/saruman/modules/ajax.c_staffdel.php',
                                params: {
                                    data: Ext.encode(params),
                                },
                                success: function(data) {
                                    data = JSON.parse(data.responseText);
                                    if ( data.success ) {
                                        target.remove();
                                        Ext.toast(data.msg, 'Успешно');
                                        if ( params.elemType === 'org' ) {
                                            storeCompanyList.reload();
                                        } else if ( params.elemType === 'div' ) {
                                            storeDivisionList.reload();
                                        } else if ( params.elemType === 'fnc' ) {
                                            storeFunctionList.reload();
                                        }
                                        storeStaff.commitChanges();
                                        tree.reconfigure(storeStaff);
                                    } else {
                                        Ext.Msg.alert('Ошибка', data.msg);
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }
	});
});
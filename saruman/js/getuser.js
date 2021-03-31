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

	Ext.define('modelUser', {
		extend: 'Ext.data.Model',
		fields: [
			{ name: 'id', type: 'int' },
			{ name: 'name', type: 'string' },
			{ name: 'login', type: 'string' },
			{ name: 'company', type: 'string' },
			{ name: 'division', type: 'string' },
			{ name: 'func', type: 'string' },
			{ name: 'email', type: 'string' },
			{ name: 'phone', type: 'string' },
			{ name: 'location', type: 'string' },
			{ name: 'tnumber', type: 'string' },
			{ name: 'enabled', type: 'boolean' }
		]
	});

	Ext.define('modelCompanyList', {
		extend: 'Ext.data.Model',
		fields: [
			{ name: 'idComp', type: 'string' },
			{ name: 'company', type: 'string' }
		]
	});

	Ext.define('modelDivisionList', {
		extend: 'Ext.data.Model',
		fields: [
			{ name: 'idDiv', type: 'string' },
			{ name: 'division', type: 'string' }
		]
	});

	Ext.define('modelFunctionList', {
		extend: 'Ext.data.Model',
		fields: [
			{ name: 'idFunc', type: 'string' },
			{ name: 'func', type: 'string' }
		]
	});



	var storeUser = Ext.create('Ext.data.Store', {
		model: 'modelUser',
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.exchange.php?func=fnUser',
			reader: {
				type: 'json',
				idProperty: 'id'
			},
			writer: {
				type: 'json',
				rootProperty: 'write'
			}
		},
		autoLoad: true,
		autoSync: true
	});

	var storeCompanyList = Ext.create('Ext.data.Store', {
		model: 'modelCompanyList',
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.request.php?func=getCompanyListComplex',
			reader: {
				type: 'json',
				idProperty: 'idComp'
			}
		},
		autoLoad: true
	});

	var storeDivisionList = Ext.create('Ext.data.Store', {
		model: 'modelDivisionList',
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.request.php?func=getDivisionListAllComplex',
			reader: {
				type: 'json',
				idProperty: 'idDiv'
			}
		},
		autoLoad: true
	});

	var storeFunctionList = Ext.create('Ext.data.Store', {
		model: 'modelFunctionList',
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.request.php?func=getFunctionListAllComplex',
			reader: {
				type: 'json',
				idProperty: 'idFunc'
			}
		},
		autoLoad: true,
	});

	var tblUser = [
		{
			xtype: 'rownumberer',
			width: 35
		},
		{
			text: 'id',
			dataIndex: 'id',
			width: 35,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			}
        },
		{
			text: 'ФИО',
			dataIndex: 'name',
			minWidth: 200,
			summaryType: 'count',
			summaryRenderer: function(val) {
                return '<span style="font-weight: bold">Количество: ' + val + '<span>';
            },
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			},
			editor: {
				xtype: 'textfield',
				allowBlank: false,
				validator: function(val) {
					if ( val.match(/^([А-Яа-яёЁ]{2,})(\s)([А-Яа-яёЁ]{2,})(\s)([А-Яа-яёЁ]{2,})$/u) )
						return true;
					else
						return 'Это поле должно содержать только кириллические символы в формате "Фамилия Имя Отчество"';
				}
			}
        },
        {
            text: 'Логин',
            dataIndex: 'login',
            summaryType: 'count',
            summaryRenderer: function(val) {
                return '<span style="font-weight: bold">Количество: ' + val + '<span>';
            },
            filter: {
                type: 'string',
                itemDefaults: {
                    emptyText: 'Найти...'
                }
            },
            editor: {
                xtype: 'textfield',
                allowBlank: true,
                validator: function(val) {
                    if ( val.match(/^([vVkKiIsI]{2})([0-9]{4})$/) )
                        return true;
                    else
                        return 'Имя доменной учетной записи пользователя в формате "vk1234" или "is1234"';
                }
            }
        },
		{
			text: 'Организация',
			dataIndex: 'company',
			flex: 1,
			filter: 'list',
			editor: new Ext.form.field.ComboBox({
				typeAhead: true,
				allowBlank: false,
				triggerAction: 'all',
				store: storeCompanyList,
				displayField: 'company',
				valueField: 'idComp',
				listeners : {
					change : function(field, newValue, o, e) {
						storeDivisionList.getProxy().extraParams = { data: newValue };
						storeDivisionList.load();
						var listDiv = Ext.ComponentQuery.query("#comboDivision")[0];
						listDiv.setStore(storeDivisionList);
						listDiv.setValue(division);
					}
				},
                validator: function(val) {
                    if ( ( val.match(/^([0-9а-яА-ЯёЁ,\.()\-/\\№"«»_ ]){3,}$/gmiu) ) || ( val.length == 0 ) )
                        return true;
                    else
                        return 'Разрешенные символы: 0-9 а-Я ёЁ , .() - / \\ № " «» _ Пробел';
                }
			}),

        },
        {
			text: 'Подразделение',
			dataIndex: 'division',
			//itemId: 'comboDivision',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			},
			editor: new Ext.form.field.ComboBox({
                itemId: 'comboDivision',
				typeAhead: true,
				allowBlank: false,
				triggerAction: 'all',
				store: storeDivisionList,
				displayField: 'division',
				valueField: 'idDiv',
                listeners : {
                    change : function(field, newValue, o, e) {
                        storeFunctionList.getProxy().extraParams = { data: newValue };
                        storeFunctionList.load();
                        var listFunc = Ext.ComponentQuery.query("#comboFunction")[0];
                        listFunc.setStore(storeFunctionList);
                    }
                },
                validator: function(val) {
                    if ( ( val.match(/^([0-9а-яА-ЯёЁ,\.()\-/\\№"«»_ ]){3,}$/gmiu) ) || ( val.length == 0 ) )
                        return true;
                    else
                        return 'Разрешенные символы: 0-9 а-Я ёЁ , .() - / \\ № " «» _ Пробел';
                }
			}),
		},
		{
			text: 'Должность',
			dataIndex: 'func',
			//itemId: 'comboFunction',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			},
			editor: new Ext.form.field.ComboBox({
                itemId: 'comboFunction',
				typeAhead: true,
				allowBlank: false,
				triggerAction: 'all',
				store: storeFunctionList,
				displayField: 'func',
				valueField: 'idFunc',
                validator: function(val) {
                    if ( ( val.match(/^([0-9а-яА-ЯёЁ,\.()\-/\\№"«»_ ]){3,}$/gmiu) ) || ( val.length == 0 ) )
                        return true;
                    else
                        return 'Разрешенные символы: 0-9 а-Я ёЁ , .() - / \\ № " «» _ Пробел';
                }
			}),
		},
		{
			text: 'E-mail',
			dataIndex: 'email',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			},
			editor: {
				xtype: 'textfield',
				allowBlank: true,
				validator: function(val) {
					if ( ( val.match(/^([A-Za-z0-9_\.\-]{2,})(@)([a-zA-Z0-9\.]){3,}$/) ) || ( val.length == 0 ) )
						return true;
					else
						return 'Это поле должно содержать адрес в формате "user@domain"';
				}
			}
		},
		{
			text: 'Телефон',
			dataIndex: 'phone',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			},
			editor: {
				xtype: 'textfield',
				allowBlank: true,
				validator: function(val) {
					if ( ( val.match(/^([0-9\-,\+\(\)\s]{4,})$/u) ) || ( val.length == 0 ) )
						return true;
					else
						return 'Это поле может содержать только цифры "0-9", символы "+-,()" и "пробел"';
				}
			}
		},
        {
            text: 'Месторасположение',
            dataIndex: 'location',
            flex: 1,
            filter: {
                type: 'string',
                itemDefaults: {
                    emptyText: 'Найти...'
                }
            },
            editor: {
                xtype: 'textfield',
                allowBlank: true,
                validator: function(val) {
                    if ( ( val.match(/^([а-яА-ЯёЁa-zA-Z0-9\-,\.\(\)\s\/]{4,})$/u) ) || ( val.length == 0 ) )
                        return true;
                    else
                        return 'Это поле может содержать только цифры, буквы, символы "-,.()/" и "пробел"';
                }
            }
        },
        {
            text: 'Табельный номер',
            dataIndex: 'tnumber',
            flex: 1,
            filter: {
                type: 'string',
                itemDefaults: {
                    emptyText: 'Найти...'
                }
            },
            editor: {
                xtype: 'textfield',
                allowBlank: true,
                validator: function(val) {
                    if ( ( val.match(/^([а-яА-ЯёЁ0-9\-]{4,})$/u) ) || ( val.length == 0 ) )
                        return true;
                    else
                        return 'Это поле может содержать только цифры, буквы, дефис';
                }
            }
        },
		{
			text: 'Активирован',
			xtype: 'checkcolumn',
			dataIndex: 'enabled',
			width: 100,
			filter: 'list',
			editor: {
				xtype: 'checkbox',
				cls: 'x-grid-checkheader-editor'
			},
            listeners: {
                afterrender: function(obj, eOpts) {
                    if ( !getACR('acrWrite') ) obj.disable();
                }
            }
		}
	];
		
	var toolbar = Ext.create('Ext.toolbar.Toolbar', {
		dock: 'top',
		items: [{
			text: 'Очистить фильтры',
			iconCls: 'pictos pictos-delete_black1 red',
			handler: function () {
				grid.filters.clearFilters()
			}
		}, {
			text: 'Обновить таблицу',
			iconCls: 'pictos pictos-table blue',
			handler: function () {
				var params = {};
				params.request = 'buildRVSGrid';
				$.post("/saruman/modules/ajax.request.php", params, function(data){
					grid.reconfigure();
					grid.getStore().load();
				}, "text");
			}
		}, '-', {
			text: 'Експорт в Excel',
			iconCls: 'fa fa-file-excel-o green',
			handler: function () {
				var elements = grid.getStore().getRange();
				var jsonData = [];
				var xlsColumns = ['id', 'ФИО', 'Логин', 'Организация', 'Подразделение', 'Должность', 'E-mail', 'Телефон', 'Месторасположение', 'Табельный номер', 'Активирован'];
				Ext.Array.each(elements, function (item, index) {
					jsonData.push(item.getData());
				});
				Ext.Ajax.request({
					url: '/saruman/modules/expexcel.php',
					params: {
						jsonData: Ext.encode(jsonData),
						reportName: 'Список пользователей',
						headersArray: Ext.encode(xlsColumns)
					},
					success: function (response) {
						var rObj = Ext.decode(response.responseText);
						window.location.href = '/saruman/modules/expexcel.php?fileKey=' + rObj.fileKey;
					}
				});
			}
		}, '-', {
			text: 'Добавить пользователя',
			iconCls: 'pictos pictos-user gray',
			listeners: {
				afterrender: function(obj, eOpts) {
					if ( !getACR('acrWrite') ) obj.hide();
				}
			},
			handler: function() {
				var users = grid.getStore(),
				rec = new modelUser({
					id: 0,
					name: '',
					login: '',
					company: '',
					division: '',
					func: '',
					email: '',
					phone: '',
					location: '',
					tnumber: '',
					enabled: false
				});
				users.insert(0, rec);
				grid.findPlugin('rowediting').startEdit(rec, 0);
			}
		}
	]
	});
		
	var grid = Ext.create('Ext.grid.Panel', {
		store: storeUser,
		xtype: [
			'row-numberer',
			'grouped-grid',
			'grid-filtering',
			'big-data-grid',
			'grouped-header-grid'
		],
		selModel: {
			type: 'rowmodel'
		},
		plugins: [{
            ptype: 'rowediting',
            clicksToEdit: 2,
			clicksToMoveEditor: 1,
			autoCancel: true,
        },
		{
			ptype: 'gridfilters'
		}],
        listeners: {
            selectionchange: function(selModel, selections) {
                this.getPlugins()[0].disabled = !getACR('acrWrite');
            }
        },
		loadMask: true,
		stateful: true,
		stateId	: 'stateful-filter-grid',
		defaultListenerScope: true,
		draggable: false,
		enableColumnHide: true,
		enableTextSelection: true,
		headerCheckbox: true,
		resizable: false,
		forceFit:false,
		tbar: toolbar,
    	features: [{
			ftype: 'groupingsummary'
		}],
		columns: tblUser,
		height: 786,
		minHeight: 250,
		minWidth: 600,
		syncRowHeight: false,
		fullscreen: true,
		dynamic: true,
		renderTo: 'grid'
	});
});
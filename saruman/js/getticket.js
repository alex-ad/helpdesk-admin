Ext.onReady(function() {
    function getACR(acr) {
        //return true;
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
	Ext.define('modelTicket', {
		extend: 'Ext.data.Model',
		fields: [
			{ name: 'id', type: 'int' },
			{ name: 'name', type: 'string' },
			{ name: 'company', type: 'string' },
			{ name: 'division', type: 'string' },
			{ name: 'func', type: 'string' },
			{ name: 'resource', type: 'string' },
			{ name: 'service', type: 'string' },
			{ name: 'start', type: 'string' },
			{ name: 'file', type: 'string' }
		]
	});
	
	var storeTicket = Ext.create('Ext.data.Store', {
		model: 'modelTicket',
		autoSync: true,
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.exchange.php?func=getTicketList',
			reader: {
				type: 'json'
			}
		},
		autoLoad: true
	});
	 
	var tblTicket = [
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
			}
        },
		{
			text: 'Организация',
			dataIndex: 'company',
			flex: 1,
			filter: 'list'
        },
        {
			text: 'Подразделение',
			dataIndex: 'division',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			}
		},
		{
			text: 'Должность',
			dataIndex: 'func',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			}
		},
		{
			text: 'ИТ-ресурс',
			dataIndex: 'resource',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			}
		},
		{
			text: 'ИТ-услуга',
			dataIndex: 'service',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			}
		},
		{
			text: 'Дата',
			dataIndex: 'start',
			flex: 1,
			filter: {
				type: 'string',
				itemDefaults: {
					emptyText: 'Найти...'
				}
			}
		},
		{
			text: 'Бланк',
			dataIndex: 'file',
			flex: 1,
			renderer: function(val) {
                if ( val.length > 0 ) {
					return '<a href="'+val+'" target="_blank" download>Сохранить</a>';
				} else {
					return '';
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
				$.post("/saruman/modules/request.php", params, function(data){
					grid.reconfigure();
					grid.getStore().load();
				}, "text");
			}
		}, {
			text: 'Експорт в Excel',
			iconCls: 'fa fa-file-excel-o green',
			handler: function () {
				var elements = grid.getStore().getRange();
				var jsonData = [];
				var xlsColumns = ['id', 'ФИО', 'Телефон', 'Организация', 'Подразделение', 'Должность', 'ИТ-ресурс', 'ИТ-услуга', 'Дата', 'Бланк заявки'];
				Ext.Array.each(elements, function (item, index) {
					jsonData.push(item.getData());
				});
				Ext.Ajax.request({
					url: '/saruman/modules/expexcel.php',
					params: {
						jsonData: Ext.encode(jsonData),
						reportName: 'Список заявок',
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
	
	var grid = Ext.create('Ext.grid.Panel', {
		//title: '',
		store: storeTicket,
		xtype: [
			'row-numberer',
			'grouped-grid',
			'grid-filtering',
			'big-data-grid',
			'grouped-header-grid'
		],
		plugins: [{
			ptype: 'gridfilters'
		}],
		loadMask: true,
		stateful: true,
		stateId	: 'stateful-filter-grid',
		defaultListenerScope: true,
		draggable: false,
		enableColumnHide: true,
		enableTextSelection: true,
		headerCheckbox: true,
		resizable: false,
		//enableLocking : true,
		forceFit:false,
		tbar: toolbar,
		columns: tblTicket,
		height: 786,
		minHeight: 250,
		minWidth: 600,
		syncRowHeight: false,
		fullscreen: true,
		dynamic: true,
		renderTo: 'grid'
	});
});
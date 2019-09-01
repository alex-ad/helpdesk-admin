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
	
	Ext.define('modelAdmin', {
		extend: 'Ext.data.Model',
		fields: [
			{ name: 'id', type: 'string' },
			{ name: 'name', type: 'string' },
			{ name: 'login', type: 'string' },
			{ name: 'acr', type: 'boolean' }
		]
	});

	var storeAdmin = Ext.create('Ext.data.Store', {
		model: 'modelAdmin',
		proxy: {
			type: 'ajax',
			url: '/saruman/modules/ajax.c_su.php',
			reader: {
				type: 'json',
                idProperty: 'id'
			},
            writer: {
                type: 'json',
                rootProperty: 'write'
            }
		},
		sorters: ['name'],
		autoLoad: true,
		autoSync: true
	});

	var tblAdmin = [
		{
			xtype: 'rownumberer',
			width: 35
		},
		{
			text: 'ФИО',
			dataIndex: 'name',
			minWidth: 200
        },
        {
            text: 'Логин',
            dataIndex: 'login',
            sortable: true,
        },
		{
			text: 'Администратор',
			xtype: 'checkcolumn',
			dataIndex: 'acr',
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
            },
		}
	];
		
	var grid = Ext.create('Ext.grid.Panel', {
		store: storeAdmin,
		loadMask: true,
		stateful: true,
		defaultListenerScope: true,
		draggable: false,
		enableColumnHide: false,
		enableTextSelection: true,
		headerCheckbox: true,
		resizable: false,
		forceFit:false,
		columns: tblAdmin,
		height: 786,
		minHeight: 250,
		minWidth: 600,
		syncRowHeight: false,
		fullscreen: true,
		dynamic: true,
		renderTo: 'grid'
	});
});
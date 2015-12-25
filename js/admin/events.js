Ext.onReady(function()
{
	var pageUrl = intelli.config.admin_url + '/events/';

	if (Ext.get('js-grid-placeholder'))
	{
		intelli.events =
		{
			columns: [
				'selection',
				{name: 'title', title: _t('title'), width: 2, editor: 'text'},
				{name: 'username', title: _t('member'), width: 240},
				{name: 'date', title: _t('date'), width: 120, editor: 'date'},
				{name: 'date_end', title: _t('date_end'), width: 120, editor: 'date'},
				'status',
				'update',
				'delete'
			],
			url: pageUrl
		};

		intelli.events = new IntelliGrid(intelli.events, false);

		intelli.events.toolbar = Ext.create('Ext.Toolbar', {items:
		[
			{
				emptyText: _t('title'),
				name: 'title',
				listeners: intelli.gridHelper.listener.specialKey,
				xtype: 'textfield'
			}, {
				displayField: 'title',
				editable: false,
				emptyText: _t('status'),
				id: 'fltStatus',
				name: 'status',
				store: intelli.events.stores.statuses,
				typeAhead: true,
				valueField: 'value',
				xtype: 'combo'
			}, {
				handler: function(){intelli.gridHelper.search(intelli.events);},
				id: 'fltBtn',
				text: '<i class="i-search"></i> ' + _t('search')
			}, {
				handler: function(){intelli.gridHelper.search(intelli.events, true);},
				text: '<i class="i-close"></i> ' + _t('reset')
			}
		]});

		intelli.events.init();
	}
});
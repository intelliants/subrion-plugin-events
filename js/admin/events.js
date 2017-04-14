Ext.onReady(function () {
    var grid = new IntelliGrid(
        {
            columns: [
                'selection',
                {name: 'title', title: _t('title'), width: 2, editor: 'text'},
                {name: 'owner', title: _t('owner'), width: 240},
                {name: 'date', title: _t('date'), width: 165, editor: 'date'},
                {name: 'date_end', title: _t('date_end'), width: 165, editor: 'date'},
                'status',
                'update',
                'delete'
            ]
        }, false);

    grid.toolbar = Ext.create('Ext.Toolbar', {
        items: [
            {
                emptyText: _t('text'),
                name: 'text',
                listeners: intelli.gridHelper.listener.specialKey,
                xtype: 'textfield'
            }, {
                displayField: 'title',
                editable: false,
                emptyText: _t('status'),
                id: 'fltStatus',
                name: 'status',
                store: grid.stores.statuses,
                typeAhead: true,
                valueField: 'value',
                xtype: 'combo'
            }, {
                handler: function () {
                    intelli.gridHelper.search(grid);
                },
                id: 'fltBtn',
                text: '<i class="i-search"></i> ' + _t('search')
            }, {
                handler: function () {
                    intelli.gridHelper.search(grid, true);
                },
                text: '<i class="i-close"></i> ' + _t('reset')
            }]
    });

    grid.init();
});
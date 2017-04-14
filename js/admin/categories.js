Ext.onReady(function () {
    var grid = new IntelliGrid(
        {
            columns: [
                'selection',
                {name: 'title', title: _t('title'), width: 2, editor: 'text'},
                {name: 'slug', title: _t('slug'), width: 1, editor: 'text'},
                'status',
                'update',
                'delete'
            ]
        }, false);

    grid.toolbar = new Ext.Toolbar({
        items: [
            {
                emptyText: _t('title'),
                listeners: intelli.gridHelper.listener.specialKey,
                name: 'title',
                width: 250,
                xtype: 'textfield'
            }, {
                displayField: 'title',
                editable: false,
                emptyText: _t('status'),
                name: 'status',
                store: grid.stores.statuses,
                typeAhead: true,
                valueField: 'value',
                width: 100,
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
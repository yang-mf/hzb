define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'weici/index' + location.search,
                    add_url: 'weici/add',
                    edit_url: 'weici/edit',
                    del_url: 'weici/del',
                    multi_url: 'weici/multi',
                    table: 'weici',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'weici_id',
                sortName: 'weici_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'weici_id', title: __('Weici_id')},
                        {field: 'fenshu', title: __('Fenshu')},
                        {field: '2016', title: __('2016')},
                        {field: '2017', title: __('2017')},
                        {field: '2018', title: __('2018')},
                        {field: '2019', title: __('2019')},
                        {field: '2020', title: __('2020')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
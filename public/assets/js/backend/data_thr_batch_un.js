define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'data_thr_batch_un/index' + location.search,
                    add_url: 'data_thr_batch_un/add',
                    edit_url: 'data_thr_batch_un/edit',
                    del_url: 'data_thr_batch_un/del',
                    multi_url: 'data_thr_batch_un/multi',
                    table: 'data_thr_batch_un',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'school', title: __('School'), operate: 'LIKE'},
                        {field: 'province', title: __('Province'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: $.getJSON("data_thr_batch_un/type")},
                        {field: 'year', title: __('Year'), searchList: $.getJSON("data_thr_batch_un/year")},
                        {field: 'batch', title: __('Batch'), operate: false, visible: false},
                        {field: 'mean', title: __('Mean'), operate: 'BETWEEN'},
                        {field: 'min', title: __('Min'), operate: 'BETWEEN'},
                        {field: 'pass', title: __('Pass'), operate: 'BETWEEN'},
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
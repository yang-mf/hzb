define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'grades_remark/index' + location.search,
                    add_url: 'grades_remark/add',
                    edit_url: 'grades_remark/edit',
                    del_url: 'grades_remark/del',
                    multi_url: 'grades_remark/multi',
                    table: 'grades_remark',
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
                        {field: 'id', title: __('Id')},
                        {field: 'type', title: __('Type'), formatter: Table.api.formatter.label, custom: {1:'danger', 2:'info', 3:'primary'}, searchList: {1: __('优秀'), 2: __('良好'), 3: __('一般')}},
                        {field: 'category', title: __('Category'), formatter: Table.api.formatter.label, custom: {1:'success', 2:'warning', 3:'danger'}, searchList: {1: __('进步'), 2: __('保持'), 3: __('退步')}},
                        {field: 'tips', title: __('Tips'), operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('Hidden')}},
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
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'data_school/index' + location.search,
                    add_url: 'data_school/add',
                    edit_url: 'data_school/edit',
                    del_url: 'data_school/del',
                    multi_url: 'data_school/multi',
                    table: 'data_school',
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
                        {field: 'first', title: __('First'), searchList: {A: __('A'), B: __('B'), C: __('C'), D: __('D'), E: __('E'), F: __('F'), G: __('G'), H: __('H'), I: __('I'), J: __('J'), K: __('K'), L: __('L'), M: __('M'), N: __('N'), O: __('O'), P: __('P'), Q: __('Q'), R: __('R'), S: __('S'), T: __('T'), U: __('U'), V: __('V'), W: __('W'), X: __('X'), Y: __('Y'), Z: __('Z')}, visible: false},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'management', title: __('Management'), operate: false},
                        {field: 'province', title: __('Province'), operate: 'LIKE'},
                        {field: 'city', title: __('City'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: $.getJSON("data_school/type")},
                        {field: 'category', title: __('Category'), operate: false},
                        {field: 'nature', title: __('Nature'), searchList: $.getJSON("data_school/nature")},
                        {field: 'is_211', title: __('Is_211'), searchList: $.getJSON("data_school/is_211"), visible: false},
                        {field: 'is_985', title: __('Is_985'), searchList: $.getJSON("data_school/is_985"), visible: false},
                        {field: 'classic', title: __('Classic'), searchList: $.getJSON("data_school/classic"), visible: false},
                        {field: 'renown', title: __('Renown'), searchList: $.getJSON("data_school/renown"), visible: false},
                        {field: 'rank', title: __('Rank'), searchList: $.getJSON("data_school/rank"), visible: false},
                        {field: 'extend', title: __('院校补充'), operate: false},
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
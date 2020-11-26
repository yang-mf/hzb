define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'grades_log/index' + location.search,
                    add_url: 'grades_log/add',
                    edit_url: 'grades_log/edit',
                    del_url: 'grades_log/del',
                    multi_url: 'grades_log/multi',
                    table: 'grades_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'userschool.province', title: __('Userschool.province'), operate:'LIKE'},
                        {field: 'userschool.city', title: __('Userschool.city'), operate:'LIKE'},
                        {field: 'userschool.area', title: __('Userschool.area'), operate:'LIKE'},
                        {field: 'userschool.school', title: __('Userschool.school'), operate:'LIKE'},
                        {field: 'useracademic.academic_year', title: __('Useracademic.academic_year'), operate:'LIKE'},
                        {field: 'useracademic.term', title: __('Useracademic.term'), operate:'LIKE'},
                        {field: 'user.grade', title: __('User.grade'), operate:'LIKE'},
                        {field: 'user.nickname', title: __('User.nickname'), operate:'LIKE'},
                        {field: 'name', title: __('Name'), operate:'LIKE'},
                        {field: 'grades', title: __('Grades'), operate: false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                        //     var that = $.extend({}, this);
                        //     if (row.forecast_id != 0) {
                        //         return '';
                        //     }
                        //     var table = $(that.table).clone(true);
                        //     if (row.forecast_id == 0)
                        //         $(table).data("operate-edit", null);
                        //     that.table = table;
                        //     return Table.api.formatter.operate.call(that, value, row, index);
                        // }}
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name:'audit',
                                    title:'详情',
                                    icon: 'fa fa-reorder',
                                    classname: 'btn btn-xs btn-info btn-dialog btn-newSalesList',
                                    url: 'grades_log/details',
                                    hidden:function(row){
                                        if(row.forecast_id == 0){ 
                                            return true; 
                                        }
                                    }
                                }
    
                            ],
                            formatter: Table.api.formatter.buttons
                        }
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
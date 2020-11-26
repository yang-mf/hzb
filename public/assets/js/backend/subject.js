define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/index' + location.search,
                    add_url: 'subject/add',
                    edit_url: 'subject/edit',
                    del_url: 'subject/del',
                    multi_url: 'subject/multi',
                    table: 'subject',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                commonSearch: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'grade_txt', title: __('Grade_id')},
                        {field: 'subject_txt', title: __('Subject_id')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            $(".subject").change(function() {
                var status = $(this).is(':checked');
                if(status){
                    $(this).parent().next().next().find('.full').attr("disabled",false);// 移除 disabled
                    // console.log($(this).is(':checked'));
                }else{
                    $(this).parent().next().next().find('.full').attr("disabled",true).val('');// 增加 disabled 清除 value 值
                    // console.log($(this).is(':checked'));
                }
            });
        },
        edit: function () {
            Controller.api.bindevent();
            $(".subject").change(function() {
                var status = $(this).is(':checked');
                if(status){
                    $(this).parent().next().next().find('.full').attr("disabled",false);// 移除 disabled
                    // console.log($(this).is(':checked'));
                }else{
                    $(this).parent().next().next().find('.full').attr("disabled",true).val('');// 增加 disabled 清除 value 值
                    // console.log($(this).is(':checked'));
                }
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
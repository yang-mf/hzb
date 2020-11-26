define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'banner/index' + location.search,
                    add_url: 'banner/add',
                    edit_url: 'banner/edit',
                    del_url: 'banner/del',
                    multi_url: 'banner/multi',
                    table: 'banner',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        { field: 'attr', title: __('Attr'), searchList: { "none": __('无链接'), "outer": __('外部连接'), "inner": __('内部连接') }, formatter: Table.api.formatter.normal },
                        { field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image },
                        { field: 'url', title: __('Url'), operate: false, formatter: Controller.api.formatter.link },
                        { field: 'status', title: __('Status'), searchList: { "normal": __('Normal'), "hidden": __('Hidden') }, formatter: Table.api.formatter.status },
                        { field: 'createtime', title: __('Createtime'), operate: false, addclass: 'datetimerange', formatter: Table.api.formatter.datetime },
                        // {field: 'updatetime', title: __('Updatetime'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            // 监听链接属性值
            $("input[name='row[attr]']").click(function () {
                var attr = $(this).val()  //获取选中的radio的值
                // console.log(attr);
                if (attr == 'none') {
                    // 显示
                    $(".outerlink").attr("style", "display:none;");
                    $(".innerlink").attr("style", "display:none;");
                    // disable
                    $("input[name='row[url]']").attr("disabled", true);
                    $("select[name='row[url]']").attr("disabled", true);
                } else if (attr == 'outer') {
                    // 显示
                    $(".outerlink").attr("style", "display:block;");
                    $(".innerlink").attr("style", "display:none;");
                    // disable
                    $("input[name='row[url]']").attr("disabled", false);
                    $("select[name='row[url]']").attr("disabled", true);
                } else if (attr == 'inner') {
                    // 显示
                    $(".outerlink").attr("style", "display:none;");
                    $(".innerlink").attr("style", "display:block;");
                    // disable
                    $("input[name='row[url]']").attr("disabled", true);
                    $("select[name='row[url]']").attr("disabled", false);

                }
            });
        },
        edit: function () {
            Controller.api.bindevent();
            var attr = $("input[name='row[attr]']:checked").val();
            if (attr == 'none') {
                // 显示
                $(".outerlink").attr("style", "display:none;");
                $(".innerlink").attr("style", "display:none;");
                // disable
                $("input[name='row[url]']").attr("disabled", true);
                $("select[name='row[url]']").attr("disabled", true);
                // 清空值 
                $("#none").attr("disabled", false);
            } else if (attr == 'outer') {
                // 显示
                $(".outerlink").attr("style", "display:block;");
                $(".innerlink").attr("style", "display:none;");
                // disable
                $("input[name='row[url]']").attr("disabled", false);
                $("select[name='row[url]']").attr("disabled", true);
                // 清空值 
                $("#none").attr("disabled", true);
            } else if (attr == 'inner') {
                // 显示
                $(".outerlink").attr("style", "display:none;");
                $(".innerlink").attr("style", "display:block;");
                // disable
                $("input[name='row[url]']").attr("disabled", true);
                $("select[name='row[url]']").attr("disabled", false);
                // 清空值 
                $("#none").attr("disabled", true);
            }

            // 监听链接属性值
            $("input[name='row[attr]']").click(function () {
                var attr = $(this).val()  //获取选中的radio的值
                if (attr == 'none') {
                    // 显示
                    $(".outerlink").attr("style", "display:none;");
                    $(".innerlink").attr("style", "display:none;");
                    // disable
                    $("input[name='row[url]']").attr("disabled", true);
                    $("select[name='row[url]']").attr("disabled", true);
                    // 清空值 
                    $("#none").attr("disabled", false);
                } else if (attr == 'outer') {
                    // 显示
                    $(".outerlink").attr("style", "display:block;");
                    $(".innerlink").attr("style", "display:none;");
                    // disable
                    $("input[name='row[url]']").attr("disabled", false);
                    $("select[name='row[url]']").attr("disabled", true);
                    // 清空值 
                    $("#none").attr("disabled", true);
                } else if (attr == 'inner') {
                    // 显示
                    $(".outerlink").attr("style", "display:none;");
                    $(".innerlink").attr("style", "display:block;");
                    // disable
                    $("input[name='row[url]']").attr("disabled", true);
                    $("select[name='row[url]']").attr("disabled", false);
                    // 清空值 
                    $("#none").attr("disabled", true);
                }
            });
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                link: function (value, row, index) {
                    switch (row.attr) {
                        case 'none':
                            var html = '--';
                            break;
                        case 'outer':
                            var html = '<div class="input-group input-group-sm" style="width:250px;margin:0 auto;"><input type="text" class="form-control input-sm" value="' + row.url + '"><span class="input-group-btn input-group-sm"><a href="' + row.url + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                            break;
                        case 'inner':
                            var html = '<div class="input-group input-group-sm" style="width:250px;margin:0 auto;"><input type="text" class="form-control input-sm" value="/channel/public/index.php/admin/article/edit/ids/' + row.url + '"><span class="input-group-btn input-group-sm"><a href="/NVftLdwgMn.php/article/edit/ids/' + row.url + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                            break;
                        default:
                            var html = '--';
                    }
                    return html;
                }
            }
        }
    };
    return Controller;
});
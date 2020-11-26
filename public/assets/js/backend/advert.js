define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'advert/index' + location.search,
                    add_url: 'advert/add',
                    edit_url: 'advert/edit',
                    del_url: 'advert/del',
                    multi_url: 'advert/multi',
                    table: 'advert',
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
                        {field: 'id', title: __('Id'), operate: false},
                        {
                            field: 'area', title: __('区域'), searchList: function (column) {
                                return Template('categorytpl', {});
                            }, formatter: function (value, row, index) {
                                return '无';
                            }, visible: false
                        },
                        {field: 'area.mergename', title: __('区域'), operate: false},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        // {field: 'lng', title: __('Lng')},
                        // {field: 'lat', title: __('Lat')},
                        {field: 'views', title: __('Views'), operate: false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'weigh', title: __('Weigh')},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 搜索事件
            $(document).on('click','.form-commonsearch .btn-success',function () {
                var options = table.bootstrapTable('getOptions');
                var queryParams = options.queryParams;
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    //这一行必须要存在,否则在点击下一页时会丢失搜索栏数据
                    params = queryParams(params);
                    params.filter = '';
                    params.op = '';
                    //如果希望追加搜索条件,可使用
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    var op = params.op ? JSON.parse(params.op) : {};
                    
                    // 区域
                    var province = $("#c-province").val();
                    if(province){
                        filter.province = province;
                        op.province = '=';
                    }
                    var city = $("#c-city").val();
                    if(city){
                        filter.city = city;
                        op.city = '=';
                    }
                    area = $("#c-area").val();
                    if(area){
                        filter.area = area;
                        op.area = '=';
                    }
                    
                    // 标题
                    var title = $("input[name='title']").val();
                    if(title){
                        filter.title = title;
                        op.title = "LIKE";
                    }

                    // 地址
                    var address = $("input[name='address']").val();
                    if(address){
                        filter.address = address;
                        op.address = "LIKE";
                    }

                    // 创建时间
                    var createtime = $("input[name='createtime']").val();
                    if(createtime){
                        filter.createtime = createtime;
                        op.createtime = "RANGE";
                    }

                    // 状态
                    var status = $("select[name='status']").val();
                    if(status){
                        filter.status = status;
                        op.status = "=";
                    }

                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    // console.log(params);
                    return params;
                };
                //console.log('自定义：'+lists_flag);
                table.bootstrapTable('refresh',{});
                // Toastr.info("当前执行的是自定义搜索,搜索area等于"+area+"的数据");
                return false;
            });
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'advert/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'advert/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'advert/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
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
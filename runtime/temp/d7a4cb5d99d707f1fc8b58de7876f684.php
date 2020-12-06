<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:76:"E:\phpstudy_pro\WWW\fw366.cn\public/../application/index\view\test\test.html";i:1607246481;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/assets/css/index.css">
    <title>test</title>

</head>
<body>
<div id="app">
    <div v-show="show_table">
    <el-form ref="form" :model="form" label-width="80px">
        <el-form-item label="分数" style="width: 20%">
            <el-input v-model="form.score" ></el-input>
        </el-form-item>
        <el-form-item label="年份">
            <el-select v-model="form.year" placeholder="请选择年份">
                <el-option label="2017" value="2017"></el-option>
                <el-option label="2018" value="2018"></el-option>
                <el-option label="2019" value="2019"></el-option>
                <el-option label="2020" value="2020"></el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="批次">
            <el-select v-model="form.batch" placeholder="请选择批次">
                <el-option label="一批" value="1"></el-option>
                <el-option label="二批" value="2"></el-option>
                <el-option label="三批" value="3"></el-option>
                <el-option label="大专" value="4"></el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="文科/理科">
            <el-select v-model="form.type" placeholder="请选择文科/理科">
                <el-option label="理科" value="reason"></el-option>
                <el-option label="文科" value="culture"></el-option>
            </el-select>
        </el-form-item>
        <div v-show="show_add">
            <el-form-item label="专业" style="width: 30%">
                <el-autocomplete
                        v-model="state"
                        :fetch-suggestions="querySearchProfession"
                        value-key="profession_name"
                        placeholder="请输入专业（默认本科专业）"
                        @select="handleSelectProfession"
                ></el-autocomplete>
                <el-button @click.native="profession_name($event)" id="profession1" :type="profession_primary('profession1')">本科</el-button>
                <el-button @click.native="profession_name($event)" id="profession2" :type="profession_primary('profession2')">专科</el-button>
            </el-form-item>
            <el-form-item label="院校名称" style="width: 30%">
                <el-autocomplete
                        v-model="state_school"
                        :fetch-suggestions="querySearchSchool"
                        value-key="school_name"
                        placeholder="请输入院校名称"
                        @select="handleSelectSchool"
                ></el-autocomplete>
            </el-form-item>
            <el-form-item label="办学类型" style="width: 30%">
                <el-button @click.native="school_type($event)" id="type1" :type="type_primary('type1')">公办</el-button>
                <el-button @click.native="school_type($event)" id="type2" :type="type_primary('type2')">民办</el-button>
                <el-button @click.native="school_type($event)" id="type3" :type="type_primary('type3')">内地与港澳台地区合作办学</el-button>
                <el-button @click.native="school_type($event)" id="type4" :type="type_primary('type4')">中外合作办学</el-button>
            </el-form-item>
            <el-form-item label="省份" style="width: 20%">
                <el-button @click.native="province_type($event)" id="province" :type="province_primary('province')">省份排序</el-button>
            </el-form-item>
        </div>
        <el-form-item>
            <el-button @click="onSubmit" id="button" >确定</el-button>
            <el-button>取消</el-button>
        </el-form-item>
    </el-form>
    </div>
    <div v-show="show_data">
        <el-table
                :cell-style="cellStyle"
                :data="show_info"
                @row-click="clickShow"
                style="width: 100%"
        >
            <el-table-column
                    prop="school_name"
                    label="学校名称"
                    sortable
                    width="250">
            </el-table-column>
            <el-table-column
                    prop="school_num"
                    label="院校代码"
                    sortable
                    width="100">
            </el-table-column>
            <el-table-column
                    prop="school_type"
                    label="公民办"
                    sortable
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="school_nature"
                    label="类别"
                    sortable
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="school_province"
                    label="省份"
                    sortable
                    width="180">
            </el-table-column>
        </el-table>
    </div>
    <div v-show="show_select_data">
        <el-table
                :cell-style="cellStyle"
                :data="show_select_info"
                style="width: 100%"
        >
            <el-table-column
                    prop="school_name"
                    label="学校名称"
                    sortable
                    width="250">
            </el-table-column>
            <el-table-column
                    prop="school_num"
                    label="院校代码"
                    sortable
                    width="100">
            </el-table-column>
            <el-table-column
                    prop="school_type"
                    label="公民办"
                    sortable
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="school_nature"
                    label="类别"
                    sortable
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="school_province"
                    label="省份"
                    sortable
                    width="180">
            </el-table-column>
        </el-table>
    </div>
    <template>
        <el-table
                :data="tableData"
                style="width: 100%">
            <el-table-column type="expand">
                <template slot-scope="props">
                    <el-form label-position="left" inline class="demo-table-expand">
                        <el-form-item label="商品名称">
                            <span>{{ props.row.name }}</span>
                        </el-form-item>
                        <el-form-item label="所属店铺">
                            <span>{{ props.row.shop }}</span>
                        </el-form-item>
                        <el-form-item label="商品 ID">
                            <span>{{ props.row.id }}</span>
                        </el-form-item>
                        <el-form-item label="店铺 ID">
                            <span>{{ props.row.shopId }}</span>
                        </el-form-item>
                        <el-form-item label="商品分类">
                            <span>{{ props.row.category }}</span>
                        </el-form-item>
                        <el-form-item label="店铺地址">
                            <span>{{ props.row.address }}</span>
                        </el-form-item>
                        <el-form-item label="商品描述">
                            <span>{{ props.row.desc }}</span>
                        </el-form-item>
                    </el-form>
                </template>
            </el-table-column>
            <el-table-column
                    label="商品 ID"
                    prop="id">
            </el-table-column>
            <el-table-column
                    label="商品名称"
                    prop="name">
            </el-table-column>
            <el-table-column
                    label="描述"
                    prop="desc">
            </el-table-column>
        </el-table>
    </template>
</div>
</body>
<script src="/assets/js/vue.js"></script>
<script src="/assets/js/index.js"></script>
<script src="/jquery-3.5.1.min.js"></script>
<<style>
    .demo-table-expand {
        font-size: 0;
    }
    .demo-table-expand label {
        width: 90px;
        color: #99a9bf;
    }
    .demo-table-expand .el-form-item {
        margin-right: 0;
        margin-bottom: 0;
        width: 50%;
    }
</style>
<script>
    new Vue({
        el: '#app',
        data: function() {
            return {
                form: {
                    score: '',      //分数值
                    batch: '',      //批次值
                    type: '',       //文科理科值
                    year: '',       //年份值
                    show_info: '',  //分数值
                },
                show_table: true,
                show_data: false,
                show_select_data: false,
                show_add:false,
                show_info:[],
                show_select_info:[],
                profession_restaurants: [],
                school_restaurants: [],
                state: '',
                state_school:'',
                timeout:  null,
                sta_profession:'',  //input输入的专业值
                sta_school:'',      //input输入的学校值
                profession:'',      //专业（本科/专科）值
                pp_type:'',         //公办民办值
                province:'',        //省份值
                tableData: [{
                    id: '12987122',
                    name: '好滋好味鸡蛋仔',
                    category: '江浙小吃、小吃零食',
                    desc: '荷兰优质淡奶，奶香浓而不腻',
                    address: '上海市普陀区真北路',
                    shop: '王小虎夫妻店',
                    shopId: '10333'
                }, {
                    id: '12987123',
                    name: '好滋好味鸡蛋仔',
                    category: '江浙小吃、小吃零食',
                    desc: '荷兰优质淡奶，奶香浓而不腻',
                    address: '上海市普陀区真北路',
                    shop: '王小虎夫妻店',
                    shopId: '10333'
                }, {
                    id: '12987125',
                    name: '好滋好味鸡蛋仔',
                    category: '江浙小吃、小吃零食',
                    desc: '荷兰优质淡奶，奶香浓而不腻',
                    address: '上海市普陀区真北路',
                    shop: '王小虎夫妻店',
                    shopId: '10333'
                }, {
                    id: '12987126',
                    name: '好滋好味鸡蛋仔',
                    category: '江浙小吃、小吃零食',
                    desc: '荷兰优质淡奶，奶香浓而不腻',
                    address: '上海市普陀区真北路',
                    shop: '王小虎夫妻店',
                    shopId: '10333'
                }]
            }
        },
        methods: {
            onSubmit() {
                //专业，学校，公民办，省份排序以及信息展示，默认为false，有值改为true
                // this.show_data=true;
                // this.show_add=true;
                var score = this.form.score;                //分数值
                var batch = this.form.batch;                //批次值
                var type = this.form.type;                  //文科理科值
                var year = this.form.year;                  //年份值
                var sta_profession = this.sta_profession.profession_name ? this.sta_profession.profession_name : '';     //input输入的专业值
                // var sta_profession = this.sta_profession ? this.sta_profession : '';     //input输入的专业值
                var sta_school = this.sta_school.school_name ? this.sta_school.school_name : '';                 //input输入的学校值
                // console.log(sta_school);return;
                var profession = this.profession ? this.profession: 'profession1';      //专业（本科/专科）值
                var pp_type = this.pp_type ? this.pp_type: '';                          //公办民办值
                var province = this.province ? this.province: '';                       //省份值
                var show_info = this.show_info ;
                var _this = this   //很重要！！
                if(sta_profession=='' && sta_school==''
                    && pp_type=='' ){
                    // console.log(111);return
                    //查询当前输入的分数，批次，文理科，年份
                    $.post('/index/test/get_ajax_info', {
                        'score':score ,
                        'batch':batch ,
                        'type':type,
                        'year':year,
                    }, function (response) {
                        if(response){
                            //默认为false，有值改为true
                            _this.show_data=true;
                            _this.show_add=true;
                            _this.show_select_data=false;
                            _this.show_info = response.info;
                            console.log(response.info);
                        }
                    });
                    return
                }
                // console.log(score);return;

                //查询当前输入的分数，批次，文理科，年份以及专业名称，学校名称，专业（本科/专科），公民办，省份排序值
                $.post('/index/test/get_select_info', {
                    'score':score ,
                    'batch':batch ,
                    'type':type,
                    'year':year,
                    'show_info':JSON.stringify(show_info),
                    'sta_profession':sta_profession,
                    'sta_school':sta_school,
                    'profession':profession,
                    'pp_type':pp_type,
                }, function (response) {
                    if(response['code']==2){
                        _this.show_data=false;
                        _this.show_select_data=false;
                        alert(response['message']);return ;
                    }
                    if(response['code']==1){
                        console.log(response.info);
                        _this.show_data=false;
                        _this.show_select_data=true;
                        _this.show_select_info = response['info'];
                        // console.log(333);return
                    }

                });
                return;
            },
            cellStyle(row,column,rowIndex,columnIndex){
                // console.log(row);
                // console.log(row.row.color);
                if(row.row.color==='red'){
                    return 'color:red'
                }else if(row.row.color==='blue'){
                    return 'color:blue'
                }else if(row.row.color==='green'){
                    return 'color:green'
                }
            },
            //从后台获取部分的专业数据
            load_profession_name(state=null, cb) {
                var that = this;
                var profession = this.profession ? this.profession: '';      //专业（本科/专科）值
                // console.log(profession);return
                if(state){
                    $.post('/index/test/get_select_profession_name', {
                        'profession':profession,
                        'word':state,
                        async: false,
                    }, function (response) {
                        that.profession_restaurants = response;
                        cb(response);return
                    });
                }else {
                    $.post('/index/test/get_profession_name', {
                        'profession':profession,
                    }, function (response) {
                        that.profession_restaurants = response;
                        cb(response);return
                    });
                }
            },
            //从后台获取部分的学校数据
            load_school_name(state_school=null, cb) {
                var that = this;
                if(state_school){
                    $.post('/index/test/get_select_school_name', {
                        'word':state_school,
                        async: false,
                    }, function (response) {
                        that.school_restaurants = response;
                        cb(response);
                        return;
                    });
                }else {
                    $.post('/index/test/get_school_name', {
                    }, function (response) {
                        that.school_restaurants = response;
                        cb(response);
                        return
                    });
                }
            },
            //输入专业名称的搜索
            querySearchProfession(queryString, cb) {
                var profession_restaurants = this.profession_restaurants;
                var _this = this;
                var state = _this.state;
                _this.sta_profession=state;
                console.log(_this.sta_profession);
                _this.load_profession_name(state, cb);return
            },
            //输入学校名称的搜索
            querySearchSchool(queryString, cb) {
                var school_restaurants = this.school_restaurants;
                var _this = this;
                var state_school = _this.state_school;
                _this.sta_school=state_school;
                // console.log(_this.sta_school);
                _this.load_school_name(state_school, cb);return
            },
            handleSelectProfession(item) {
                this.sta_profession = item;
                console.log(item);
            },
            handleSelectSchool(item) {
                this.sta_school = item;
                console.log(item);
            },
            //更改专业（本科/专科）
            profession_name:function (e){
                //获取当前点击的按钮的id值
                var current_id = e.currentTarget.id;
                var _this = this;
                var profession = _this.profession;
                _this.profession=current_id;
                if(profession==current_id){
                    _this.profession = '';
                }
            },
            //更改公民办
            school_type:function (e){
                //获取当前点击的按钮的id值
                var current_id = e.currentTarget.id;
                var _this = this;
                var pp_type = _this.pp_type;
                _this.pp_type=current_id;
                if(pp_type==current_id){
                    _this.pp_type = '';
                }
            },
            //更改省份
            province_type:function (e){
                //获取当前点击的按钮的id值
                var current_id = e.currentTarget.id;
                var _this = this;
                var province = _this.province;
                _this.province=current_id;
                if(province==current_id){
                    _this.province = '';
                }
            },
            //省份排序
            clickShow(row, event, column){
                console.log(row.school_num)

            },
            //更改专业默认选中值
            profession_primary(val){
                var _this = this;
                var profession=_this.profession;
                // console.log(val);
                if(val==profession){
                    return "primary";
                }
            },
            //更改公民办默认选中值
            type_primary(val){
                var _this = this;
                var pp_type=_this.pp_type;
                // console.log(val);
                if(val==pp_type){
                    return "primary";
                }
            },
            //更改省份默认选中值
            province_primary(val){
                var _this = this;
                var province=_this.province;
                // console.log(val);
                if(val==province){
                    return "primary";
                }
            },

        },
        mounted() {
            this.load_profession_name();
            this.load_school_name();
        }
    })
</script>
</html>



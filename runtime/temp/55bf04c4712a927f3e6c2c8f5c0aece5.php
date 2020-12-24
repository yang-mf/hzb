<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"E:\phpstudy_pro\WWW\fw366.cn\public/../application/index\view\test\agent.html";i:1608368181;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/assets/css/element.css">
    <title>Agent</title>
</head>
<body>
<div id="app">
    <div v-show="show_table">
        <el-form ref="form"
                 :model="form"
                 label-width="100px"
                 :rules="formRules"
        >
            <div v-show="input_show">
                <el-form-item label="分数" style="width: 20%" prop="score">
                    <el-input v-model="form.score" placeholder="请输入分数" :disabled=form.region ></el-input>
                </el-form-item>
                <el-form-item label="批次">
                    <el-select v-model="form.batch" placeholder="请选择批次" :disabled=form.region>
                        <el-option label="一批" value="1"></el-option>
                        <el-option label="二批" value="2"></el-option>
                        <el-option label="三批" value="3"></el-option>
                        <el-option label="大专" value="4"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="文科/理科" prop="type">
                    <el-select v-model="form.type" placeholder="请选择文科/理科" :disabled=form.region>
                        <el-option label="理科" value="reason"></el-option>
                        <el-option label="文科" value="culture"></el-option>
                    </el-select>
                </el-form-item>
            </div>
            <div v-show="show_add">
                <el-form-item label="专业" style="width: 40%">
                    <el-autocomplete
                            :debounce="0"
                            v-model="state"
                            :fetch-suggestions="querySearchProfession"
                            value-key="profession_name"
                            placeholder="请输入专业（默认本科专业）"
                            @select="handleSelectProfession"
                            :disabled=form.profession_region
                    ></el-autocomplete>
                </el-form-item>
                <el-form-item label="院校名称" style="width: 40%">
                    <el-autocomplete
                            :debounce="0"
                            v-model="state_school"
                            :fetch-suggestions="querySearchSchool"
                            value-key="school_name"
                            data-id="school_num"
                            placeholder="请输入院校名称"
                            @select="handleSelectSchool"
                    >
                    </el-autocomplete>
                    <el-alert
                            v-show="show_warn"
                            title="请以招生简介为准"
                            type="warning"
                            show-icon>
                    </el-alert>
                </el-form-item>
                <el-form-item label="办学类型" style="width: 55%">
                    <template>
                        <el-checkbox-group
                                v-model="checked_school_type"
                        >
                            <el-checkbox
                                    v-for="item in school_type"
                                    :label="item"
                            >{{item}}</el-checkbox>
                        </el-checkbox-group>
                    </template>
                </el-form-item>
                <el-form-item label="省份" style="width: 55%">
                    <template>
                        <el-checkbox-group
                                v-model="checked_province"
                                @change="province_name()">
                            <el-checkbox
                                    v-for="item in province"
                                    :label="item.province_name"
                            >{{item.province_name}}</el-checkbox>
                        </el-checkbox-group>
                    </template>
                </el-form-item>
            </div>
            <el-form-item>
                <el-button @click="onSubmit" id="button" >确定</el-button>
                <el-button>取消</el-button>
            </el-form-item>
        </el-form>
    </div>
    <div v-show="show_data">
        <template>
            <div
                    :data="show_info"
                    style="width: 100%"
                    v-for="(info,index) in show_info">
                <div>
                    <p style="text-align: center">{{index}}</p>
                </div>
                <el-table
                        :data="info"
                        style="width: 100%"
                        :cell-style="cellStyle"
                >
                    <el-table-column type="expand">
                        <template slot-scope="props">
                            <el-form
                                    label-position="left"
                                    inline class="demo-table-expand"
                            >
                                <el-form-item label="院校名称：">
                                    <span>{{ props.row.school_name }}</span>
                                </el-form-item>
                                <el-form-item label="院校代码：">
                                    <span>{{ props.row.school_num }}</span>
                                </el-form-item>
                                <el-form-item label="本科/专科：">
                                    <span>{{ props.row.school_nature }}</span>
                                </el-form-item>
                                <el-form-item label="公办/民办：">
                                    <span>{{ props.row.school_type }}</span>
                                </el-form-item>
                                <el-form-item label="所在省份：">
                                    <span>{{ props.row.school_province }}</span>
                                </el-form-item>
                                <el-form-item label="所在市：">
                                    <span>{{ props.row.school_city }}</span>
                                </el-form-item>
                            </el-form>
                            <el-table
                                    ref="multipleTable"
                                    :data="props.row.show_year"
                                    style="width: 100%"
                            >
                                <el-table-column prop="the_year" label="年份"></el-table-column>
                                <el-table-column prop="plan" label="计划"></el-table-column>
                                <el-table-column prop="admit" label="实际"></el-table-column>
                                <el-table-column prop="fraction_max" label="最高分"></el-table-column>
                                <el-table-column prop="fraction_min" label="最低分"></el-table-column>
                                <el-table-column prop="msd" label="最低分与分数线差值"></el-table-column>
                                <el-table-column prop="ler" label="录取最低分位次"></el-table-column>
                                <el-table-column prop="tas" label="平均分"></el-table-column>
                                <el-table-column prop="dbas" label="平均分与分数线差值"></el-table-column>
                            </el-table>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="院校名称"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="院校代码"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_num }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="公办/民办"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_type }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="省份"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_province }}</span>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </template>
    </div>
    <div v-show="show_select_data">
        <template>
            <div
                    :data="show_select_info"
                    style="width: 100%"
                    v-for="(info,index) in show_select_info"
            >
                <div>
                    <p style="text-align: center">{{index}}</p>
                </div>
                <el-table
                        :data="info"
                        style="width: 100%"
                        :cell-style="cellStyle"
                >
                    <el-table-column type="expand">
                        <template slot-scope="props">
                            <el-form
                                    label-position="left"
                                    inline class="demo-table-expand"
                            >
                                <el-form-item label="院校名称">
                                    <span>{{ props.row.school_name }}</span>
                                </el-form-item>
                                <el-form-item label="院校代码">
                                    <span>{{ props.row.school_num }}</span>
                                </el-form-item>
                                <el-form-item label="本科/专科">
                                    <span>{{ props.row.school_nature }}</span>
                                </el-form-item>
                                <el-form-item label="公办/民办">
                                    <span>{{ props.row.school_type }}</span>
                                </el-form-item>
                                <el-form-item label="所在省份">
                                    <span>{{ props.row.school_province }}</span>
                                </el-form-item>
                                <el-form-item label="所在市">
                                    <span>{{ props.row.school_city }}</span>
                                </el-form-item>
                            </el-form>
                            <el-table
                                    ref="multipleTable"
                                    :data="props.row.show_year"
                                    style="width: 100%"
                            >
                                <el-table-column prop="the_year" label="年份"></el-table-column>
                                <el-table-column prop="plan" label="计划"></el-table-column>
                                <el-table-column prop="admit" label="实际"></el-table-column>
                                <el-table-column prop="fraction_max" label="最高分"></el-table-column>
                                <el-table-column prop="fraction_min" label="最低分"></el-table-column>
                                <el-table-column prop="msd" label="最低分与分数线差值"></el-table-column>
                                <el-table-column prop="ler" label="录取最低分位次"></el-table-column>
                                <el-table-column prop="tas" label="平均分"></el-table-column>
                                <el-table-column prop="dbas" label="平均分与分数线差值"></el-table-column>
                            </el-table>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="院校名称"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="院校代码"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_num }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="公办/民办"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_type }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="省份"
                    >
                        <template slot-scope="props">
                            <span>{{ props.row.school_province }}</span>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </template>
    </div>
</div>
</body>
<script src="/assets/js/vue.js"></script>
<script src="/assets/js/index.js"></script>
<script src="/assets/js/jquery.js"></script>

<style>
    .demo-table-expand {
        font-size: 0;
    }
    .demo-table-expand label {
        color: #99a9bf;
    }
    .demo-table-expand .el-form-item {
        margin-right: 0;
        margin-left: 10px;
        margin-bottom: 0;
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
                    show_info: '',  //分数值
                    region:false,
                    profession_region:false
                },
                formRules: {
                    score: [
                        {required: true, message: '请输入分数', trigger: 'blur'},
                        {min: 1, max: 3, message: '请输入正确分数', trigger: 'blur'},
                        {pattern :'^[1-9]{1}[0-9]*$',message: '请输入正确的分数'},
                    ],
                    type: [
                        {required: true, },
                    ],

                },
                province_index:'',              //省份id值
                show_table: true,               //条件搜索的div，默认为true不改变
                show_data: true,               //条件搜索的div，默认为false普通搜索之后改为true
                show_select_data: false,        //附加条件搜索的div，默认为false筛选搜索之后改为true
                show_add:false,                 //附加条件筛选的div，默认为false普通搜索之后改为true
                show_warn:false,
                input_show:true,                //输入搜索条件的div，默认为true不改变
                info:[],                        //PHP页面传参的值（搜索的数据未进行省份处理）
                show_info:[],                   //普通搜索之后的用来展示的数据
                show_select_info:[],            //加筛选条件搜索之后的用来展示的数据
                profession_restaurants: [],     //专业下拉框展示数据
                school_restaurants: [],         //学校下拉框展示数据
                state: '',                      //专业下拉框输入的或者选中的数据
                state_school:'',                //学校下拉框输入的或者选中的数据
                sta_profession:[],              //input输入的专业值
                sta_school:[],                  //input输入的学校值
                province:'',                    //后台获取的所有省份值
                checked_province:[],            //获取选中的省份
                school_nature:[],               //后台传值（本科/专科）
                school_num:[],                  //后台传值（分数位次对应的院校代码）
                school_name:[],                  //后台传值（分数位次对应的院校代码）
                school_type:[],                 //后台传值（分数位次对应的学校类型）
                checked_school_type:[],         //后台传值（选中的分数位次对应的学校类型）
                cb:'',
            }
        },
        methods: {
            onSubmit() {
                var _this = this   //很重要！！
                if(this.sta_profession.length == 0 && this.sta_school.length == 0
                    && this.checked_school_type.length == 0 && this.checked_province.length == 0){
                    // 都为空
                    //查询当前输入的分数，批次，文理科，年份
                    $.post('/index/agent/get_ajax_info', {
                        'score': this.form.score ,
                        'batch': this.form.batch ,
                        'type':  this.form.type,
                        async: true,
                    }, function (response) {
                        if(response['code']==2){
                            _this.show_data=false;
                            _this.show_add=false;
                            _this.show_select_data=false;
                            alert(response['message']);return ;
                        }
                        if(response['code']==1){
                            _this.form.region = true;
                            _this.form.profession_region = false;
                            _this.info = response.info;
                            _this.province = response.province;
                            _this.school_type = response.school_type;
                            _this.school_num = response.school_num;
                            _this.school_name = response.school_name;
                            // console.log(_this.school_name)
                            _this.show_add=true;
                            _this.show_select_data=false;
                            _this.show_info = response.show_info;
                            console.log(typeof _this.show_info)
                            _this.show_data=true;
                            _this.school_nature = response.school_nature;
                            _this.show_warn = false;
                        }
                    });
                    return
                }else {
                    var sta_profession_name = '';
                    if(this.sta_profession['profession_name']) {
                        sta_profession_name=this.sta_profession['profession_name'];
                    }
                    var sta_school_num = '';
                    if(this.sta_school['school_num']) {
                        sta_school_num=this.sta_school;
                    }
                    var checked_province = '';
                    if(!this.checked_province.length == 0) {
                        checked_province = this.checked_province;
                    }
                    var checked_school_type = '';
                    if(!this.checked_school_type.length == 0) {
                        checked_school_type = this.checked_school_type;
                    }
                    $.post('/index/agent/get_select_info', {
                        'info': JSON.stringify(this.info),
                        'sta_profession': sta_profession_name,
                        'sta_school': sta_school_num,
                        'school_nature': this.school_nature,
                        'province': this.province,
                        'checked_province': checked_province,
                        'checked_school_type': checked_school_type,
                    }, function (response) {
                        if (response['code'] == 2) {
                            _this.show_data = true;
                            _this.show_select_data = true;
                            alert(response['message']);
                            return;
                        }
                        _this.form.profession_region = false;
                        _this.form.region = true;
                        _this.show_select_data = false;
                        _this.info = response.info;
                        _this.province = response.province;
                        _this.school_type = response.school_type;
                        _this.school_num = response.school_num;
                        if (_this.school_name) {
                            _this.school_name = response.school_name;
                        }
                        _this.show_add = true;
                        _this.show_data = false;
                        _this.show_select_info = response.show_info;
                        _this.show_select_data = true;
                        _this.show_warn = false;
                        console.log('in code = 1');
                        console.log((_this.info));
                        if(response['code']==3) {
                            _this.form.profession_region = true;
                            _this.show_warn = true;
                            _this.state = '';
                        }
                        return
                    });
                }
                return;
            },
            //根据color字段更改文字颜色
            cellStyle(row,column,rowIndex,columnIndex){
                if(row.row.color==='red'){
                    return 'color:red'
                }else if(row.row.color==='blue'){
                    return 'color:blue'
                }else if(row.row.color==='green'){
                    return 'color:green'
                }
            },
            //从后台获取专业数据
            load_profession_name(state='', cb) {
                var _this = this;
                var school_nature = _this.school_nature ? _this.school_nature : '';   //后台传值（本科/专科）
                var school_num = this.school_num ? this.school_num : '';            //后台传值（院校代码）
                $.post('/index/agent/get_profession_name', {
                    'school_nature':school_nature,
                    'school_num':school_num,
                    'word':state,
                    async: false,
                }, function (response) {
                    _this.profession_restaurants = response;
                    _this.onSubmit();
                    cb(response);
                    return
                });
            },
            //输入专业名称的搜索
            querySearchProfession(queryString, cb) {
                var _this = this;
                _this.cb=cb;
                var state = _this.state;
                if(!state) {
                    _this.sta_profession=[];
                }
                var profession_restaurants=_this.profession_restaurants
                for(i = 0,len=profession_restaurants.length; i < len; i++) {
                    if(profession_restaurants[i]['profession_name']==state)
                    {
                        _this.sta_profession['profession_name'] = state;
                        _this.sta_profession.length=1;
                        _this.load_profession_name(state, cb);return
                    }
                }
                _this.load_profession_name(state, cb);return
            },
            //从后台获取学校数据
            load_school_name(state_school=null, cb) {
                var checked_province = '';
                if(!this.checked_province.length == 0) {
                    checked_province = this.checked_province;
                }
                var checked_school_type = '';
                if(!this.checked_school_type.length == 0) {
                    checked_school_type = this.checked_school_type;
                }
                var _this = this;
                var school_num = this.school_num ? this.school_num : '';                    //后台传值（分数位次对应的院校代码）
                if(state_school){
                    $.post('/index/agent/get_select_school_name', {
                        'info': JSON.stringify(this.info),
                        'checked_province': checked_province,
                        'checked_school_type': checked_school_type,
                        'school_num':school_num,
                        'word':state_school,
                        'score': this.form.score ,
                        'batch': this.form.batch ,
                        'type':  this.form.type,
                        async: false,
                    }, function (response) {
                        _this.school_restaurants = response;
                        cb(response);
                        return;
                    });
                }else {
                    cb(_this.school_name);
                }
            },
            //输入学校名称的搜索
            querySearchSchool(queryString, cb) {
                var school_restaurants = this.school_restaurants;
                var _this = this;
                _this.cb=cb;
                var state_school = _this.state_school;
                if(!state_school) {
                    _this.sta_school=[];
                    _this.onSubmit();
                }
                for(i = 0,len=school_restaurants.length; i < len; i++) {
                    if(school_restaurants[i]['school_name']==state_school)
                    {
                        _this.sta_school['school_num'] = school_restaurants[i]['school_num'];
                        _this.sta_school['school_name'] = school_restaurants[i]['school_name'];
                        _this.sta_school.length=1;
                        _this.onSubmit();
                        _this.load_school_name(state_school, cb);return
                    }
                }
                setTimeout(function () {
                    _this.load_school_name(state_school, cb);return
                },500)
            },
            //选中的专业名称赋值
            handleSelectProfession(item) {
                this.sta_profession = item;
                this.onSubmit();
                console.log(item);
            },
            //选中的学校名称赋值
            handleSelectSchool(item) {
                this.sta_school = item;
                this.onSubmit();
                console.log(item);
            },
            //省份选中，查看传参
            province_name (){
                console.log(this.checked_province);return
            },
            //更改公民办默认选中值
            type_primary(val){
                var _this = this;
                var pp_type=_this.pp_type;
                if(val==pp_type){
                    return "primary";
                }
            },
            //更改省份默认选中值
            province_primary(val){
                var _this = this;
                var province=_this.province;
                if(val==province){
                    return "primary";
                }
            },
        },
    })
</script>
</html>



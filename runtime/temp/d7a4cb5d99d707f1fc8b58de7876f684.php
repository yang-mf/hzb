<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:76:"E:\phpstudy_pro\WWW\fw366.cn\public/../application/index\view\test\test.html";i:1608775664;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/assets/css/element.css">
    <title>Test</title>
</head>
<body>
<div id="app">
    <div v-show="show_table">
    <el-form ref="form"
             :model="form"
             label-width="100px"
             :rules="formRules"
    >
        <div
                v-show="input_show"
        >
            <el-row>
                <el-col :span="3">
                    <el-form-item label="分数"  prop="score">
                        <el-input
                                v-model="form.score"
                                placeholder="请输入分数"
                                :disabled=form.region
                        ></el-input>
                    </el-form-item>
                </el-col>
<!--                <el-col :span="3">-->
<!--                    <el-form-item label="位次"  prop="rank" >-->
<!--                        <el-input-->
<!--                                v-model="form.rank"-->
<!--                                placeholder="请输入位次"-->
<!--                                :disabled=form.region-->
<!--                        ></el-input>-->
<!--                    </el-form-item>-->
<!--                </el-col>-->
                <el-col :span="3">
                    <el-form-item label="年份">
                        <el-select v-model="form.year" placeholder="请选择年份" :disabled=form.region>
                            <el-option label="2017" value="2017"></el-option>
                            <el-option label="2018" value="2018"></el-option>
                            <el-option label="2019" value="2019"></el-option>
                            <el-option label="2020" value="2020"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="3">
                    <el-form-item label="文科/理科" prop="type">
                        <el-select v-model="form.type" placeholder="请选择文科/理科" :disabled=form.region>
                            <el-option label="理科" value="reason"></el-option>
                            <el-option label="文科" value="culture"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item  label="批次">
                        <el-select
                            v-model="form.batch"
                            multiple
                            placeholder="请选择批次"
                            @click.native="getBatchInfo()"
                            >
                            <el-option
                                    v-for ="item in all_batch_name_info"
                                    :key  ="item.batch_value"
                                    :label="item.batch_name"
                                    :value="item.batch_value"
                            >
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
        </div>
        <div v-show="show_add">
            <el-row>
            <el-col :span="4">
                <el-form-item label="专业" >
                    <el-select
                            :debounce="0"
                            value-key="profession_name"
                            placeholder="请选择专业（默认本科专业）"
                            @select="handleSelectProfession"
                            v-model="state"
                            @change="querySearchProfession"
                            :disabled=form.profession_region
                            multiple
                            filterable
                            @click.native="getProfessionInfo()"
                            @blur.native.capture="ProfessionInfo()"
                    >
                        <el-option
                                v-for ="item in all_profession_name_info"
                                :key  ="item.id"
                                :label="item.profession_name"
                                :value="item.profession_name"
                        >
                        </el-option>
                    </el-select>
                    <!--
                    <el-autocomplete
                            :debounce="0"
                            v-model="state"
                            :fetch-suggestions="querySearchProfession"
                            value-key="profession_name"
                            placeholder="请输入专业（默认本科专业）"
                            @select="handleSelectProfession"
                            :disabled=form.profession_region
                    ></el-autocomplete>
                    -->
                </el-form-item>
            </el-col>
            <el-col :span="8">
                <el-form-item label="院校名称"
                >

                    <el-select
                            style="width: 100%"
                            v-model="state_school"
                            multiple
                            filterable
                            placeholder="请选择院校名称"
                            @click.native="getSchoolNameInfo()"
                            @blur.native.capture="SchoolNameInfo()"

                    >
                        <el-option
                                v-for ="item in all_school_name_info"
                                :key  ="item.school_num"
                                :label="item.school_name"
                                :value="item.school_num+item.school_name"
                        >
                        </el-option>
                    </el-select>
               <!--
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
-->
                <el-alert
                        v-show="show_warn"
                        title="请以招生简介为准"
                        type="warning"
                        show-icon>
                </el-alert>
            </el-form-item>
            </el-col>
            <el-col :span="4">
                <el-form-item label="办学类型" >
                <el-select
                        v-model="checked_school_type"
                        multiple
                        placeholder="请选择办学类型"
                        @click.native="getSchoolTypeInfo()"
                        @blur.native.capture="SchoolTypeInfo()"

                >
                    <el-option
                            v-for ="(item,index) in all_school_type_name_info"
                            :key  ="item.school_type_num"
                            :value="item.school_type_name"
                            :label="item.school_type_name"
                    >
                    </el-option>
                </el-select>
                <!--
                <template>
                    <el-checkbox-group
                            v-model="checked_school_type"
                    >
                        <el-checkbox
                                v-for="item in school_type"
                                :label="item"
                        >{{item}}
                        </el-checkbox>
                    </el-checkbox-group>

                </template>
                -->
            </el-form-item>
            </el-col>
            <el-col :span="4">
                <el-form-item label="省份"  >
                <el-select
                        v-model="checked_province"
                        multiple
                        filterable
                        placeholder="请选择省份"
                        @click.native="getProvinceInfo()"
                        @blur.native.capture="ProvinceInfo()"

                >
                    <el-option
                            v-for ="item in all_province_name_info"
                            :key  ="item.id"
                            :label="item.school_province"
                            :value="item.school_province"
                    >
                    </el-option>
                </el-select>
                <!--
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
                -->
            </el-form-item>
            </el-col>
            </el-row>
            <el-form-item>
                <div v-show="switch_show_more">
                    <el-switch
                            v-model="show_more"
                            inactive-text="显示当年信息"
                            @change="show_more_year"
                    >
                    </el-switch>
                </div>

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
                    batch: [],      //批次值
                    type: '',       //文科理科值
                    year: '',       //年份值
                    rank: '',       //位次
                    the_show_year: '',          //年份值（swich开关单独使用
                    show_info: '',              //
                    region:false,               //是否可用
                    profession_region:false     //是否可用（专业单独使用
                },
                linkage:false,                  //开关用于是否对筛选的
                show_more:false,                //switch开关用于是否显示当年的数据
                switch_show_more:false,
                //输入信息的js验证
                formRules: {
                    score: [
                        {required: true, message: '请输入分数', trigger: 'blur'},
                        {min: 1, max: 6, message: '请输入正确分数', trigger: 'blur'},
                        {pattern :/^(([^0][0-9]\d{0,2}|0)\.([0-9]{1,1})$)|^([^0][0-9]+|0)$/,message: '请输入正确的分数'},
                    ],
                    rank: [
                        {required: true, message: '请输入位次', trigger: 'blur'},
                        {min: 1, max: 6, message: '请输入正确位次', trigger: 'blur'},
                        {pattern :/^[1-9]+$/,message: '请输入正确的位次'},
                    ],
                    type: [
                        {required: true, },
                    ],
                    year: [
                        {required: true, },
                    ],
                },
                province_index:'',              //省份id值
                show_table: true,               //条件搜索的div，默认为true不改变
                show_data: true,                //条件搜索的div，默认为false普通搜索之后改为true
                show_select_data: false,        //附加条件搜索的div，默认为false筛选搜索之后改为true
                show_add:true,                 //附加条件筛选的div，默认为false普通搜索之后改为true
                show_warn:false,                //当选择的学校名称中带有（）的将专业设为禁止选择和为空，在搜索的学校下方弹出提示语句
                input_show:true,                //输入搜索条件的div，默认为true不改变
                info:[],                        //PHP页面传参的值（搜索的数据未进行省份处理）
                show_info:[],                   //普通搜索之后的用来展示的数据
                show_select_info:[],            //加筛选条件搜索之后的用来展示的数据
                profession_restaurants: [],     //专业下拉框展示数据
                school_restaurants: [],         //学校下拉框展示数据
                state: [],                      //专业下拉框输入的或者选中的数据
                state_school:[],                //学校下拉框输入的或者选中的数据
                sta_profession:[],              //input输入的专业值
                sta_school:[],                  //input输入的学校值
                province:'',                    //后台获取的所有省份值
                checked_province:[],            //获取选中的省份
                school_nature:[],               //后台传值（本科/专科）
                school_num:[],                  //后台传值（分数位次对应的院校代码）
                school_name:[],                 //后台传值（分数位次对应的院校代码）
                school_type:[],                 //后台传值（分数位次对应的学校类型）
                checked_school_type:[],         //后台传值（选中的分数位次对应的学校类型）
                cb:'',                          //element UI 自定义的渲染方法，此处定义为方便全文使用
                all_batch_name_info:JSON.parse('<?php echo $all_batch_name_info; ?>'),                  //批次名称，后台获取
                all_profession_name_info:JSON.parse('<?php echo $all_profession_name_info; ?>'),                  //批次名称，后台获取
                all_province_name_info:JSON.parse('<?php echo $all_province_name_info; ?>'),                  //批次名称，后台获取
                all_school_type_name_info:JSON.parse('<?php echo $all_school_type_name_info; ?>'),                  //批次名称，后台获取
                all_school_name_info:JSON.parse('<?php echo $all_school_name_info; ?>'),                  //批次名称，后台获取
                getBatchInfoData:[],
            }
        },
        methods: {
            onSubmit() {
                var _this = this   //很重要！！
                var reg =/^(([^0][0-9]\d{0,2}|0)\.([0-9]{1,1})$)|^([^0][0-9]+|0)$/
                var n = reg.test(_this.form.score)
                if(!n){
                    alert('请输入正确的分数');return;
                }
                // console.log(this.form.batch);
                // console.log(this.state_school);
                // console.log(this.state);
                // console.log(this.checked_school_type);
                // console.log(this.checked_province);
                // return;
                if(!this.linkage){
                    if(_this.form.batch.length ==0){
                        _this.form.batch='';
                    }
                    var state = '';
                    if(!_this.state.length ==0) {
                        state=this.state;
                    }
                    var state_school = '';
                    if(!_this.state_school.length ==0) {
                        state_school=this.state_school;
                    }
                    var checked_province = '';
                    if(!this.checked_province.length == 0) {
                        checked_province = this.checked_province;
                    }
                    var checked_school_type = '';
                    if(!this.checked_school_type.length == 0) {
                        checked_school_type = this.checked_school_type;
                    }
                    // 都为空
                    //查询当前输入的分数，批次，文理科，年份
                    $.post('/index/batch/get_ajax_info', {
                        'score': this.form.score ,
                        'batch': this.form.batch ,
                        'type' :  this.form.type,
                        'year' :  this.form.year,
                        'rank' :  this.form.rank,
                        'the_show_year' :this.form.the_show_year,
                        'sta_profession': state,
                        'sta_school': state_school,
                        'checked_province': checked_province,
                        'checked_school_type': checked_school_type,
                        async: true,
                    }, function (response) {
                        if(response['code']==2){
                            _this.show_data=false;
                            _this.show_add=true;
                            _this.show_select_data=false;
                            alert(response['message']);return ;
                        }
                        if(response['code']==1){
                            _this.switch_show_more = true;
                            _this.form.region = true;
                            _this.form.profession_region = false;
                            _this.info = response.info;
                            _this.all_profession_name_info = response.profession_name;
                            _this.all_province_name_info = response.province;
                            _this.all_school_type_name_info = response.school_type;
                            _this.school_num = response.school_num;
                            _this.all_school_name_info = response.school_name;
                            // console.log(_this.school_name)
                            _this.show_add=true;
                            _this.show_select_data=false;
                            _this.show_info = response.show_info;
                            console.log( _this.info)
                            _this.show_data=true;
                            _this.school_nature = response.school_nature;
                            _this.show_warn = false;
                            _this.linkage = true;
                        }
                    });
                    return
                }else {
                    var state = '';
                    if(!_this.state.length ==0) {
                        state=this.state;
                    }
                    var state_school = '';
                    if(!_this.state_school.length ==0) {
                        state_school=this.state_school;
                    }
                    var checked_province = '';
                    if(!this.checked_province.length == 0) {
                        checked_province = this.checked_province;
                    }
                    var checked_school_type = '';
                    if(!this.checked_school_type.length == 0) {
                        checked_school_type = this.checked_school_type;
                    }
                    $.post('/index/test/get_select_info', {
                        'info': JSON.stringify(this.info),
                        'sta_profession': state,
                        'sta_school': state_school,
                        'school_nature': this.school_nature,
                        'checked_province': checked_province,
                        'checked_school_type': checked_school_type,
                        'the_show_year':this.form.the_show_year,
                        'batch': this.form.batch ,
                        'type':  this.form.type,
                        'year':  this.form.year,
                        'score': this.form.score ,
                        'rank':  this.form.rank,
                    }, function (response) {
                        if (response['code'] == 2) {
                            _this.show_data = true;
                            _this.show_select_data = true;
                            alert(response['message']);
                            return;
                        }
                        _this.form.profession_region = false;
                        _this.form.region = true;
                        _this.info = response.info;
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
                $.post('/index/test/get_profession_name', {
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
                console.log(111111111111111);return;
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
                    $.post('/index/test/get_select_school_name', {
                        'info': JSON.stringify(this.info),
                        'checked_province': checked_province,
                        'checked_school_type': checked_school_type,
                        'school_num':school_num,
                        'word':state_school,
                        'score': this.form.score ,
                        'batch': this.form.batch ,
                        'type':  this.form.type,
                        'year':  this.form.year,
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
                console.log(2222222222222);return;

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
            //switch开关，显示当前选择的年份显示与否
            show_more_year(val){
                var _this = this;
                var show_more=_this.show_more;
                var year = _this.form.year;
                if( show_more ) {
                    _this.form.the_show_year = year;
                }else {
                    _this.form.the_show_year = '';
                }
                console.log(_this.form.the_show_year)
                _this.onSubmit();
            },
            //获取基础显示数据，批次，
            getBatchInfo( ) {
                var _this = this;
                if( !_this.form.score && !_this.form.type && !_this.form.year ) {
                    return ;
                }
                _this.form.batch=[];
                $.post('/index/test/check_batch', {
                    'score': _this.form.score ,
                    'type' : _this.form.type,
                    'year' : _this.form.year,
                }, function (response) {
                    if( response.batch_code==9 ) {
                        alert(response.message);
                        _this.all_batch_name_info =[];
                        return
                    }
                    if( response.batch_code==8 ) {
                        alert(response.message);
                        _this.all_batch_name_info =[];
                        return
                    }
                    _this.all_batch_name_info = response.batch_data;
                    console.log(response)
                });
            },
            getProfessionInfo() {
                console.log('1111111111111ProfessionInfo');
            },
            getSchoolNameInfo() {
                console.log('1111111111111SchoolName');
            },
            getSchoolTypeInfo() {
                console.log('1111111111111SchoolTypeInfo');
            },
            getProvinceInfo() {
                console.log('1111111111111ProvinceInfo');
            },
            ProfessionInfo() {
                console.log('22222222222222222222ProfessionInfo');
            },
            SchoolNameInfo() {
                console.log('22222222222222222222SchoolNameInfo');
            },
            SchoolTypeInfo() {
                console.log('22222222222222222222SchoolTypeInfo');
            },
            ProvinceInfo() {
                console.log('22222222222222222222ProvinceInfo');
            },
        },
        mounted() {
        }
    })
</script>
</html>



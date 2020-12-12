<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"E:\phpstudy_pro\WWW\fw366.cn\public/../application/index\view\test\agent.html";i:1607765056;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/assets/css/index.css">
    <title>Agent</title>
</head>
<body>
<div id="app">
    <div v-show="show_table">
        <el-form ref="form"
                 :model="form"
                 label-width="80px"
                 :rules="formRules"
        >
            <div v-show="input_show">
                <el-form-item label="分数" style="width: 20%" prop="score">
                    <el-input v-model="form.score" placeholder="请输入分数" :disabled=form.region ></el-input>
                    <!--                <el-input v-model="ruleForm.score"></el-input>-->
                </el-form-item>
                <el-form-item label="批次">
                    <el-select v-model="form.batch" placeholder="请选择批次" :disabled=form.region>
                        <el-option label="一批" value="1"></el-option>
                        <el-option label="二批" value="2"></el-option>
                        <el-option label="三批" value="3"></el-option>
                        <el-option label="大专" value="4"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="文科/理科">
                    <el-select v-model="form.type" placeholder="请选择文科/理科" :disabled=form.region>
                        <el-option label="理科" value="reason"></el-option>
                        <el-option label="文科" value="culture"></el-option>
                    </el-select>
                </el-form-item>
            </div>
            <div v-show="show_add">
                <el-form-item label="专业" style="width: 40%">
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
                <el-form-item label="院校名称" style="width: 40%">
                    <el-autocomplete
                            v-model="state_school"
                            :fetch-suggestions="querySearchSchool"
                            value-key="school_name"
                            placeholder="请输入院校名称"
                            @select="handleSelectSchool"
                    ></el-autocomplete>
                </el-form-item>
                <el-form-item label="办学类型" style="width: 50%">
                    <el-button @click.native="school_type($event)" id="type1" :type="type_primary('type1')">公办</el-button>
                    <el-button @click.native="school_type($event)" id="type2" :type="type_primary('type2')">民办</el-button>
                    <el-button @click.native="school_type($event)" id="type3" :type="type_primary('type3')">内地与港澳台地区合作办学</el-button>
                    <el-button @click.native="school_type($event)" id="type4" :type="type_primary('type4')">中外合作办学</el-button>
                </el-form-item>
                <el-form-item label="省份" style="width: 55%">
                    <template>
                        <el-checkbox-group
                                v-model="test"
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
                    v-for="(info,index) in show_info"
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
                                <el-form-item label="省份大学数量">
                                    <span>{{ props.row.province_school_number }}</span>
                                </el-form-item>
                            </el-form>
                            <el-form
                                    label-position="left"
                                    inline class="demo-table-expand"
                                    v-for="the_year_info in props.row.show_year "
                            >
                                <el-form-item label="年份">
                                    <span>{{ the_year_info.the_year }}</span>
                                </el-form-item>
                                <el-form-item label="计划招收">
                                    <span>{{ the_year_info.plan }}</span>
                                </el-form-item>
                                <el-form-item label="实际招收">
                                    <span>{{ the_year_info.admit }}</span>
                                </el-form-item>
                                <el-form-item label="最高分">
                                    <span>{{ the_year_info.fraction_max }}</span>
                                </el-form-item>
                                <el-form-item label="最低分">
                                    <span>{{ the_year_info.fraction_min }}</span>
                                </el-form-item>
                                <el-form-item label="最低分与分数线差值">
                                    <span>{{ the_year_info.msd }}</span>
                                </el-form-item>
                                <el-form-item label="录取最低分位次">
                                    <span>{{ the_year_info.ler }}</span>
                                </el-form-item>
                                <el-form-item label="平均分">
                                    <span>{{ the_year_info.tas }}</span>
                                </el-form-item>
                                <el-form-item label="平均分与分数线差值">
                                    <span>{{ the_year_info.dbas }}</span>
                                </el-form-item>
                            </el-form>
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
                                <el-form-item label="省份大学数量">
                                    <span>{{ props.row.province_school_number }}</span>
                                </el-form-item>
                            </el-form>
                            <el-form
                                    label-position="left"
                                    inline class="demo-table-expand"
                                    v-for="the_year_info in props.row.show_year "
                            >
                                <el-form-item label="年份">
                                    <span>{{ the_year_info.the_year }}</span>
                                </el-form-item>
                                <el-form-item label="计划招收">
                                    <span>{{ the_year_info.plan }}</span>
                                </el-form-item>
                                <el-form-item label="实际招收">
                                    <span>{{ the_year_info.admit }}</span>
                                </el-form-item>
                                <el-form-item label="最高分">
                                    <span>{{ the_year_info.fraction_max }}</span>
                                </el-form-item>
                                <el-form-item label="最低分">
                                    <span>{{ the_year_info.fraction_min }}</span>
                                </el-form-item>
                                <el-form-item label="最低分与分数线差值">
                                    <span>{{ the_year_info.msd }}</span>
                                </el-form-item>
                                <el-form-item label="录取最低分位次">
                                    <span>{{ the_year_info.ler }}</span>
                                </el-form-item>
                                <el-form-item label="平均分">
                                    <span>{{ the_year_info.tas }}</span>
                                </el-form-item>
                                <el-form-item label="平均分与分数线差值">
                                    <span>{{ the_year_info.dbas }}</span>
                                </el-form-item>
                            </el-form>
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
<script src="/jquery-3.5.1.min.js"></script>
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
                    region:false
                },
                formRules: {
                    score: [
                        {required: true, message: '请输入分数', trigger: 'blur'},
                        {min: 3, max: 3, message: '长度为 3 个字符', trigger: 'blur'},
                        {pattern :'^[1-9]{1}[0-9]{2}$',message: '请输入正确的分数'},
                    ],
                },
                province_index:'',              //省份id值
                checkList: '',
                show_table: true,               //条件搜索的div，默认为true不改变
                show_data: true,               //条件搜索的div，默认为false普通搜索之后改为true
                show_select_data: false,        //附加条件搜索的div，默认为false筛选搜索之后改为true
                show_add:false,                 //附加条件筛选的div，默认为false普通搜索之后改为true
                input_show:true,                //输入搜索条件的div，默认为true不改变
                info:[],                        //PHP页面传参的值（搜索的数据未进行省份处理）
                show_info:[],                   //普通搜索之后的用来展示的数据
                show_select_info:[],            //加筛选条件搜索之后的用来展示的数据
                profession_restaurants: [],     //专业下拉框展示数据
                school_restaurants: [],         //学校下拉框展示数据
                state: '',                      //专业下拉框输入的或者选中的数据
                state_school:'',                //学校下拉框输入的或者选中的数据
                timeout:  null,
                sta_profession:'',              //input输入的专业值
                sta_school:'',                  //input输入的学校值
                profession:'',                  //专业（本科/专科）值
                pp_type:'',                     //公办民办值
                province:'',                    //后台获取的所有省份值
                test:[],                        //获取选中的省份
            }
        },
        methods: {
            onSubmit() {
                var score = this.form.score;                //分数值
                var batch = this.form.batch;                //批次值
                if(batch=='')
                {
                    alert('请选择批次');return ;
                }
                var type = this.form.type;                  //文科理科值
                if(type=='')
                {
                    alert('请选择文理科');return ;
                }
                var year = this.form.year;                  //年份值
                var sta_profession = this.sta_profession.profession_name
                    ? this.sta_profession.profession_name : '';                                     //input输入的专业值
                var sta_school = this.sta_school.school_name ? this.sta_school.school_name : '';    //input输入的学校值
                var profession = this.profession ? this.profession : 'profession1';                  //专业（本科/专科）值
                var pp_type = this.pp_type ? this.pp_type : '';                                      //公办民办值
                var province = this.province ? this.province : '';                                   //省份值
                var info = this.info ? this.info : '';                                               //后续判断筛选的传参值
                // var show_info = this.show_info ? this.show_info: '';                              //
                var test = this.test  ?  this.test :  '';                                            //后续判断筛选的传参值
                if(test.length == 0){
                    var test='';                        //空值
                }
                var _this = this   //很重要！！
                if(sta_profession=='' && sta_school==''
                    && pp_type=='' && test=='' ){
                    //查询当前输入的分数，批次，文理科，年份
                    $.post('/index/agent/get_ajax_info', {
                        'score':score ,
                        'batch':batch ,
                        'type':type,
                    }, function (response) {
                        //默认为false，有值改为true
                        if(response['code']==1){
                            _this.form.region = true;
                            _this.info = response.info;
                            _this.province = response.province;
                            _this.province_select_name='';
                            _this.show_data=true;
                            _this.show_add=true;
                            _this.show_select_data=false;
                            _this.show_info = response.show_info;
                            console.log(_this.info);
                        }else if(response['code']==2){
                            _this.show_data=false;
                            _this.show_add=false;
                            _this.show_select_data=false;
                            alert(response['message']);return ;
                        }
                    });
                    return
                }
                // console.log(info);return
                $.post('/index/agent/get_select_info', {
                    'show_info':JSON.stringify(info),
                    'sta_profession':sta_profession,
                    'sta_school':sta_school,
                    'profession':profession,
                    'pp_type':pp_type,
                    'province':province,
                    'test':test,
                }, function (response) {
                    if(response['code']==2){
                        _this.show_data=false;
                        _this.show_select_data=false;
                        alert(response['message']);return ;
                    }
                    if(response['code']==1){
                        _this.form.region = true;
                        _this.info = response.info;
                        _this.province = response.province;
                        _this.show_add=true;
                        _this.show_data=false;
                        _this.show_select_data=true;
                        _this.show_select_info = response.show_info;
                        console.log('in code = 1');
                        console.log(_this.info);
                    }
                });
                // return;
            },
            cellStyle(row,column,rowIndex,columnIndex){
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
            //省份
            province_name (){
                console.log(this.test);return
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



<?php

namespace app\index\controller;

use app\index\model\TestHzbDataSelectBatch;
use think\Controller;
use think\Request;
use app\common\controller\Frontend;
use app\common\model\Config;

class Test extends Frontend
{
    /**
     * 测试模块
     *
     * @return \think\Response
     */
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }
    //首页展示
    public function test()
    {
        return $this->view->fetch('test/test');
    }
    //获取学生位次信息
    /*
     * $score  分数
     * $status 状态
     * $year   年份，测试版有，正式版直接在model中获取今年
     * $type   文理科
     * $batch  批次
     * $result 获取用来展示的数据
     */
    public function get_info()
    {
        //分数
        $score = $this->request->request('score');
        //状态
        $status = $this->request->request('status');
        //年份，测试版有，正式版直接在model中获取今年
        $year = $this->request->request('year');
        //文理科
        $type = $this->request->request('type');
        //批次
        $batch = $this->request->request('batch');
        if(empty($score) && empty($type) ){
            $score = session('score');
            $year = session('year');
            $type = session('type');
            $batch = session('batch');
            $status = session('status');
        }else{
            session('score',$score);
            session('status',$status);
            session('year',$year);
            session('type',$type);
            session('batch',$batch);
        }
        //获取数据
        if(empty($year)){
            $year = date('Y');
        }
        $result = model('TestHzbDataBatch')->getBatchData($score,$status,$type,$year,$batch);
        $province = model('TestHzbDataBatch')->getBatchProvince($result['info']);

        $this->assign('info',$result['info']);
        return $this->view->fetch('test/testshow');
    }
    public function get_ajax_info()
    {
        $score =$_POST['score'];
        $batch =$_POST['batch'];
        $type =$_POST['type'];
        $year =$_POST['year'];
        if(!($score)){
            return $data=['code'=>2,'message'=>'请输入正确的分数'];
        }
        if(!($batch)){
            return $data=['code'=>2,'message'=>'请输入选择批次'];
        }
        if(!($type)){
            return $data=['code'=>2,'message'=>'请输入选择文理科'];
        }
        if(empty($score) && empty($type) ){
            $score = session('score');
            $year = session('year');
            $type = session('type');
            $batch = session('batch');
        }else{
            session('score',$score);
            session('year',$year);
            session('type',$type);
            session('batch',$batch);
        }
        //获取数据
        if(!empty($year)){
            $year = date($year);
            $result = model('TestHzbDataBatch')->getBatchData($score,$type,$year,$batch);
        }else{
            $year = date('Y');
            $result = model('TestHzbDataBatchYear')->getBatchData($score,$type,$year,$batch);
        }
        $data = $this->groupByInitials($result['info'], 'school_province');
//        var_dump($data);die;
        $data = ['code'=>1,'info'=>$data,];
        return $data;
    }
    //附加条件搜索
    public function get_select_info(){
        $show_info =$_POST['show_info'];
        $show_info = json_decode($show_info);
        $show_info = json_decode( json_encode( $show_info),true);
        $sta_profession =$_POST['sta_profession'];
        $sta_school =$_POST['sta_school'];
        $profession =$_POST['profession'];
        $pp_type =$_POST['pp_type'];
        if (!empty($sta_profession)) {
            $show_info = model('TestHzbDataSelectBatch')->check_sta_profession($show_info,$sta_profession,$profession);
        }
        //学校名称搜索
        if (!empty($sta_school)) {
            $show_info = model('TestHzbDataSelectBatch')->check_sta_school($show_info,$sta_school);
        }
        //办学类型搜索
        if (!empty($pp_type)) {
            $show_info = model('TestHzbDataSelectBatch')->check_pp_type($show_info,$pp_type);

        }
        if(empty($show_info))
        {
            $show_info=['code'=>2,'message'=>'请重新选择'];
        }else
        {
            $show_info=['code'=>1,'info'=>$show_info];
        }
        return $show_info;
    }

    //获取部分profession_name数据
    public function get_profession_name()
    {
        $profession=$_POST['profession'];
        $result = model('TestHzbDataCategory')->getProfessionData($profession);
        return $result;
    }
    //根据客户输入的关键字查询获取全部profession_name数据
    public function get_select_profession_name()
    {
        $profession=$_POST['profession'];
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')->getProfessionSelectData($word,$profession);
        return $result;
    }
    //获取部分school_name数据
    public function get_school_name()
    {
        $result = model('TestHzbDataCategory')->getSchoolNameData();
        return $result;
    }
    //根据客户输入的关键字查询获取全部school_name数据
    public function get_select_school_name()
    {
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')->getSchoolNameSelectData($word);
        return $result;
    }

    //省份排序
    public function province()
    {
        $province=array(
            [ "school_province"=> "河北省"],
            [ "school_province"=> "福建省"],
            [ "school_province"=> "新疆维吾尔自治区"],
            [ "school_province"=> "湖北省"],
            [ "school_province"=> "辽宁省"],
            [ "school_province"=> "吉林省"],
        );
//        var_dump($province);die;
        $province = $this->initialsProvince($province);

    }
    /**
     * 二维数组根据首字母分组排序
     * @param  array  $data      二维数组
     * @param  string $targetKey 首字母的键名
     * @return array             根据首字母关联的二维数组
     */
    public function groupByInitials(array $data, $targetKey = 'name')
    {
        $data = array_map(function ($item) use ($targetKey) {
            return array_merge($item, [
                'initials' => $this->getInitials($item[$targetKey]),
            ]);
        }, $data);
        $data = $this->sortInitials($data);
        return $data;
    }

    /**
     * 按字母排序
     * @param  array  $data
     * @return array
     */
    public function sortInitials(array $data)
    {
        $sortData = [];
        foreach ($data as $key => $value) {
            $sortData[$value['initials']][] = $value;
        }
        ksort($sortData);
        return $sortData;
    }

    /**
     * 获取首字母
     * @param  string $str 汉字字符串
     * @return string 首字母
     */
    public function getInitials($str)
    {
        if (empty($str)) {return '';}
        $fchar = ord($str{0});

        if ($fchar >= ord('A') && $fchar <= ord('z')) {
            return strtoupper($str{0});
        }

        $s1  = iconv('UTF-8', 'gb2312', $str);
        $s2  = iconv('gb2312', 'UTF-8', $s1);
        $s   = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) {
            return 'A';
        }

        if ($asc >= -20283 && $asc <= -19776) {
            return 'B';
        }

        if ($asc >= -19775 && $asc <= -19219) {
            return 'C';
        }

        if ($asc >= -19218 && $asc <= -18711) {
            return 'D';
        }

        if ($asc >= -18710 && $asc <= -18527) {
            return 'E';
        }

        if ($asc >= -18526 && $asc <= -18240) {
            return 'F';
        }

        if ($asc >= -18239 && $asc <= -17923) {
            return 'G';
        }

        if ($asc >= -17922 && $asc <= -17418) {
            return 'H';
        }

        if ($asc >= -17417 && $asc <= -16475) {
            return 'J';
        }

        if ($asc >= -16474 && $asc <= -16213) {
            return 'K';
        }

        if ($asc >= -16212 && $asc <= -15641) {
            return 'L';
        }

        if ($asc >= -15640 && $asc <= -15166) {
            return 'M';
        }

        if ($asc >= -15165 && $asc <= -14923) {
            return 'N';
        }

        if ($asc >= -14922 && $asc <= -14915) {
            return 'O';
        }

        if ($asc >= -14914 && $asc <= -14631) {
            return 'P';
        }

        if ($asc >= -14630 && $asc <= -14150) {
            return 'Q';
        }

        if ($asc >= -14149 && $asc <= -14091) {
            return 'R';
        }

        if ($asc >= -14090 && $asc <= -13319) {
            return 'S';
        }

        if ($asc >= -13318 && $asc <= -12839) {
            return 'T';
        }

        if ($asc >= -12838 && $asc <= -12557) {
            return 'W';
        }

        if ($asc >= -12556 && $asc <= -11848) {
            return 'X';
        }

        if ($asc >= -11847 && $asc <= -11056) {
            return 'Y';
        }

        if ($asc >= -11055 && $asc <= -10247) {
            return 'Z';
        }

        return null;
    }
    public function initialsProvince($province){
        // 按首字母排序
//        $cityName = Db::query("select `id`,`title`,
//`domain`,`pinyin` from `agent`
//where `status`=1 order by convert(`title` using gb2312) asc");

        $data = $this->groupByInitials($province, 'school_province');
        dump($data);
    }

    public function tst()
    {
        $arr=[];
        /*
        $arr =[
            ["G"]=> [
                ['0']=> [
                    ["id"] => ['355'],
                    ["school_num"=> "4815"],
                    ["school_name"=> "东莞理工学院"],
                    ["the_year"=> 2016],
                    ["plan"=> 29],
                    ["admit"=> 29] ,
                    ["fraction_max"=> 574] ,
                    ["fraction_min"=> 523] ,
                    ["msd"=>0] ,
                    ["ler"=> 70854 ],
                    ["tas"=>  "537.6"] ,
                    ["dbas"=>  "14.6" ],
                    ["batch"=> 1 ],
                    ["type"=>  "reason"] ,
                    ["color"=>  "red"] ,
                    ["school_type"=> "公办" ],
                    ["school_management"=> "广东省" ],
                    ["school_province"=>  "广东省" ],
                    ["school_city"=>  "东莞市" ],
                    ["school_nature"=>  "本科" ],
                    ["province_school_number"=>  "154所" ],
                    ["school_renown"=> 2 ],
                    ["school_independent"=> NULL ],
                    ["show_year"=> [
                        ['2016'=> [
                            ["the_year"=> 2016 ],
                            ["plan"=> 29 ],
                            ["admit"=> 29 ],
                            ["fraction_max"=> 574 ],
                            ["fraction_min"=> 523 ],
                            ["msd"=> 0 ],
                            ["ler"=> 70854 ],
                            ["tas"=>  "537.6" ],
                            ["dbas"=>  "14.6"] ,
                        ]
                        ] ,
                    ] ,
                    ],
                    ["initials"=>  "G"] ,
                ],
                ['1']=> [
                    ["id"=> '355'],
                    ["school_num"=> "4815"],
                    ["school_name"=> "东莞理工学院"],
                    ["the_year"=> 2016],
                    ["plan"=> 29],
                    ["admit"=> 29] ,
                    ["fraction_max"=> 574] ,
                    ["fraction_min"=> 523] ,
                    ["msd"=>0] ,
                    ["ler"=> 70854 ],
                    ["tas"=>  "537.6"] ,
                    ["dbas"=>  "14.6" ],
                    ["batch"=> 1 ],
                    ["type"=>  "reason"] ,
                    ["color"=>  "red"] ,
                    ["school_type"=> "公办" ],
                    ["school_management"=> "广东省" ],
                    ["school_province"=>  "广东省" ],
                    ["school_city"=>  "东莞市" ],
                    ["school_nature"=>  "本科" ],
                    ["province_school_number"=>  "154所" ],
                    ["school_renown"=> 2 ],
                    ["school_independent"=> NULL ],
                    ["show_year"=> [
                        ['2016'=> [
                            ["the_year"=> 2016 ],
                            ["plan"=> 29 ],
                            ["admit"=> 29 ],
                            ["fraction_max"=> 574 ],
                            ["fraction_min"=> 523 ],
                            ["msd"=> 0 ],
                            ["ler"=> 70854 ],
                            ["tas"=>  "537.6" ],
                            ["dbas"=>  "14.6"] ,
                        ]
                        ] ,
                    ] ,
                    ],
                    ["initials"=>  "G"] ,
                ],
                ['2']=> [
                    ["id"=> '355'],
                    ["school_num"=> "4815"],
                    ["school_name"=> "东莞理工学院"],
                    ["the_year"=> 2016],
                    ["plan"=> 29],
                    ["admit"=> 29] ,
                    ["fraction_max"=> 574] ,
                    ["fraction_min"=> 523] ,
                    ["msd"=>0] ,
                    ["ler"=> 70854 ],
                    ["tas"=>  "537.6"] ,
                    ["dbas"=>  "14.6" ],
                    ["batch"=> 1 ],
                    ["type"=>  "reason"] ,
                    ["color"=>  "red"] ,
                    ["school_type"=> "公办" ],
                    ["school_management"=> "广东省" ],
                    ["school_province"=>  "广东省" ],
                    ["school_city"=>  "东莞市" ],
                    ["school_nature"=>  "本科" ],
                    ["province_school_number"=>  "154所" ],
                    ["school_renown"=> 2 ],
                    ["school_independent"=> NULL ],
                    ["show_year"=> [
                        ['2016'=> [
                            ["the_year"=> 2016 ],
                            ["plan"=> 29 ],
                            ["admit"=> 29 ],
                            ["fraction_max"=> 574 ],
                            ["fraction_min"=> 523 ],
                            ["msd"=> 0 ],
                            ["ler"=> 70854 ],
                            ["tas"=>  "537.6" ],
                            ["dbas"=>  "14.6"] ,
                        ]
                        ] ,
                    ] ,
                    ],
                    ["initials"=>  "G"] ,
                ],
            ]
        ];
        foreach ($arr as $k => $v)
        {
            foreach ($v as $kk => $vv)
            {
                echo $v;
                echo "<br>";
            }
        }
        */
    }

}

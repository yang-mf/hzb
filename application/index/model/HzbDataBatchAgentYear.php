<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class HzbDataBatchAgentYear extends Model
{
    // 设置主表名
//    protected $table = 'yzx_hzb_data_2016_batch';
    /**
     * 获取导航
     *
     * @param string $pid 父级ID
     * @param string $type 导航类型(查询的类型)
     * @return  array
     */
    /*
     *
     * @param string $score 分数
     * @param string $year 年
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $status 冲刺保守保底
     *
     */
    public function getBatchData($score,$type,$year=null,$batch=null ,$status=null)
    {
        $result =  $this->test($score,$year,$type,$batch,$status);
        return $result;
    }

    /**
     * 测试
     *
     * @param string $score 分数
     * @param string $year 年
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $status 冲刺保守保底
     * @return array|bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function test($score,$year,$type,$batch,$status)
    {
        $this_year=$year;
        $last_year=$year-1;
        $year_name =  $this->CheckYear($this_year);
        //今年得分
        $this_year_now_score=[
            'score' => $score
        ];
        //今年得分加一分，
        $this_year_Previous_score=[
            'score' => $score + 1
        ];
        //今年得分位次
        $this_year_now=Db::name('hzb_rank')->where($this_year_now_score)->find();
        $this_year_now = $this_year_now[$year_name['this_year']];
        //加分之后的位次
        $this_year_Previous=Db::name('hzb_rank')->where($this_year_Previous_score)->find();
        $this_year_Previous = $this_year_Previous[$year_name['this_year']];
        //位次之差
        $rank = $this_year_now - $this_year_Previous;
        //得出加分项
        $w=floor($rank/100);
        //得出去年得分
        $last_year_score=Db::name('hzb_rank')->where($year_name['last_year'],'>=',$this_year_now)->find();
//        var_dump($last_year_score);die;
        $last_year_score = $last_year_score['score'];
//        var_dump($last_year_score);die;
        //今年应得分数有加分项
        $score_max =floor($last_year_score + $w) ;
//        var_dump($score_max);die;
        //今年应得分数无加分项
        $score = floor($last_year_score);
        //查询去年的录取信息
        $table='hzb_data_batch';
        $join_table_name = 'hzb_data_school_info';
        //学校批次，若没有则用程序自己判断
        if(empty($batch)){
            //计算学校批次
            $batch = $this->Batch($last_year,$type,$score_max,$score);
        }else{
            $batch = ['score_max'=>$batch,'score'=>$batch];
        }
//        var_dump($score);die;
        //演示时输入状态  冲刺保守保底 判断，直接返回值
        if(!empty($status)){
            $result = $this->CheckType($status,$score_max,$score,$table,$type,$batch,$join_table_name,$last_year);
            return $result;
        }
        //根据分数查询数据
        $where_first = 'fraction_max >= '.$score_max.' and fraction_min <= ' .$score_max;
        $where_second = 'fraction_max >= '.$score.' and fraction_min <= ' .$score;
        $where_third = 'fraction_min <= ' .$score;
        $object=Db::name($table)
            ->where(function ($query)use($where_first,$where_second,$where_third) {
                $query->where($where_first)->whereOr($where_second)->whereOr($where_third);
            })
            ->where('the_year','=',$last_year)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score_max'])
            ->where('ler','>=',$this_year_now)
            ->select();
        if(empty($object)){
            print_r("请重新输入分数，或选择批次");die;
        }
        foreach ($object as $key => $value) {
            $school_num[] = $value['school_num'];
        }
        //根据school_num获取学校基本信息
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        $school_data = Db::name('hzb_data_all_school_info')
            ->where($where_school_num)
            ->select();
        //上颜色
        foreach ($object as $k => &$v){
            $fraction_max = $v['fraction_max'];
            $fraction_min = $v['fraction_min'];
            if($fraction_max >= $score_max && $fraction_min <= $score_max ){
                $object[$k]['color'] = "red";
                $school_date_info[] = $v;
            }
            if($fraction_max >= $score && $fraction_min <= $score ){
                $object[$k]['color'] = "blue";
                $school_date_info[] = $v;
            }
            if ($fraction_min <= $score ){
                $object[$k]['color'] = "green";
                $school_date_info[] = $v;
            }
        }
        //颜色区分冲刺，保守，保底
        foreach ($school_date_info as $k => &$v) {
            if($school_date_info[$k]['color'] == "red" ){
                $new_info[] = $v;
            }
        }
        foreach ($school_date_info as $k => &$v) {
            if ($school_date_info[$k]['color'] == "blue") {
                $new_info[] = $v;
            }
        }
        foreach ($school_date_info as $k => &$v){
            if ($school_date_info[$k]['color'] == "green" ){
                $new_info[] = $v;
            }
        }
        //根据school_num获取的学校基本信息取需要的数据与分数查询出的数据合并
        foreach ($new_info as $ok => $ov)
        {
            foreach ($school_data as $sk => $sv)
            {
                if($ov['school_num'] == $sv['school_num'])
                {
                    $new_info[$ok]['school_type'] = $sv['school_type'];
                    $new_info[$ok]['school_management'] = $sv['school_management'];
                    $new_info[$ok]['school_province'] = $sv['school_province'];
                    $new_info[$ok]['school_city'] = $sv['school_city'];
                    $new_info[$ok]['school_nature'] = $sv['school_nature'];
                    $new_info[$ok]['province_school_number'] = $sv['province_school_number'];
                    $new_info[$ok]['school_renown'] = $sv['school_renown'];
                    $new_info[$ok]['school_independent'] = $sv['school_independent'];
                }
            }
        }
        $data = ['info'=>$new_info];
        return $data;
    }
    /*
     * 为年份匹配数据库字段
     *
     * @param string $this_year 所输入的成绩的年份
     */
    public function CheckYear($this_year)
    {
        if($this_year==2020) {
            $this_year_name='ershi';
            $last_year_name='yijiu';
        }elseif ($this_year==2019){
            $this_year_name='yijiu';
            $last_year_name='yiba';
        }elseif ($this_year==2018){
            $this_year_name='yiba';
            $last_year_name='yiqi';
        }elseif ($this_year==2017){
            $this_year_name='yiqi';
            $last_year_name='yiliu';
        }
        $year_name = ['this_year'=>$this_year_name,'last_year'=>$last_year_name];
        return $year_name;
    }
    /*
     * @param string $last_year 上一年
     * @param string $type 文理科
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @return string[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    //查询得分所能达到的学校批次
    public function Batch($last_year,$type,$score_max,$score)
    {
        $batch_data = Db::name('hzb_batch')
            ->where('year','=',$last_year)
            ->where('type','=',$type)
            ->find();
        if($score_max>=$batch_data['first_batch']){
            $batch_max='1';
        }elseif ($score_max>=$batch_data['second_batch']){
            $batch_max='2';
        }elseif (empty($batch_data['third_batch'])){
            $batch_max='2';
        }elseif (!empty($batch_data['third_batch']) && $score_max>=$batch_data['third_batch']  ){
            $batch_max='3';
        }elseif ($score_max>=$batch_data['spe_batch']){
            $batch_max='4';
        }
        if($score>=$batch_data['first_batch']){
            $batch='1';
        }elseif ($score>=$batch_data['second_batch']){
            $batch='2';
        }elseif (empty($batch_data['third_batch'])){
            $batch='2';
        }elseif (!empty($batch_data['third_batch']) && $score>=$batch_data['third_batch']){
            $batch='3';
        }elseif ($score>=$batch_data['spe_batch']){
            $batch='4';
        }
        $batch = ['score_max'=>$batch_max,'score'=>$batch];
        return $batch;
    }
    //冲刺
    /*
     * //冲刺
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $table 表名字
     */
    public function Sprint($score_max,$score,$table,$type,$batch,$join_table_name,$last_year)
    {
        $where = 'fraction_max >= '.$score_max.' and fraction_min <= ' .$score_max;
        $info=Db::name($table)
            ->alias('a')    // alias 表示命名数据库的别称为a
            ->join($join_table_name .' j','a.school_num = j.school_num')
            ->where($where)
            ->where('the_year','=',$last_year)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score_max'])
            ->select();
        foreach ($info as $k => &$v){
            $info[$k]['color'] = "red";
        }
        $data = ['info'=>$info];
        return $data;
    }
    //保守
    /*
     * //保守
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $table 表名字
     */
    public function Conservative($score_max,$score,$table,$type,$batch,$join_table_name,$last_year)
    {
        $where = 'fraction_max >= '.$score.' and fraction_min <= ' .$score;
        $info=Db::name($table)
            ->alias('a')    // alias 表示命名数据库的别称为a
            ->join($join_table_name .' j','a.school_num = j.school_num')
            ->where($where)
            ->where('the_year','=',$last_year)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score'])
            ->select();
        foreach ($info as $k => &$v){
            $info[$k]['color'] = "blue";
        }
        $data = ['info'=>$info];
        return $data;
    }
    //保底
    /*
     * //保底
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $table 表名字
     */
    public function Guaranteed($score_max,$score,$table,$type,$batch,$join_table_name,$last_year)
    {
        $where = 'fraction_min <= ' .$score;
        $info=Db::name($table)
            ->alias('a')    // alias 表示命名数据库的别称为a
            ->join($join_table_name .' j','a.school_num = j.school_num')
            ->where($where)
            ->where('the_year','=',$last_year)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score'])
            ->select();
        foreach ($info as $k => &$v){
            $info[$k]['color'] = "green";
        }
        $data = ['info'=>$info];
        return $data;
    }
    //判断冲刺保守保底
    /*
     * //判断冲刺保守保底
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $table 表名字字段
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $status 冲刺保守保底
     */
    public function CheckType($status,$score_max,$score,$table,$type,$batch,$join_table_name,$last_year){
//        var_dump($page);die;
        if($status == 1){
//            var_dump($status);die;
            $red = $this->Sprint($score_max,$score,$table,$type,$batch,$join_table_name,$last_year);
            return $red;
        }elseif ($status == 2){
//            var_dump($status);die;
            $blue = $this->Conservative($score_max,$score,$table,$type,$batch,$join_table_name,$last_year);
            return $blue;
        }elseif ($status == 3){
//            var_dump($status);die;
            $green = $this->Guaranteed($score_max,$score,$table,$type,$batch,$join_table_name,$last_year);
            return $green;
        }
    }
}

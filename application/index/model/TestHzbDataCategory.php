<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataCategory extends Model
{
    //将专业分为本科与专科的
    public function checkProfession($profession_name) {

        $und = Db::table('yzx_hzb_data_und_profession_data')
            ->where('profession_name','like','%'.$profession_name.'%')
            ->field('id')
            ->find();
        $spe = Db::table('yzx_hzb_data_spe_profession_all_data')
            ->where('profession_name','like','%'.$profession_name.'%')
            ->field('id')
            ->find();
        if( $und ) {
            return 1;
        }else if( $spe ) {
            return 2;
        }
    }
    //将专业分为本科与专科的
    public function checkSchool($school) {
        $res = Db::table('yzx_hzb_data_all_school_info')
            ->where('school_name',$school['school_name'])
            ->where('school_num',$school['school_num'])
            ->field('school_nature')
            ->find();
        if( $res['school_nature'] == '本科' ) {
            return 1;
        }else if( $res['school_nature'] == '专科' ) {
            return 2;
        }
    }
    /**
     * 获取ProfessionName数据
     * @param $school_nature    //院校状态，用于判单是本科或专科，小于4是本科，等于4是专科
     * @param $school_num       //院校代码，用于搜索条件使用，来自分数位次搜索之后的数据中的院校代码
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProfessionData($school_nature,$school_num,$word=null)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        if($school_nature<4){
            $result = Db::table('yzx_hzb_data_und_profession_data')
                ->where($where_school_num)
                ->where('profession_name','like','%'.$word.'%')
                ->field('profession_name')
                ->select();
        }else {
            $result = Db::table('yzx_hzb_data_spe_profession_all_data')
                ->where($where_school_num)
                ->where('profession_name','like','%'.$word.'%')
                ->field('profession_name')
                ->select();
        }
        $result = array_column($result, NULL, 'profession_name');   //以ID为索引
        $result = array_values($result);                                            //去除关联索引
        return $result;
    }
    /**
     * 获取SchoolName数据
     * @param $school_num           //院校代码，用于搜索条件使用，来自分数位次搜索之后的数据中的院校代码
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSchoolNameData($school_num)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
//        var_dump($school_num);die;
        $result = Db::table('yzx_hzb_data_school_info')
            ->where($where_school_num)
            ->field('school_name,school_num')
            ->select();
        return $result;
    }
    /**
     * 获取搜索的SchoolName数据
     * @param $word             //搜索学校信息时，前台页面传来的关键词
     * @param $school_num       //院校代码，用于搜索条件使用，来自分数位次搜索之后的数据中的院校代码
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSchoolNameSelectData($word, $school_num, $score, $batch, $type,
                                            $year, $info, $checked_province, $checked_school_type)
    {   //办学类型筛选
        $new_info=[];
        if( $checked_school_type || $checked_province ) {
            if($checked_school_type){
                foreach ($info as $k => $v)
                {
                    foreach ($checked_school_type as $kk => $vv)
                    {
                        if(!empty($v["school_type"]) && $v["school_type"] == $vv)
                        {
                            $new_info[]=$v;
                        }
                    }
                }
                if(empty($new_info)){
                    return  $new_info=['code'=>2,'message'=>'请重新输入信息'];
                }
            }
            //省份的筛选
            if($checked_province) {
                $new_show_info=[];
                foreach ($checked_province as $k=>$v)
                {
                    foreach ($info as $kk=>$vv)
                    {
                        if($v == $vv["school_province"])
                        {
                            $new_info[$v]=$vv;
                        }
                    }
                }
                if(empty($new_info)){
                    return  $new_info=['code'=>2,'message'=>'请重新输入信息'];
                }
            }
            if($new_info) {
                foreach ($school_num as $k => $v) {
                    foreach ($new_info as $kk => $vv) {
                        if($v==$vv['school_num']) {
                            $new_school_num=$v;
                        }
                    }
                }
            }
            if(!$new_school_num) {
                return  $new_info=['code'=>2,'message'=>'请重新输入信息'];
            }else {
                $school_num = $new_school_num;
            }
        }
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
        if($type == 'reason')
        {
            //今年得分位次
            $this_year_now=Db::name('hzb_reason_rank')
                ->where($this_year_now_score)
                ->find();
            $this_year_now = $this_year_now[$year_name['this_year']];
            //加分之后的位次
            $this_year_Previous=Db::name('hzb_reason_rank')
                ->where($this_year_Previous_score)
                ->find();
            $this_year_Previous = $this_year_Previous[$year_name['this_year']];
            //得出去年得分
            $last_year_score=Db::name('hzb_reason_rank')
                ->where($year_name['last_year'],'>=',$this_year_now)
                ->find();
            $last_year_score = $last_year_score['score'];
        }else if ($type == 'culture')
        {
            //今年得分位次
            $this_year_now=Db::name('hzb_culture_rank')
                ->where($this_year_now_score)
                ->find();
            $this_year_now = $this_year_now[$year_name['this_year']];
            //加分之后的位次
            $this_year_Previous=Db::name('hzb_culture_rank')
                ->where($this_year_Previous_score)
                ->find();
            $this_year_Previous = $this_year_Previous[$year_name['this_year']];
            //得出去年得分
            $last_year_score=Db::name('hzb_culture_rank')
                ->where($year_name['last_year'],'>=',$this_year_now)
                ->find();
            $last_year_score = $last_year_score['score'];
        }
        //位次之差
        $rank = $this_year_now - $this_year_Previous;
        //每年要乘以的倍数，数据库获取
        $times = model('TestHzbDataChangeTimes')->get_times();
        //得出加分项
        $w=floor(($rank/100) * $times);
        //今年应得分数有加分项
        $score_max =floor($last_year_score + $w) ;
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
        $where_first = 'fraction_max >= '.$score_max.' and fraction_min <= ' .$score_max;
        $where_second = 'fraction_max >= '.$score.' and fraction_min <= ' .$score;
        $where_third = 'fraction_min <= ' .$score;
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        $result = Db::table('yzx_hzb_data_batch')
            ->where('school_name','like','%'.$word.'%')
            ->where(function ($query)use($where_first,$where_second,$where_third) {
                $query->where($where_first)->whereOr($where_second)->whereOr($where_third);
            })
            ->where('the_year','=',$last_year)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score_max'])
            ->where('ler','>=',$this_year_now)
            ->field('school_name,school_num')
            ->select();
        return $result;
    }

    /**
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
    /**
     * 查询得分所能达到的学校批次
     * @param string $last_year 上一年
     * @param string $type 文理科
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @return string[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
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


}

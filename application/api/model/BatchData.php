<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class BatchData extends Model
{
    // 设置主表名
    protected $table = 'yzx_hzb_data_2016_batch';
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
    public function getBatchData($score,$status,$type,$year=null,$batch=null)
    {
        if(empty($year)){
            $year=date('Y');
        }
        $red =  $this->test($score,$year,$type,$batch,$status);
        return $red;
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
    public function test($score,$year,$type,$batch=null,$status)
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
        $last_year_score=Db::name('hzb_rank')->where($year_name['last_year'],'>=',$this_year_now)->select();
        $last_year_score = $last_year_score[0]['score'];
        //去年应得分数有加分项
        $score_max =floor($last_year_score + $w) ;
        //去年应得分数无加分项
        $score = floor($last_year_score);
        //查询去年的录取信息
        $table='hzb_data_'.$last_year.'_batch';
        $join_table_name = 'hzb_data_school_info';
        //学校批次，若没有则自己判断
        if(empty($batch)){
            //计算学校批次
            $batch = $this->Batch($last_year,$type,$score_max,$score);
        }
        //演示时输入状态  冲刺保守保底 判断，直接返回值
        if(empty(!$status)){
            $result = $this->CheckType($status,$score_max,$score,$table,$type,$batch,$join_table_name);
            return $result;
        }
        //冲刺学校
        $where = 'fraction_max >= '.$score_max.' and fraction_min <= ' .$score_max;
        $data_first_info=Db::name($table)->where($where)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score_max'])
            ->select();
        $red=$data_first_info;
        //保守学校
        $where = 'fraction_max >= '.$score.' and fraction_min <= ' .$score;
        $data_second_info=Db::name($table)->where($where)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score'])
            ->select();
        $blue=$data_second_info;
        //保底学校
        $where = 'fraction_min <= ' .$score;
        $data_third_info=Db::name($table)->where($where)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score'])
            ->select();
        $green=$data_third_info;
        $school_info = ['red'=>$red,'blue'=>$blue,'green'=>$green];
        return $school_info;
    }

    /*
     *
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
        }elseif (!empty($batch_data['third_batch']) && $score_max>=$batch_data['third_batch']){
            $batch_max='3';
        }elseif (!empty($batch_data['spe_batch']) && $score_max>=$batch_data['spe_batch']){
            $batch_max='4';
        }
        if($score>=$batch_data['first_batch']){
            $batch='1';
        }elseif ($score>=$batch_data['second_batch']){
            $batch='2';
        }elseif (!empty($batch_data['third_batch']) && $score>=$batch_data['third_batch']){
            $batch='3';
        }elseif (!empty($batch_data['spe_batch']) && $score>=$batch_data['spe_batch']){
            $batch='4';
        }
        $batch = ['score_max'=>$batch_max,'score'=>$batch];
        return $batch;
    }
    /*
     * //冲刺
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $table 表名字
     */
    public function Sprint($score_max,$table,$type,$batch,$join_table_name)
    {
        $where = 'fraction_max >= '.$score_max.' and fraction_min <= ' .$score_max;
        $red=Db::name($table)
            ->join('hzb_data_school_info.school_num','=','hzb_data_2016_batch.-school_num')
            ->where($where)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score_max'])
            ->select();
        return $red;
    }
    /*
     * //保守
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $table 表名字
     */
    public function Conservative($score,$table,$type,$batch,$join_table_name)
    {
        $where = 'fraction_max >= '.$score.' and fraction_min <= ' .$score;
        $blue=Db::name($table)->where($where)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score'])
            ->select();
        return $blue;
    }
    /*
     * //保底
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $table 表名字
     */
    public function Guaranteed($score,$table,$type,$batch,$join_table_name)
    {
        $where = 'fraction_min <= ' .$score;
        $green=Db::name($table)->where($where)
            ->where('type', '=',$type)
            ->where('batch','=',$batch['score'])
            ->select();
        return $green;
    }
    /*
     * //判断冲刺保守保底
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $table 表名字字段
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $status 冲刺保守保底
     */
    public function CheckType($status,$score_max,$score,$table,$type,$batch,$join_table_name){
        if($status == 1){
            $red = $this->Sprint($score_max,$table,$type,$batch,$join_table_name);
            return $red;
        }elseif ($status == 2){
            $blue = $this->Conservative($score,$table,$type,$batch,$join_table_name);
            return $blue;
        }elseif ($status == 3){
            $green = $this->Guaranteed($score,$table,$type,$batch,$join_table_name);
            return $green;
        }
    }
    
}

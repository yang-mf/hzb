<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataTheShowYear extends Model
{
    public function TheShowYear( $score , $year , $type , $batch = null , $the_show_year = null ) {
        $this_year = $year;
        $last_year = $year-1;
        $year_name = $this->CheckYear($this_year);
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
        $batch = $this->Batch($last_year,$type,$score_max,$score);
        return $batch;
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
        }else {
            return $batch=['code'=>2];
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
        }else {
            return $batch=['code'=>2];
        }
        $batch = ['code'=>1,'score_max'=>$batch_max,'score'=>$batch];
        return $batch;
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
    public function GetYearInfo($school_num,$type,$batch,$new_info,$this_year,$the_show_year)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in', $school_num);
        $year_info = $school_data = Db::name('hzb_data_batch')
            ->where($where_school_num)
            ->where('type', '=', $type)
            ->where('batch', '=', $batch['score_max'])
            ->select();
        if( $the_show_year ) {
            $show_year = (int)$the_show_year;
        }
        foreach ($new_info as $ok => $ov)
        {
            foreach ($year_info as $sk => $sv)
            {
                for( $i = $show_year ; $i >= 2016 ; $i-- ) {
                    $this_new_info = [];
                    if( $sv['the_year'] == $i
                        && $ov['school_num'] == $sv['school_num']
                        && $ov['school_name'] == $sv['school_name'] )
                    {
                        $this_new_info['the_year'] = $sv['the_year'];
                        $this_new_info['plan'] = $sv['plan'];
                        $this_new_info['admit'] = $sv['admit'];
                        $this_new_info['fraction_max'] = $sv['fraction_max'];
                        $this_new_info['fraction_min'] = $sv['fraction_min'];
                        $this_new_info['msd'] = $sv['msd'];
                        $this_new_info['ler'] = $sv['ler'];
                        $this_new_info['tas'] = $sv['tas'];
                        $this_new_info['dbas'] = $sv['dbas'];
                        $new_info[$ok]['show_year'][] = $this_new_info;
                    }
                }
            }
        }
        if( !$the_show_year ) {
            foreach ( $new_info as $k => $v ) {
                $new_info[$k]['show_year'] =
                    array_slice($new_info[$k]['show_year'],-3,3);
            }
        }else {
            foreach ( $new_info as $k => $v ) {
                $new_info[$k]['show_year'] =
                    array_slice($new_info[$k]['show_year'],-4,4);
            }
        }
        return $new_info;
    }
}

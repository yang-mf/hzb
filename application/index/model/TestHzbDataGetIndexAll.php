<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataGetIndexAll extends Model
{
    //判断分数与批次，第一次前台 展示
    public function get_batch($score, $type, $year) {
        $this_year=$year;
        $last_year=$year-1;
        $year_name =  $this->CheckYear($this_year);
        //今年得分
        $this_year_now_score=[
            'score' => $score
        ];
        if($type == 'reason') {
            //今年得分位次
            $this_year_now=Db::name('hzb_reason_rank')
                ->where($this_year_now_score)
                ->find();
            $this_year_now = $this_year_now[$year_name['this_year']];
            //得出去年得分
            $last_year_score=Db::name('hzb_reason_rank')
                ->where($year_name['last_year'],'>=',$this_year_now)
                ->find();
            $last_year_score = $last_year_score['score'];
        }else if ($type == 'culture') {
            //今年得分位次
            $this_year_now=Db::name('hzb_culture_rank')
                ->where($this_year_now_score)
                ->find();
            $this_year_now = $this_year_now[$year_name['this_year']];
            //得出去年得分
            $last_year_score=Db::name('hzb_culture_rank')
                ->where($year_name['last_year'],'>=',$this_year_now)
                ->find();
            $last_year_score = $last_year_score['score'];
        }
        $batch_info = Db::table('yzx_hzb_batch')
            ->where('year',$last_year)
            ->where('type',$type)
            ->select();
        $batch=[];
        if( $last_year_score >= $batch_info[0]['first_batch'] ) {
            $batch[]=['batch_name'=>'一批','batch_value'=>1];
            $batch[]=['batch_name'=>'二批','batch_value'=>2];
            $batch[]=['batch_name'=>'专科','batch_value'=>4];
        }else if( $last_year_score >= $batch_info[0]['second_batch']
            && $last_year_score < $batch_info[0]['first_batch'] ) {
            $batch[]=['batch_name'=>'二批','batch_value'=>2];
            $batch[]=['batch_name'=>'专科','batch_value'=>4];
        }else if( $last_year_score >= $batch_info[0]['spe_batch']
            && $last_year_score < $batch_info[0]['second_batch'] ) {
            $batch[]=['batch_name'=>'专科','batch_value'=>4];
        } else if(  $last_year_score < $batch_info['spe_batch'] ) {
            $batch[]=['batch_code'=>'9','message'=>'抱歉，您输入的分数暂无对于批次'];
            return $batch;
        }
        $batch=['batch_code'=>'1','batch_data'=>$batch];
        return $batch;
    }
    //获取全部专业名称，第一次前台 展示
    public function get_profession() {
        $und_profession_name = Db::table('yzx_hzb_data_und_profession_info')
            ->field('profession_name,id')
            ->select();
//        $spe_profession_name = Db::table('yzx_hzb_data_spe_profession_info')
//            ->field('profession_name,id')
//            ->select();
        $und_profession_name = array_column($und_profession_name,null,'profession_name');
        $profession_name = array_values($und_profession_name);
//        $spe_profession_name = array_column($spe_profession_name,null,'profession_name');
//        $spe_profession_name = array_values($spe_profession_name);
//        $profession_name = array_merge($und_profession_name,$spe_profession_name);
        return $profession_name;
    }
    //获取全部省份名称，第一次前台 展示
    public function get_province() {
        $province_name = Db::table('yzx_hzb_data_all_school_info')
            ->field('school_province,id')
            ->select();
        $province_name = array_column($province_name,null,'school_province');
        $province_name = array_values($province_name);
        return $province_name;
    }
    //获取全部学院类型，第一次前台 展示
    public function get_school_type() {
        $school_type[]=['school_type_name'=>'公办','school_type_num'=>1];
        $school_type[]=['school_type_name'=>'民办','school_type_num'=>2];
        $school_type[]=['school_type_name'=>'中外合作办学','school_type_num'=>3];
        $school_type[]=['school_type_name'=>'内地与港澳台地区合作办学','school_type_num'=>4];
        return $school_type;
    }
    //获取全部专院校名称，第一次前台 展示
    public function get_school_name() {
        $school_name = Db::table('yzx_hzb_data_all_school_info')
            ->field('school_name,school_num')
            ->select();
        return $school_name;
    }
    /**
     * 为年份匹配数据库字段
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
}

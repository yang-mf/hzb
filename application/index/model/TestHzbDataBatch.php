<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataBatch extends Model
{
    /**
     * 页面展示
     * @param string $score 分数
     * @param string $type  文理科
     * @param null $year    年
     * @param null $batch   批次
     * @param null $status  冲刺保守保底
     * @return array|bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBatchData($score,$type,$rank,$year,$batch,$the_show_year=null)
    {
        $result =  $this->test($score,$type,$rank,$year,$batch,$the_show_year);
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
    public function test($score,$type,$rank,$year,$batch,$the_show_year=null)
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
        if($type == 'reason') {
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
        }else if ($type == 'culture') {
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
        $rank_difference = $this_year_now - $this_year_Previous;
        //每年要乘以的倍数，数据库获取
        $times = model('TestHzbDataChangeTimes')->get_times();
        //得出加分项
        $w=floor(($rank_difference/100) * $times);
        //今年应得分数有加分项
        $score_max =floor($last_year_score + $w) ;
        //今年应得分数无加分项
        $score = floor($last_year_score);
        if( empty( $rank )) {
            $rank = $this_year_now;
        }
        //查询去年的录取信息
        $table='hzb_data_batch';
        //学校批次，若没有则用程序自己判断
//        if(empty($batch)){
//            //计算学校批次
//            $batch = $this->Batch($last_year,$type,$score_max,$score);
//            if( $batch['code']==2 ) {
//                return $data = ['code'=>2];
//            }
//        }else{
            $batch = ['score'=>$batch];
//        }
        //根据分数查询数据
        $where_first  = 'fraction_max >= ' .$score_max.' and fraction_min <= ' .$score_max;
        $where_second = 'fraction_max >= ' .$score.' and fraction_min <= ' .$score;
        $where_third  = 'fraction_max <= ' .$score;
        $object=Db::name($table)
            ->where(function ($query)use($where_first,$where_second,$where_third) {
                $query->where($where_first)->whereOr($where_second)->whereOr($where_third);
            })
            ->where('the_year','=',$last_year)
            ->where('type', '=',$type)
            ->whereIn('batch',$batch['score'])
            ->where('ler','>=',$rank)
            ->select();
        //再做多次前几年的院校的查询，然后比对院校名称查看是否有今年招生，去年未招生，但是再之前有过招生的院校
        //如果有那么根据当前分分数换算至招生当年查看该学校是否你能上，如果可以，加入数据集，不可以则舍弃，执行下一步
        if(empty($object)){
            return $data=[];
        }
        //school_num
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
            if ($fraction_max <= $score ){
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
        //去除没有省份的值（相当于去除学校详细信息中该学校没有学院代码，也就是不在河南招生）
        foreach ($new_info as $key => $value) {
            if(!isset($value['school_province']))
            {
                unset($new_info[$key]);
            }
        }
        $school_nature[]=$batch['score'];
        $new_info=$this->GetYearInfo($school_num,$type,$batch,$new_info,$this_year,$the_show_year);
        foreach ($new_info as $key => $value) {
            $school_name[] = [
                'school_name'=>$value['school_name'],
                'school_num'=>$value['school_num'],
                'batch'=>$value['batch'],
            ];
            $school_type[]=$value['school_type'];
            $province[]=$value['school_province'];
        }
        $school_name = array_column($school_name,null,'school_num');
        $school_name = array_values($school_name);
        $school_type = array_unique($school_type);
        $province    = array_unique($province);
        foreach ($province as $k => $v ) {
            $province_name[]['school_province']=$v;
        }
        $und=[];$spe=[];
        foreach ( $new_info as $k => $v ) {
            $show_new_info[$v['batch']][]= $v;
            if($v['batch']==4) {
                $spe[]=$v['school_num'];        //本科
            }else {
                $und[]=$v['school_num'];          //专科
            }
        }
        if(!empty($und)) {
            $und = array_unique($und);
        }
        if(!empty($spe)) {
            $spe = array_unique($spe);
        }
        $school_num = [
            'und'=>$und, 'spe'=>$spe,
        ];
        $data = [
            'code'=>1,
            'forNextSelectInfo' =>$new_info,
            'show_info'=>$show_new_info,
            'school_province'=>$province_name,
            'school_name'=>$school_name,
            'school_num'=>$school_num,
            'school_type'=>$school_type,
        ];
        return $data;
    }
    /**
     * 给信息增加历年信息
     * @param $school_num
     * @param $type
     * @param $batch
     * @param $new_info
     * @param $this_year
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function GetYearInfo($school_num,$type,$batch,$new_info,$this_year,$the_show_year=null)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        $year_info = $school_data = Db::name('hzb_data_batch')
            ->where($where_school_num)
            ->where('type','=',$type)
            ->whereIn('batch',$batch['score'])
            ->select();

        if( !$the_show_year ) {
            $show_year = $this_year - 1;
            $test_year = $show_year -2016;
            if($test_year>=3 ) {
                $last_year = $test_year-3;
            }else {
                $last_year = 2016;
            }
        }else {
            $show_year = (int)$the_show_year;
            $test_year = $show_year -2016;
            if($test_year>=4 ) {
                $last_year = $test_year-4;
            }else {
                $last_year = 2016;
            }
        }
        $for_show_year = $show_year ;
        foreach ($new_info as $ok => $ov)
        {
            foreach ($year_info as $sk => $sv)
            {
                for( $i = $show_year ; $i >= 2016 ; $i-- ) {
                    $this_new_info = [];
                    if( $sv['the_year']         == $i
                        && $ov['school_num']    == $sv['school_num']
                        && $ov['school_name']   == $sv['school_name']
                        && $ov['batch']         == $sv['batch'])
                    {
                        $this_new_info['the_year']      = $sv['the_year'];
                        $this_new_info['plan']          = $sv['plan'];
                        $this_new_info['admit']         = $sv['admit'];
                        $this_new_info['fraction_max']  = $sv['fraction_max'];
                        $this_new_info['fraction_min']  = $sv['fraction_min'];
                        $this_new_info['msd']           = $sv['msd'];
                        $this_new_info['ler']           = $sv['ler'];
                        $this_new_info['tas']           = $sv['tas'];
                        $this_new_info['dbas']          = $sv['dbas'];
                        $this_new_info['last_year']     = $last_year;
                        $new_info[$ok]['show_year'][]   = $this_new_info;
                    }
                }
            }
        }
        $style = '';
        foreach ($new_info as $k => $v ) {
            $count = count($new_info[$k]['show_year']);
            if( $count > 3 ) {
                $style = 1;
            }
        }
        if( !$the_show_year ) {
            if( $style == 1 ) {
                foreach ( $new_info as $k => $v ) {
                    $new_info[$k]['show_year'] =
                        array_slice($new_info[$k]['show_year'],-3,3);
                }
            }
        }else {
            if( $style == 1 ) {
                foreach ($new_info as $k => $v) {
                    $new_info[$k]['show_year'] =
                        array_slice($new_info[$k]['show_year'], -4, 4);
                }
            }
        }
//        var_dump($new_info);die;
        return $new_info;
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
     * 冲刺
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
    /**
     * 保守
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
    /**
     * 保底
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
    /**
     * 判断冲刺保守保底
     * @param string $score_max 最高得分（有加分）
     * @param string $score 得分（无加分）
     * @param string $table 表名字字段
     * @param string $type 文理科
     * @param null $batch 批次
     * @param string $status 冲刺保守保底
     */
    public function CheckType($status,$score_max,$score,$table,$type,$batch,$join_table_name,$last_year){
        if($status == 1){
            $red = $this->Sprint($score_max,$score,$table,$type,$batch,$join_table_name,$last_year);
            return $red;
        }elseif ($status == 2){
            $blue = $this->Conservative($score_max,$score,$table,$type,$batch,$join_table_name,$last_year);
            return $blue;
        }elseif ($status == 3){
            $green = $this->Guaranteed($score_max,$score,$table,$type,$batch,$join_table_name,$last_year);
            return $green;
        }
    }
    /**
     * 获取省份
     * @param $info
     * @return array
     */
    public function getBatchProvince($info)
    {
        $school_province=[];
        foreach ($info as $k => $v)
        {
            foreach ( $school_province as $kk => $vv )
            {
                if(empty($school_province)){
                    $school_province[]=$v['school_province'];
                }else if($vv['school_province'] != $v['school_province'])
                {
                    $school_province[]=$v['school_province'];
                }
            }
        }
        return $school_province;
    }
}

<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class AgentHzbDataBatch extends Model
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
    public function getBatchData($score,$type,$year=null,$batch=null,$status=null)
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
            if( $batch['code']==2 ) {
                return $data = ['code'=>2];
            }
        }else{
            $batch = ['score_max'=>$batch,'score'=>$batch];
        }

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
            return $data=[];
        }
        //school_num
        foreach ($object as $key => $value) {
            $school_num[] = $value['school_num'];
        }
//        //根据school_num获取学校基本信息
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
        //去除没有省份的值（相当于去除学校详细信息中该学校没有学院代码，也就是不在河南招生）
        foreach ($new_info as $key => $value) {
            if(!isset($value['school_province']))
            {
                unset($new_info[$key]);
            }
        }
        $new_info = array_values($new_info);
        if($batch['score']==$batch['score_max']){
            $school_nature[]=$batch['score_max'];
        }else if($batch['score']<$batch['score_max'] && $batch['score']==4)
        {
            $school_nature[]=$batch['score_max'];
            $school_nature[]=$batch['score'];
        }
        $new_info=$this->GetYearInfo($school_num,$type,$batch,$new_info,$this_year);
        $school_type=$this->GetTypeInfo($new_info);
        foreach ($new_info as $key => $value) {
            $school_name[] = [
                'school_name'=>$value['school_name'],
                'school_num'=>$value['school_num'],
            ];
        }
        $school_name = array_column($school_name,null,'school_num');
        $school_name = array_values($school_name);
        $data = [
            'code'=>1,
            'info'=>$new_info,
            'school_nature'=>$school_nature,
            'school_name'=>$school_name,
            'school_type'=>$school_type,
        ];
        return $data;
    }
    /**
     * 获取type的值
     * @param $new_info         //传值，获取其中值
     * @return array            //返回值，数组形式，获取type的值
     */
    public function GetTypeInfo($new_info)
    {
        $school_type=[];
        foreach ($new_info as $k => $v)
        {
            $school_type[]=$v['school_type'];
        }
        $school_type=array_unique($school_type);
        return $school_type;
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
    public function GetYearInfo($school_num,$type,$batch,$new_info,$this_year)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        $year_info = $school_data = Db::name('hzb_data_batch')
            ->where($where_school_num)
            ->where('type','=',$type)
            ->where('batch','=',$batch['score_max'])
            ->select();
        $show_year = $this_year - 1;
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
        foreach ( $new_info as $k => $v ) {
            $new_info[$k]['show_year'] =
                array_slice($new_info[$k]['show_year'],-3,3);
        }
        return $new_info;
    }
    public function for_year($show_year,$ok,$ov,$sk,$sv) {
        $num = 0;
        for( $i = $show_year ; $i >= 2016 ; $i-- ) {
            if( $sv['the_year'] == $i && $ov['school_num'] == $sv['school_num'] )
            {
                if($num>=3) {
                    break;
                }
                $new_info[$ok]['show_year'][$i]['the_year'] = $sv['the_year'];
                $new_info[$ok]['show_year'][$i]['plan'] = $sv['plan'];
                $new_info[$ok]['show_year'][$i]['admit'] = $sv['admit'];
                $new_info[$ok]['show_year'][$i]['fraction_max'] = $sv['fraction_max'];
                $new_info[$ok]['show_year'][$i]['fraction_min'] = $sv['fraction_min'];
                $new_info[$ok]['show_year'][$i]['msd'] = $sv['msd'];
                $new_info[$ok]['show_year'][$i]['ler'] = $sv['ler'];
                $new_info[$ok]['show_year'][$i]['tas'] = $sv['tas'];
                $new_info[$ok]['show_year'][$i]['dbas'] = $sv['dbas'];
            }else {
                $this->for_year($show_year,$ok,$ov,$sk,$sv);
            }

        }
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

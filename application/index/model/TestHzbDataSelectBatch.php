<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataSelectBatch extends Model
{
    //返回值名称全部为$show_new_info
    /**
     *专业信息筛选
     * @param $school_nature    //院校状态，用于判单是本科或专科，小于4是本科，等于4是专科
     */
    public function check_sta_profession($show_info,$sta_profession)
    {
        $show_new_info=[];
        foreach ( $sta_profession as $k => $v ) {
            if( $k == 'und' ) {
                $res = Db::name('hzb_data_und_profession_info')
                    ->whereIn('profession_name',$v)
                    ->select();
                if($res)
                {
                    $school_info = Db::name('hzb_data_und_profession_data')
                        ->whereIn('profession_name',$v)
                        ->select();
                }else{
                    return  $show_new_info=['code'=>2,'message'=>'请重新输入信息'];
                }
            }elseif ( $k == 'spe' ) {
                $res = Db::name('hzb_data_spe_profession_info')
                    ->where('profession_name', '=', $v)
                    ->find();
                if ($res) {
                    $school_info = Db::name('hzb_data_spe_profession_all_data')
                        ->where('profession_name', '=', $v)
                        ->select();
                } else {
                    return $show_new_info = ['code' => 2, 'message' => '请重新输入信息'];
                }
            }
                if($school_info)
                {
                    foreach ($show_info as $showkey => $showvalue ) {
                        foreach ( $showvalue as $key => $value ) {
                            foreach ($school_info as $schoolkey => $schoolvalue ) {
                                if( $value['school_num'] == $schoolvalue['school_num'] ) {
                                    $show_new_info[$showkey][]=$value;
                                }
                            }
                        }
                    }
                }else{
                    return  $show_new_info=['code'=>2,'message'=>'抱歉，请您再选择其他专业'];
                }
            }

        return  $show_new_info=['code'=>1,'show_new_info'=>$show_new_info];

    }
    /**
     *学校名称筛选
     *
     */
    public function check_sta_school($show_info,$sta_school)
    {
        $show_new_info=[];
        foreach ( $show_info as $showkey => $showvalue) {
            foreach ( $sta_school as $schoolkey => $schoolvalue ) { //循环学校，可能有本科，专科
                if( $showkey < 4 && $schoolkey == 'und' ) {
                    foreach ( $showvalue as $key => $value ) {
                        foreach ( $schoolvalue as $item ) {                 //循环所以获取的学校
                            if ($value['school_num'] == $item['school_num']
                                && $value['school_name'] == $item['school_name']) {
                                $show_new_info[$showkey][] = $value;
                            }
                        }
                    }
                }else if( $showkey = 4 && $schoolkey == 'spe' ) {
                    foreach ( $showvalue as $key => $value ) {
                            foreach ( $schoolvalue as $item ) {                 //循环所以获取的学校
                        if( $value['school_num'] == $item['school_num']
                            && $value['school_name'] == $item['school_name']) {
                            $show_new_info[$showkey][]=$value;
                            }
                        }
                    }
                }
            }
        }
        if(empty($show_new_info)){
            return  $show_new_info=['code'=>2,'message'=>'抱歉，请您再选择其他学校'];
        }
        return  $show_new_info=['code'=>1,'show_new_info'=>$show_new_info];
    }
    /**
     *办学类型筛选
     *
     */
    public function check_checked_province_name($show_info,$checked_school_type)
    {
        foreach ($show_info as $showkey => $showvalue ) {
            foreach ( $showvalue as $key => $value ) {
                foreach ($checked_school_type as $SchoolTypeKey => $SchoolTypeValue ) {
                    if(!empty($value["school_type"]) && $value["school_type"] == $SchoolTypeValue) {
                        $show_new_info[$showkey][]=$value;
                    }
                }
            }
        }
        if(empty($show_new_info)){
            return  $show_new_info=['code'=>2,'message'=>'抱歉，请您再选择其他学校办学类型'];
        }
        return  $show_new_info=['code'=>1,'show_new_info'=>$show_new_info];
    }
    /**
     *省份的筛选
     *
     */
    public function check_province($show_info,$checked_province)
    {
        $new_show_info=[];
        foreach ( $show_info as $ShowKey=>$ShowValue)
        {
            foreach ( $ShowValue as $key => $value ) {
                foreach ( $checked_province as $provinceKey => $provinceValue ) {
                    if( $key == $provinceValue ) {
                        $show_new_info[$ShowKey][$key]=$value;
                    }
                }
            }
        }
        if(empty($show_new_info)){
            return  $show_new_info=['code'=>2,'message'=>'抱歉，请您再选择其他省份'];
        }
        return  $show_new_info=['code'=>1,'show_new_info'=>$show_new_info];
    }
}

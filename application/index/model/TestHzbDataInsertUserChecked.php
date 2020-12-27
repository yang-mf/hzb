<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataInsertUserChecked extends Model
{
    public function InsertUserChecked($user_id, $score, $batch, $type, $year, $rank, $state_profession=null,
    $state_school=null, $checked_province=null, $checked_school_type=null, $the_show_year=null) {
        if($state_profession) {
            $state_profession = implode(',',$state_profession);
        }
        if($state_school) {
            $state_school = implode('+',$state_school);
        }
        if($batch) {
            $batch = implode(',',$batch);
        }
        if($checked_province) {
            $checked_province = implode(',',$checked_province);
        }
        if($checked_school_type) {
            $checked_school_type = implode(',',$checked_school_type);
        }
        $res = Db::table('yzx_hzb_user_checked_info')
            ->where('user_id', $user_id)
            ->field('max(check_num)')
            ->find();
//        var_dump($res);
        foreach ( $res as $k => $v ) {
            $check_num = $v+1;
        }
        $data = [
            'user_id'   =>$user_id,
            'score'     =>$score,
            'batch'     =>$batch,
            'type'      =>$type,
            'year'      =>$year,
            'rank'      =>$rank,
            'check_num' =>$check_num,
            'state_profession'      =>$state_profession,
            'state_school'          =>$state_school,
            'checked_province'      =>$checked_province,
            'checked_school_type'   =>$checked_school_type,
            'the_show_year'         =>$the_show_year,
        ];
        $res = Db::table('yzx_hzb_user_checked_info')
            ->insert($data);
    }
}

<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataSelectBatch extends Model
{
    /**
     *专业信息筛选
     * @param $school_nature    //院校状态，用于判单是本科或专科，小于4是本科，等于4是专科
    */
    //返回值名称全部为$show_new_info
    public function check_sta_profession($show_info,$sta_profession,$school_nature)
    {
        $show_new_info=[];
        if($school_nature[0]<4)
        {
            $res = Db::name('hzb_data_und_profession_info')
                ->where('profession_name','=',$sta_profession)
                ->find();
            if($res)
            {
            $school_info = Db::name('hzb_data_und_profession_data')
                ->where('profession_name','=',$sta_profession)
                ->select();
            }
            if($school_info)
            {
                foreach ($show_info as $k => $v)
                {
                    foreach ($school_info as $kk => $vv )
                    {
                        if($v['school_num'] == $vv['school_num'])
                        {
                            $show_new_info[]=$v;
                        }
                    }
                }
            }
            return  $show_new_info;
        } else
            {
            $res = Db::name('hzb_data_spe_profession_info')
                ->where('profession_name','=',$sta_profession)
                ->find();
            if($res)
            {
                $school_info = Db::name('hzb_data_spe_profession_all_data')
                    ->where('profession_name','=',$sta_profession)
                    ->select();
            }
            if($school_info)
            {
                foreach ($show_info as $k => $v)
                {
                    foreach ($school_info as $kk => $vv )
                    {
                        if($v['school_name'] == $vv['school_name'])
                        {
                            $show_new_info[]=$v;
                        }
                    }
                }
            }
            return  $show_new_info;
        }
    }
    /**
     *学校名称筛选
     *
     */
    public function check_sta_school($show_info,$sta_school)
    {
        $show_new_info=[];
        foreach ($show_info as $k => $v)
        {
            if($v['school_name'] == $sta_school)
            {
                $show_new_info[]=$v;
            }
        }
        return  $show_new_info;
    }
    /**
     *办学类型筛选
     *
     */
    public function check_checked_province_name($show_info,$checked_school_type)
    {
        foreach ($show_info as $k => $v)
        {
            foreach ($checked_school_type as $kk => $vv)
            {
                if(!empty($v["school_type"]) && $v["school_type"] == $vv)
                {
                    $show_new_info[]=$v;
                }
            }
        }
        return  $show_new_info;
    }
    /**
     *省份的筛选
     *
     */
    public function check_province($show_info,$checked_province)
    {

        $new_show_info=[];
        foreach ($checked_province as $k=>$v)
        {
            foreach ($show_info as $kk=>$vv)
            {
                if($v == $kk)
                {
                    $new_show_info[$v]=$vv;
                }
            }
        }
        return $new_show_info;
    }
}

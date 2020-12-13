<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataSelectBatch extends Model
{
    /**
     *专业信息搜索
     *
    */
    public function check_sta_profession($show_info,$sta_profession,$profession)
    {
        $show_new_info=[];
        if($profession =='profession1' || $profession =='')
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
        }elseif ($profession =='profession2')
        {
            $res = Db::name('hzb_data_spe_profession_info')
                ->where('profession_name','=',$sta_profession)
                ->find();

            if($res)
            {
                $school_info = Db::name('hzb_data_spe_profession_data')
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
     *学校名称搜索
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
     *办学类型搜索
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
    public function check_province($show_info,$test)
    {

        $new_show_info=[];
        foreach ($test as $k=>$v)
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

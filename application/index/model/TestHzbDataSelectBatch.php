<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataSelectBatch extends Model
{
    // 设置主表名
    // protected $table = 'yzx_hzb_data_2016_batch';
    /**
     * 获取导航
     *
     * @param string $pid 父级ID
     * @param string $type 导航类型(查询的类型)
     * @return  array
     */
    /*
    *专业信息搜索
     *
    */
    public function check_sta_profession($show_info,$sta_profession,$profession)
    {
        $show_new_info=[];
        if($profession =='profession1')
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
//            var_dump((array)($show_info));die;
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
//            var_dump( $show_new_info );die;
            return  $show_new_info;
        }
    }
    /*
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
    /*
     *办学类型搜索
     *
     */
    public function check_pp_type($show_info,$pp_type)
    {
        $show_new_info=[];
        if($pp_type == 'type1' )
        {
            $pp_type='公办';
        }elseif($pp_type == 'type2' )
        {
            $pp_type='民办';
        }elseif($pp_type == 'type3' )
        {
            $pp_type='内地与港澳台地区合作办学';
        }elseif($pp_type == 'type4' )
        {
            $pp_type='中外合作办学';
        }
//        var_dump($show_info);die;
        foreach ($show_info as $k => $v)
        {
            if(!empty($v["school_type"]) && $v["school_type"] == $pp_type)
            {
                $show_new_info[]=$v;
            }
        }
        return  $show_new_info;
    }


}

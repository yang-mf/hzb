<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataCategory extends Model
{
    /**
     * 获取导航
     *
     * @param string $pid 父级ID
     * @param string $type 导航类型(查询的类型)
     * @return  array
     */
    /*
     *
     */

    //获取部分Profession数据
    public function getProfessionData($profession)
    {
        if(empty($profession) || $profession=='profession1'){
            $result = Db::table('yzx_hzb_data_und_profession_info')
                ->field('profession_name')
                ->select();
        }elseif ($profession=='profession2'){
            $result = Db::table('yzx_hzb_data_spe_profession_info')
                ->field('profession_name')
                ->select();
        }
        return $result;
    }
    //获取搜索的Profession数据
    public function getProfessionSelectData($word,$profession)
    {
        if(empty($profession) || $profession=='profession1'){
            $result = Db::table('yzx_hzb_data_und_profession_info')
                ->field('profession_name')
                ->where('profession_name','like','%'.$word.'%')
                ->select();
        }elseif ($profession=='profession2'){
            $result = Db::table('yzx_hzb_data_spe_profession_info')
                ->field('profession_name')
                ->where('profession_name','like','%'.$word.'%')
                ->select();
        }
        return $result;
    }
    //获取部分SchoolName数据
    public function getSchoolNameData()
    {
        $result = Db::table('yzx_hzb_data_school_info')->field('school_name')->select();
        return $result;
    }
    //获取搜索的SchoolName数据
    public function getSchoolNameSelectData($word)
    {
        $result = Db::table('yzx_hzb_data_school_info')->field('school_name')->where('school_name','like','%'.$word.'%')->select();
        return $result;
    }


}

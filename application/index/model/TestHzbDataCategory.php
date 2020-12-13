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

    //获取Profession数据
    public function getProfessionData($school_nature,$school_num,$school_name)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        if($school_nature[0]<4){
            $result = Db::table('yzx_hzb_data_und_profession_data')
                ->where($where_school_num)
                ->field('profession_name')
                ->select();
            $result = array_column($result, NULL, 'profession_name');   //以ID为索引
            $result = array_values($result);//去除关联索引
        }elseif ($school_nature[0]=4){
            $where_school_num = array();
            $where_school_num ['school_num'] = array('in',$school_name);
            $result = Db::table('yzx_hzb_data_spe_profession_data')
                ->where($where_school_num)
                ->field('profession_name')
                ->select();
        }
        return $result;
    }
    //获取搜索的Profession数据
    public function getProfessionSelectData($word,$school_nature,$school_num,$school_name,$profession_restaurants)
    {
//        var_dump($profession_restaurants);die;
        if($school_nature[0]<4){
            $result = Db::table('yzx_hzb_data_und_profession_info')
                ->field('profession_name')
                ->where('profession_name','like','%'.$word.'%')
                ->select();
        }elseif ($school_nature[0]=4){
            $result = Db::table('yzx_hzb_data_spe_profession_info')
                ->field('profession_name')
                ->where('profession_name','like','%'.$word.'%')
                ->select();
        }
        foreach ($profession_restaurants as $k => $v) {
            foreach ($result as $kk => $vv) {
                if($vv['profession_name'] == $v['profession_name'])
                {
                    $new_result[]=$vv;
                }
            }
        }
        $result = $new_result;
        return $result;
    }
    //获取SchoolName数据
    public function getSchoolNameData($show_info)
    {
        $school_num=[];
        foreach ($show_info as $key => $value) {
            $school_num[] = $value['school_num'];
        }
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        $result = Db::table('yzx_hzb_data_school_info')
            ->where($where_school_num)
            ->field('school_name')
            ->select();

        return $result;
    }
    //获取搜索的SchoolName数据
    public function getSchoolNameSelectData($word,$show_info)
    {
        $school_num=[];
        foreach ($show_info as $key => $value) {
            $school_num[] = $value['school_num'];
        }
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        $result = Db::table('yzx_hzb_data_school_info')
            ->where($where_school_num)
            ->field('school_name')
            ->where('school_name','like','%'.$word.'%')
            ->select();
        return $result;
    }
}

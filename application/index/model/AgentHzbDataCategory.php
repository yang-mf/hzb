<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class AgentHzbDataCategory extends Model
{
    /**
     * 获取ProfessionName数据
     * @param $school_nature    //院校状态，用于判单是本科或专科，小于4是本科，等于4是专科
     * @param $school_num       //院校代码，用于搜索条件使用，来自分数位次搜索之后的数据中的院校代码
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProfessionData($school_nature,$school_num)
    {
//        var_dump($school_num);die;
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        if($school_nature[0]<4){
            $result = Db::table('yzx_hzb_data_und_profession_data')
                ->where($where_school_num)
                ->field('profession_name')
                ->select();
        }else {
            $result = Db::table('yzx_hzb_data_spe_profession_all_data')
                ->where($where_school_num)
                ->field('profession_name')
                ->select();
        }
        $result = array_column($result, NULL, 'profession_name');   //以ID为索引
        $result = array_values($result);//去除关联索引
        return $result;
    }
    /**
     * 获取搜索的ProfessionName数据
     * @param $word             //搜索专业信息时，前台页面传来的关键词
     * @param $school_nature    //院校状态，用于判单是本科或专科，小于4是本科，等于4是专科
     * @param $school_num       //院校代码，用于搜索条件使用，来自分数位次搜索之后的数据中的院校代码
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProfessionSelectData($word,$school_nature,$school_num)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        if($school_nature[0]<4){
            $res = Db::table('yzx_hzb_data_und_profession_info')
                ->where('profession_name','=',$word)
                ->select();
            if($res)
            {
                return $res;
            }
            $result = Db::table('yzx_hzb_data_und_profession_info')
                ->field('profession_name')
                ->where($where_school_num)
                ->where('profession_name','like','%'.$word.'%')
                ->select();
        }elseif ($school_nature[0]=4){
            $res = Db::table('yzx_hzb_data_spe_profession_all_data')
                ->where('profession_name','=',$word)
                ->select();
            if($res)
            {
                return $res;
            }
            $result = Db::table('yzx_hzb_data_spe_profession_all_data')
                ->field('profession_name')
                ->where($where_school_num)
                ->where('profession_name','like','%'.$word.'%')
                ->select();
        }
        return $result;
    }
    /**
     * 获取SchoolName数据
     * @param $school_num           //院校代码，用于搜索条件使用，来自分数位次搜索之后的数据中的院校代码
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSchoolNameData($school_num)
    {
        $where_school_num = array();
        $where_school_num ['school_num'] = array('in',$school_num);
        $result = Db::table('yzx_hzb_data_school_info')
            ->where($where_school_num)
            ->field('school_name,school_num')
            ->select();
        return $result;
    }
    /**
     * 获取搜索的SchoolName数据
     * @param $word             //搜索学校信息时，前台页面传来的关键词
     * @param $school_num       //院校代码，用于搜索条件使用，来自分数位次搜索之后的数据中的院校代码
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSchoolNameSelectData($word,$school_num)
    {
        $res = Db::table('yzx_hzb_data_school_info')
            ->where('school_name','=',$word)
            ->select();
        if($res)
        {
            return $res;
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

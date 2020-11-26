<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class Common extends Model
{

    /**
     * 获取科目ID
     * @param   string  $name
     * @return  string
     */
    public function getSubjectId($name)
    {
        $subjectArr = Config::get('site.subject');
        foreach ($subjectArr as $key => $val) {
            if($name == $val){
                $result = $key;
            }
        }
        return $result;
    }

    /**
     * 获取科目名称
     * @param   string  $id
     * @return  string
     */
    public function getSubjectName($id)
    {
        $subjectArr = Config::get('site.subject');
        foreach ($subjectArr as $key => $val) {
            if($id == $key){
                $result = $val;
            }
        }
        return $result;
    }

    /**
     * 获取科目记录表明
     * @param   string  $id
     * @return  string
     */
    public function getSubjectTable($id)
    {
        switch ($id)
        {
            case "1":
                $table = 'grade_yw_log';
                break;
            case "2":
                $table = 'grade_sx_log';
                break;
            case "3":
                $table = 'grade_yy_log';
                break;
            case "4":
                $table = 'grade_zz_log';
                break; 
            case "5":
                $table = 'grade_ls_log';
                break; 
            case "6":
                $table = 'grade_dl_log';
                break; 
            case "7":
                $table = 'grade_wl_log';
                break; 
            case "8":
                $table = 'grade_hx_log';
                break; 
            case "9":
                $table = 'grade_sw_log';
                break; 
            case "10":
                $table = 'grade_wz_log';
                break; 
            case "11":
                $table = 'grade_lz_log';
                break;                                                                                 
            default:
            $table = '';
        }
        return $table;
    }

    /**
     * 获取优良差
     * @param   string  $score
     * @param   string  $total
     * @return  string
     */
    public function getScoreLevel($score, $total)
    {
        // 参数
        $mark = Config::get('site.mark');
        $great_rate = $mark['great_rate'] / 100;
        $good_rate = $mark['good_rate'] / 100;

        // 判断分数优良差
        if($score >= $great_rate * $total ) $score_level = 'great';
        if($great_rate * $total > $score && $score >= $good_rate * $total) $score_level = 'good';
        if($good_rate * $total > $score) $score_level = 'bad';

        return $score_level;
    }



}

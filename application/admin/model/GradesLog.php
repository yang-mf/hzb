<?php

namespace app\admin\model;

use think\Model;
use think\Config;

class GradesLog extends Model
{

    

    

    // 表名
    protected $name = 'grades_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    


    // 获取区域名
    public function getAreaName($id)
    {
        $name = model('area')->where(['id'=>$id])->value('name');
        return $name;
    }

    // 获取年级名
    public function getGradeName($id)
    {
        $grade = Config::get('site.grade');
        foreach($grade as $key => $val){
            if($key == $id){
                $name = $val;
            }else{
                $name = '--';
            }
        }
        return $name;
    }

    // 记录成绩初始化
    public function getGradesInit($grades)
    {
        $gradesArr = json_decode($grades,true);
        $result = '';
        $total = 0;
        foreach($gradesArr as $row){
            
            foreach ($row as $key => $val) {
                $name = $this->getSubjectName($key);
                foreach($val as $k => $v){
                    $id = $k;
                    $score = $v;
                    $total += $v;
                }
            }
            $result .= $name.':'.$score.' | ';
        }
        $result = $result . '总分:' . $total;

        return $result;
    }

    // 预测成绩初始化
    public function getForecastInit($grades)
    {
        if($grades){
            $gradesArr = json_decode($grades,true);
            $result = '';
            foreach($gradesArr as $key => $val){
                if($key != 0){
                    $name = $this->getSubjectName($key);
                    $score = $val;
                    $result .= $name.':'.$score.' | ';
                }else{
                    $name = '总分';
                    $score = $val;
                    $result .= $name.':'.$score.' | ';
                }
                
            }
            $result = rtrim($result, ' | ');
        }else{
            $result = '未预测';
        }
        
        return $result;
    }

    /**
     * 获取科目名称
     */
    public function getSubjectName($id)
    {
        $subjectArr = Config::get('site.subject');
        foreach ($subjectArr as $key => $val) {
            if($id == $key){
                $name = $val;
            }
        }
        return $name;
    }

    public function userschool()
    {
        return $this->belongsTo('UserSchool', 'school_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function useracademic()
    {
        return $this->belongsTo('UserAcademic', 'academic_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function forecast()
    {
        return $this->belongsTo('ForecastLog', 'forecast_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    
}

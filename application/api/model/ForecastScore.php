<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class ForecastScore extends Model
{

    /**
     * 预测分数
     *
     * @param string $user_id  会员ID
     * @param array $grades 成绩数组
     * @return  array
     */
    public function getForecastScore($user_id, $grades)
    {
        $total_now = 0;// 本次总成绩
        $total_avg = 0;// 记录中总成绩平均值
        $total_full = 0;// 总成绩满分
        foreach($grades as $key =>  $val){
            // 获取表明
            $table = model('common')->getSubjectTable($key);
            // 查询两次成绩的平均值
            $arr = Db::name($table)->where(['user_id'=>$user_id])->order('id desc')->limit(2)->column('score');
            $sum = 0;
            foreach ($arr as $row) {
                $sum += $row; 
            }
            $avg = $sum / 2;
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$key])->value('full');
            $forecastScore = $this->forecastScoreFormula($avg, $val, $full);
            $total_now += $val;
            $total_avg += $avg;
            $total_full += $full;
            $forecastGrades[$key] = $forecastScore;
        }
        $forecastGrades[0] = $this->forecastScoreFormula($total_avg, $total_now, $total_full);
        return $forecastGrades;
    }

    /**
     * 获取分数评语
     *
     * @param string $user_id  会员ID
     * @param string $score    分数
     * @param array $subjectArr  科目
     * @return  string
     */
    public function getScoreRemark($user_id, $score, $subjectArr)
    {   
        $total = 0;
        $last_total = 0;
        foreach($subjectArr as $subject_id){
            // 科目满分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$subject_id])->value('full');
            // 上次成绩
            $table = model('common')->getSubjectTable($subject_id);
            $last = Db::name($table)->where(['user_id'=>$user_id])->order('id desc')->value('score');
            $total += $full;
            $last_total += $last;
        }
        // 判断优良差
        $level = model('common')->getScoreLevel($score, $total);
        if($level == 'great') {$type = 1;$number = 3;}// 优秀正负差3
        if($level == 'good') {$type = 2;$number = 5;}// 良好正负差5
        if($level == 'bad') {$type = 3;$number = 7;}// 一般正负差7
        // 判断进步/退步
        $max = $last_total + $number;
        $min = $last_total - $number;
        if($score > $max) $category = 1;// 进步
        if($max >= $score && $score >= $min) $category = 2;//保持
        if($score < $min) $category = 3;//退步
        // 随机查询一条评语
        $remark = Db::name('grades_remark')->where(['type'=>$type,'category'=>$category,'status'=>'normal'])->orderRaw('rand()')->value('tips');
        return $remark;
    }



    /**
     * 估分公式
     *
     * @param string $avg  平均分
     * @param string $total 总分
     * @return  string
     */
    private function forecastScoreFormula($avg, $score, $total)
    // public function forecastScoreFormula($avg, $score, $total)// 测试用
    {   
        // 估分参数
        $mark = Config::get('site.mark');

        // 判断优良差
        $level = model('common')->getScoreLevel($score, $total);

        // 获取比率
        $max_ratio = $level . '_max_ratio';
        $min_ratio = $level . '_min_ratio';
        // 求最大/小值
        $max = round(($avg * $mark[$max_ratio]),1);
        $min = round(($avg * $mark[$min_ratio]),1);
        if($max > $total) $max = $total;
        if($min < 0) $min = 0;
        $result = $min . '--' . $max;

        return $result;
    }

    


}

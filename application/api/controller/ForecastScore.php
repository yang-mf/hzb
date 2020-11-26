<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\GradesLog;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 预测分数
 */
class ForecastScore extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 预测分数
     *
     * @param string $province  省份
     * @param string $city 城市
     * @param string $area 县区
     * @param string $school 学校
     * @param string $user_id 会员ID
     * @param string $title 成绩名称
     * @param string $yw_score 语文成绩
     * @param string $sx_score 数学成绩
     * @param string $yy_score 英语成绩
     * @param string $zz_score 政治成绩
     * @param string $ls_score 历史成绩
     * @param string $dl_score 地理成绩
     * @param string $wl_score 物理成绩
     * @param string $hx_score 化学成绩
     * @param string $sw_score 生物成绩
     * @param string $wz_score 文综成绩
     * @param string $lz_score 理综成绩
     */
    public function forecast()
    {
        $province = $this->request->request('province');
        $city = $this->request->request('city');
        $area = $this->request->request('area');
        $school = $this->request->request('school');
        $user_id = $this->request->request('user_id');
        $title = $this->request->request('title');
        $yw_score = $this->request->request('yw_score');
        $sx_score = $this->request->request('sx_score');
        $yy_score = $this->request->request('yy_score');
        $zz_score = $this->request->request('zz_score');
        $ls_score = $this->request->request('ls_score');
        $dl_score = $this->request->request('dl_score');
        $wl_score = $this->request->request('wl_score');
        $hx_score = $this->request->request('hx_score');
        $sw_score = $this->request->request('sw_score');
        $wz_score = $this->request->request('wz_score');
        $lz_score = $this->request->request('lz_score');

        // 查询该用户保存的所有学科
        $subjectDatas = Db::name('user_subject')->where(['user_id'=>$user_id])->field('subject_id')->select();
        // 判断各个学科是否保存两次历史成绩
        foreach($subjectDatas as $row){
            $table = model('common')->getSubjectTable($row['subject_id']);
            $twoData = Db::name($table)->where(['user_id'=>$user_id])->order('id desc')->limit(2)->select();
            if(count($twoData) < 2) $this->error(__('各个学科至少有两次历史成绩才能预测成绩'));
        }
        
        // 记录新成绩并且预估分数
        $add = model('GradesLog')->createGreadeLog($province, $city, $area, $school, $user_id, $title, $yw_score, $sx_score, $yy_score, $zz_score, $ls_score, $dl_score, $wl_score, $hx_score, $sw_score, $wz_score, $lz_score, $event='forecast');
        // $add = ['res'=>true];
        
        return json($add);
        
    }

    /**
     * 分数评语
     *
     * @param string $user_id  会员ID
     * @param string $score    分数
     * @param string $subject_id  科目 单科传1或者2等等单个数字；总分传1,2,3
     */
    public function scoreRemark()
    {
        $user_id = $this->request->request('user_id');
        $score = $this->request->post("score");
        $str_subject = $this->request->post("subject_id");

        if (!$user_id || !$score || !$str_subject) {
            $this->error(__('Invalid parameters'));
        }
        $str_subject = rtrim($str_subject, ",");
        $subjectArr = explode(",", $str_subject);
        $result = model('ForecastScore')->getScoreRemark($user_id, $score, $subjectArr);
        $this->success('OK',$result);
    }


    /**
     * 估分测试接口（测试用）
     *
     * @param string $avg  平均分
     * @param string $score 分数
     * @param string $total 总分
     */
    public function testForecastScore()
    {
        $avg = $this->request->request('avg');
        $score = $this->request->request('score');
        $total = $this->request->request('total');
        $result = model('ForecastScore')->forecastScoreFormula($avg, $score, $total);
        return json($result);
    }

}

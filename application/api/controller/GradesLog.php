<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 成绩记录
 */
class GradesLog extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 设置/修改满分
     *
     * @param string $user_id  会员ID 1,2,3
     * @param string $subject_id 学科ID 100,100,100
     * @param string $full 满分
     */
    public function setFull()
    {
        $user_id = $this->request->request('user_id');
        $str_subject = $this->request->request('subject_id');
        $str_full = $this->request->request('full');
        if (!$user_id || !$str_subject || !$str_full) {
            $this->error(__('Invalid parameters'));
        }
        $str_subject = rtrim($str_subject, ",");
        $subjectArr = explode(",", $str_subject);
        $str_full = rtrim($str_full, ",");
        $fullArr = explode(",", $str_full);

        $fullInfo = Db::name('user_subject')->where(['user_id'=>$user_id])->column('subject_id,full');

        if($fullInfo){
            Db::name('user_subject')->where(['user_id'=>$user_id])->delete();
            $result = model('GradesLog')->createFull($user_id, $subjectArr, $fullArr);
            $msg = '修改';
        }else{
            $result = model('GradesLog')->createFull($user_id, $subjectArr, $fullArr);
            $msg = '设置';
        }
        
        if ($result !== false) {
            $this->success($msg.'成功');
        } else {
            $this->error(__($msg.'失败'));
        }
    }

    /**
     * 添加成绩
     *
     * @param string $province  省份
     * @param string $city 城市
     * @param string $area 县区
     * @param string $school 学校
     * @param string $user_id 会员ID
     * @param string $name 成绩名称
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
    public function addGreades()
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

        $result = model('GradesLog')->createGreadeLog($province, $city, $area, $school, $user_id, $title, $yw_score, $sx_score, $yy_score, $zz_score, $ls_score, $dl_score, $wl_score, $hx_score, $sw_score, $wz_score, $lz_score);

        if($result['res']){
            $this->success($result['msg']);
        }else{
            $this->error($result['msg']);
        }
    }

    /**
     * 获取成绩记录列表
     *
     * @param string $user_id  会员ID
     * @param string $page  页码
     * @param string $num  数量
     */
    public function getGradesLogList()
    {
        $user_id = $this->request->request('user_id');
        $page = $this->request->post("page") ? $this->request->post("page") : 1;
        $num = $this->request->post("num") ? $this->request->post("num") : 10;
        if (!$user_id) {
            $this->error(__('Invalid parameters'));
        }
        $result = model('GradesLog')->getGradesLogList($user_id, $page, $num);
        $this->success('OK',$result);
    }    

    /**
     * 获取成绩记录详情
     *
     * @param string $id  页码
     * @param string $user_id  会员ID
     */
    public function getGradesLogDetails()
    {
        $id = $this->request->request('id');
        $user_id = $this->request->request('user_id');
        if (!$id || !$user_id) {
            $this->error(__('Invalid parameters'));
        }
        
        $result = model('GradesLog')->getGradesLogDetails($id, $user_id);

        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
          
    } 
        
    /**
     * 修改录入成绩
     *
     * @param string $id  ID
     * @param string $user_id  会员ID
     * @param string $name 成绩名称
     * @param string $subject_id 成绩
     * @param string $score 成绩
     */
    public function changeGradesLog()
    {
        $id = $this->request->request('id');
        $user_id = $this->request->request('user_id');
        $name = $this->request->post("name");
        $subject_id = $this->request->post("subject_id");
        $score = $this->request->post("score");
        if (!$id || !$user_id || !$name || !$subject_id || !$score) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('GradesLog')->changeGradesLog($id, $user_id, $name, $subject_id, $score);

        if($result['res']){
            $this->success('OK',$result['data']);
        }else{
            $this->error($result['msg']);
        }

    }

}

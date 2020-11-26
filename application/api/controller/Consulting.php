<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 预约咨询
 */
class Consulting extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 预测学校
     *
     * @param string $user_id 会员ID
     * @param string $name  姓名
     * @param string $mobile 手机号
     * @param string $score 总分
     * @param string $rank 位次
     * @param string $type  文科理科
     * @param string $batch  批次
     * @param string $yw  语文成绩
     * @param string $sx  数学成绩
     * @param string $yy  英语成绩
     * @param string $zh  综合成绩
     * @param string $date  预约时间
     */
    public function consulting()
    {
        $user_id = $this->request->request('user_id');
        $name = $this->request->request('name');
        $mobile = $this->request->request('mobile');
        $score = $this->request->request('score');
        $rank = $this->request->request('rank');
        $type = $this->request->request('type');
        $batch = $this->request->request('batch');
        $yw = $this->request->request('yw');
        $sx = $this->request->request('sx');
        $yy = $this->request->request('yy');
        $zh = $this->request->request('zh');
        $date = $this->request->request('date');

        if (!$user_id || !$name || !$mobile || !$score || !$rank || !$type || !$batch || !$yw || !$sx || !$yy || !$zh || !$date) {
            $this->error(__('Invalid parameters'));
        }

        $id = Db::name('consulting')->where(['date'=>$date])->value('id');
        if($id){
            $this->error('该时段已有人预约，请换个时间'); 
        }

        $result = model('Consulting')->putConsulting($user_id, $name, $mobile, $score, $rank, $type, $batch, $yw, $sx, $yy, $zh, $date);
        
        if($result){
            $this->success('预约成功');
        }else{
            $this->error('预约失败');
        }
        
    }

    /**
     * 咨询记录
     *
     * @param string $user_id 会员ID
     */
    public function consultingLog()
    {
        $user_id = $this->request->request('user_id');

        if (!$user_id) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('Consulting')->getConsultingLog($user_id);

        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
        
    }

    /**
     * 咨询详情
     *
     * @param string $id ID
     */
    public function consultingDetails()
    {
        $id = $this->request->request('id');

        if (!$id) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('Consulting')->getConsultingDetails($id);

        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
        
    }

    

}

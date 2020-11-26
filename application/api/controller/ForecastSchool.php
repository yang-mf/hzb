<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 预测学校
 */
class ForecastSchool extends Api
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
     * @param string $sign  标记 now-现在估分 log-记录估分
     * @param string $score 分数
     * @param string $type 文科理科
     * @param string $batch 批次
     * @param string $province  省份
     * @param string $category  普通/艺术/体育
     * @param string $page  页码
     * @param string $num  数量
     */
    public function forecast()
    {
        $user_id = $this->request->request('user_id');
        $sign = $this->request->request('sign');
        $score = $this->request->request('score');
        $type = $this->request->request('type');
        $batch = $this->request->request('batch');
        $province = $this->request->request('province');
        $category = $this->request->request('category');
        $page = $this->request->post("page") ? $this->request->post("page") : 1;
        $num = $this->request->post("num") ? $this->request->post("num") : 10;

        if (!$user_id || !$sign || !$score || !$type || !$batch || !$province || !$category) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('ForecastSchool')->getForecastSchool($user_id, $sign, $score, $type, $batch, $province, $category, $page, $num);
        
        if($result['res']){
            $this->success('OK',$result['data']);
        }else{
            $this->error($result['msg']);
        }
        
    }

    /**
     * 往年分数线
     *
     * @param string $school 学校
     * @param string $type 文科理科
     * @param string $batch 批次
     * @param string $province  省份
     * @param string $category  普通/艺术/体育
     */
    public function formerGradeLine()
    {
        $school = $this->request->request('school');
        $type = $this->request->request('type');
        $batch = $this->request->request('batch');
        $province = $this->request->request('province');

        if (!$school || !$type || !$batch || !$province) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('ForecastSchool')->getFormerGradeLine($school, $type, $batch, $province);

        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
        
    }

    

}

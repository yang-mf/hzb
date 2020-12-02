<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use app\common\controller\Frontend;
use app\common\model\Config;

class Test extends Frontend
{
    /**
     * 测试模块
     *
     * @return \think\Response
     */
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }
    //首页展示
    public function test()
    {

        return $this->view->fetch('test/test');
    }
    //获取学生位次信息
    /*
     * $score  分数
     * $status 状态
     * $year   年份，测试版有，正式版直接在model中获取今年
     * $type   文理科
     * $batch  批次
     * $result 获取用来展示的数据
     */
    public function get_info()
    {
        //分数
        $score = $this->request->request('score');
        //状态
        $status = $this->request->request('status');
        //年份，测试版有，正式版直接在model中获取今年
        $year = $this->request->request('year');
        //文理科
        $type = $this->request->request('type');
        //批次
        $batch = $this->request->request('batch');
        $page = $this->request->request('page');
        if(empty($score) && empty($type) ){
            $score = session('score');
            $year = session('year');
            $type = session('type');
            $batch = session('batch');
            $status = session('status');
        }else{
            session('score',$score);
            session('status',$status);
            session('year',$year);
            session('type',$type);
            session('batch',$batch);
        }
        //获取数据
        if(!empty($year)){
            $year = date($year);
            $result = model('TestHzbDataBatchAgent')->getBatchData($score,$status,$type,$year,$batch,$page);
        }else{
            $year = date('Y');
            $result = model('TestHzbDataBatchAgentYear')->getBatchData($score,$status,$type,$year,$batch,$page);
        }
        $this->assign('info',$result['info']);
        return $this->view->fetch('test/show');
    }
}

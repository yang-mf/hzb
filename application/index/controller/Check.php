<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Config;

/**
 * 获取学生分数位次
 */
class Check extends Frontend
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
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
        setcookie('score',$score);
        setcookie('status',$status);
        setcookie('year',$year);
        setcookie('type',$type);
        setcookie('batch',$batch);
        if(empty($score) && empty($status)&& empty($year)&& empty($type)&& empty($batch)){
            $score = cookie('score');
            $year = cookie('year');
            $type = cookie('type');
            $batch = cookie('batch');
        }
        //获取数据
        if(!empty($year)){
            $result = model('HzbDataBatch')->getBatchData($score,$status,$type,$year,$batch);
        }else{
            $year = date('Y');
            $result = model('HzbDataBatchYear')->getBatchData($score,$status,$type,$year,$batch,$page);
        }
//        var_dump($result['info']);die;
        $this->assign('object',$result['object']);
        $this->assign('info',$result['info']);
        return $this->view->fetch('test/show');
    }

    public function index()
    {
        //自己写的页面
        return $this->view->fetch('test/index');
        //原页面
//        return $this->view->fetch();
    }
    public function lay()
    {
        //自己写的页面
        return $this->view->fetch('lay/layout');
        //原页面
//        return $this->view->fetch();
    }
    public function test()
    {
        //自己写的页面
        return $this->view->fetch('test/atest');
        //原页面
//        return $this->view->fetch();
    }
}

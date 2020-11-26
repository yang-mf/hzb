<?php

namespace app\index\controller;

use app\common\controller\Api;
use think\Config;
use think\Exception;
use think\Db;

/**
 * 获取学生分数位次
 */
class Check extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    //获取学生位次信息
    public function get_info()
    {
        //分数
        $score = $this->request->request('score');
        //状态
        $status = $this->request->request('status');
        //年份，测试版有，正式版直接获取今年
        $year = $this->request->request('year');
        //文理科
        $type = $this->request->request('type');
        //获取数据
        $result = model('HzbData2016Batch')->getBatchData($score,$status,$type,$year);
        var_dump($result);
        die;
    }
}

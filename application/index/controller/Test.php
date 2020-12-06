<?php

namespace app\index\controller;

use app\index\model\TestHzbDataSelectBatch;
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
            $result = model('TestHzbDataBatch')->getBatchData($score,$status,$type,$year,$batch,$page);
        }else{
            $year = date('Y');
            $result = model('TestHzbDataBatchYear')->getBatchData($score,$status,$type,$year,$batch,$page);
        }
        $this->assign('info',$result['info']);
        return $this->view->fetch('test/testshow');
    }
    public function get_ajax_info()
    {
        $score =$_POST['score'];
        $batch =$_POST['batch'];
        $type =$_POST['type'];
        $year =$_POST['year'];
//        var_dump($score);die;

        if(!($score)){
            return $data=['code'=>4,'message'=>'请输入正确的分数'];
        }
//                var_dump($score);die;

        if(empty($score) && empty($type) ){
            $score = session('score');
            $year = session('year');
            $type = session('type');
            $batch = session('batch');
        }else{
            session('score',$score);
            session('year',$year);
            session('type',$type);
            session('batch',$batch);
        }
        //获取数据
        if(!empty($year)){
            $year = date($year);
            $result = model('TestHzbDataBatch')->getBatchData($score,$type,$year,$batch);
        }else{
            $year = date('Y');
            $result = model('TestHzbDataBatchYear')->getBatchData($score,$type,$year,$batch);
        }
//        var_dump($result['info']);die;
        $data = ['code'=>1,'info'=>$result['info']];
        return $data;
    }
    //输入专业
    public function get_select_info(){
        $score =$_POST['score'];
        $batch =$_POST['batch'];
        $type =$_POST['type'];
        $year =$_POST['year'];
        $show_info =$_POST['show_info'];
        $show_info = json_decode($show_info);
        $show_info = json_decode( json_encode( $show_info),true);
        $sta_profession =$_POST['sta_profession'];
        $sta_school =$_POST['sta_school'];
        $profession =$_POST['profession'];
        $pp_type =$_POST['pp_type'];
        if (!empty($sta_profession)) {
            $show_info = model('TestHzbDataSelectBatch')->check_sta_profession($show_info,$sta_profession,$profession);
        }
        //学校名称搜索
        if (!empty($sta_school)) {
            $show_info = model('TestHzbDataSelectBatch')->check_sta_school($show_info,$sta_school);
        }
        //办学类型搜索
        if (!empty($pp_type)) {
            $show_info = model('TestHzbDataSelectBatch')->check_pp_type($show_info,$pp_type);

        }
//        var_dump($show_info);die;
        if(empty($show_info))
        {
            $show_info=['code'=>2,'message'=>'请重新输入'];
        }else
        {
            $show_info=['code'=>1,'info'=>$show_info];
        }
        return $show_info;
    }

    //获取部分profession_name数据
    public function get_profession_name()
    {
        $profession=$_POST['profession'];
        $result = model('TestHzbDataCategory')->getProfessionData($profession);
        return $result;
    }
    //根据客户输入的关键字查询获取全部profession_name数据
    public function get_select_profession_name()
    {
        $profession=$_POST['profession'];
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')->getProfessionSelectData($word,$profession);
        return $result;
    }
    //获取部分school_name数据
    public function get_school_name()
    {
        $result = model('TestHzbDataCategory')->getSchoolNameData();
        return $result;
    }
    //根据客户输入的关键字查询获取全部school_name数据
    public function get_select_school_name()
    {
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')->getSchoolNameSelectData($word);
        return $result;
    }
}

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
    /**
     * 根据用户输入的条件获取用来展示的数据
     * $score  分数
     * $year   年份，
     * $type   文理科
     * $batch  批次
     * $data 获取用来展示的数据
     */
    public function get_ajax_info()
    {
        $score =$_POST['score'];
        $batch =$_POST['batch'];
        $type =$_POST['type'];
        $year =$_POST['year'];
        if(!($score)){
            return $data=['code'=>2,'message'=>'请输入正确的分数'];
        }
        if(!($type)){
            return $data=['code'=>2,'message'=>'请选择文理科'];
        }
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
        $year = date($year);
        $result = model('TestHzbDataBatch')->getBatchData($score,$type,$year,$batch);
        
        if(empty($result))
        {
            return $data=['code'=>2,'message'=>'请重新输入分数，或选择批次'];
        }
        $M_data = $this->groupByInitials($result['info'], 'school_province');

        $S_data = $this->province($M_data);
//        var_dump($S_data);die;

        $province = $this->get_province($S_data);
        if(empty($S_data))
        {
            return $data=['code'=>2,'message'=>'请重新选择'];
        }
        $data = ['code'=>1,
            'show_info'=>$S_data,
            'info'=>$result['info'],
            'province'=>$province,
            'school_nature'=>$result['school_nature'],
            'school_num'=>$result['school_num'],
            'school_type'=>$result['school_type'],
        ];
        return $data;
    }
    /**
     * 附加条件搜索
     * $info            //$info传参为第一次分数和位次搜索之后得出，一直不变
     * $show_info       //$show_info传参为搜索之后得出的数据
     * $province        //$province传参为搜索之后得出的用于排列的省份数据
     */
    public function get_select_info(){
        $info = $_POST['info'];
        $info = json_decode($info);
        $info = json_decode( json_encode( $info),true);
        $show_info = $info;
        $checked_province = $_POST['checked_province'];
        $sta_profession = $_POST['sta_profession'];
        $sta_school = $_POST['sta_school'];
        $school_nature = $_POST['school_nature'];
        $checked_school_type = $_POST['checked_school_type'];
//        var_dump($checked_school_type);die;
//        var_dump($checked_school_type);die;
        $province = $_POST['province'];
        //专业名称搜索
        if (!empty($sta_profession)) {
            $show_info = model('TestHzbDataSelectBatch')
                ->check_sta_profession($show_info,$sta_profession,$school_nature);
        }
        //学校名称搜索
        if (!empty($sta_school)) {
            $show_info = model('TestHzbDataSelectBatch')->check_sta_school($show_info,$sta_school);
        }
        //办学类型搜索
        if (!empty($checked_school_type)) {
            $show_info = model('TestHzbDataSelectBatch')
                ->check_checked_province_name($show_info,$checked_school_type);
        }
        //学校省份搜索
        if (!empty($checked_province)) {
            $M_data = $this->groupByInitials($show_info, 'school_province');
            $province_show_info = $this->province($M_data);
            $province_show_info = model('TestHzbDataSelectBatch')
                ->check_province($province_show_info,$checked_province);
            $province = $this->get_province($province_show_info);
        }else
        {
            $M_data = $this->groupByInitials($show_info, 'school_province');
            $province_show_info = $this->province($M_data);
            $province = $this->get_province($province_show_info);
        }
        if(empty($province_show_info))
        {
            return $data=[
                'code'=>2,
                'message'=>'请重新选择',
            ];
        }
        $data = ['code'=>1,
            'show_info'=>$province_show_info,
            'info'=>$info,
            'province'=>$province
        ];
        return $data;
    }
    /**
     * 获取profession_name数据
     */
    public function get_profession_name()
    {
        $school_nature=$_POST['school_nature'];
        $school_num=$_POST['school_num'];
        $result = model('TestHzbDataCategory')->getProfessionData($school_nature,$school_num);
        return $result;
    }
    /**
     * 根据客户输入的关键字查询获取全部profession_name数据
     *
     */
    public function get_select_profession_name()
    {
        $school_nature=$_POST['school_nature'];
        $school_num=$_POST['school_num'];
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')
            ->getProfessionSelectData($word,$school_nature,$school_num);
        return $result;
    }
    /**
     * 获取school_name数据
     */
    public function get_school_name()
    {
        $school_num=$_POST['school_num'];
        $result = model('TestHzbDataCategory')->getSchoolNameData($school_num);
        return $result;
    }
    /**
     * 根据客户输入的关键字查询获取全部school_name数据
     */
    public function get_select_school_name()
    {
        $school_num=$_POST['school_num'];
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')->getSchoolNameSelectData($word,$school_num);
        return $result;
    }
    /**
     * 省份排序（来源于网络，谨慎修改）
     */
    public function province($info)
    {
        $new_info = [];
        foreach ($info as $k => $v)
        {
            foreach ($v as $kk => $vv)
            {
                $new_info[$vv['school_province']][]=$vv;
            }
        }
        return $new_info;
    }
    /**
     * 获取省份名称，用于复选框展示
     */
    public function get_province($S_data)
    {
        $province = [];
        foreach ($S_data as $k => $v)
        {
            $province[]['province_name']=$k;
        }
        return $province;
    }
    /**
     * 二维数组根据首字母分组排序
     * @param  array  $data      二维数组
     * @param  string $targetKey 首字母的键名
     * @return array             根据首字母关联的二维数组
     */
    public function groupByInitials(array $data, $targetKey = 'name')
    {
        $data = array_map(function ($item) use ($targetKey) {
            return array_merge($item, [
                'initials' => $this->getInitials($item[$targetKey]),
            ]);
        }, $data);
        $data = $this->sortInitials($data);
        return $data;
    }
    /**
     * 按字母排序
     * @param  array  $data
     * @return array
     */
    public function sortInitials(array $data)
    {
        $sortData = [];
        foreach ($data as $key => $value) {
            $sortData[$value['initials']][] = $value;
        }
        ksort($sortData);
        return $sortData;
    }
    /**
     * 获取首字母
     * @param  string $str 汉字字符串
     * @return string 首字母
     */
    public function getInitials($str)
    {
        if (empty($str)) {return '';}
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) {
            return strtoupper($str{0});
        }
        $s1  = iconv('UTF-8', 'gb2312', $str);
        $s2  = iconv('gb2312', 'UTF-8', $s1);
        $s   = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) {
            return 'A';
        }
        if ($asc >= -20283 && $asc <= -19776) {
            return 'B';
        }
        if ($asc >= -19775 && $asc <= -19219) {
            return 'C';
        }
        if ($asc >= -19218 && $asc <= -18711) {
            return 'D';
        }
        if ($asc >= -18710 && $asc <= -18527) {
            return 'E';
        }
        if ($asc >= -18526 && $asc <= -18240) {
            return 'F';
        }
        if ($asc >= -18239 && $asc <= -17923) {
            return 'G';
        }
        if ($asc >= -17922 && $asc <= -17418) {
            return 'H';
        }
        if ($asc >= -17417 && $asc <= -16475) {
            return 'J';
        }
        if ($asc >= -16474 && $asc <= -16213) {
            return 'K';
        }
        if ($asc >= -16212 && $asc <= -15641) {
            return 'L';
        }
        if ($asc >= -15640 && $asc <= -15166) {
            return 'M';
        }
        if ($asc >= -15165 && $asc <= -14923) {
            return 'N';
        }
        if ($asc >= -14922 && $asc <= -14915) {
            return 'O';
        }
        if ($asc >= -14914 && $asc <= -14631) {
            return 'P';
        }
        if ($asc >= -14630 && $asc <= -14150) {
            return 'Q';
        }
        if ($asc >= -14149 && $asc <= -14091) {
            return 'R';
        }
        if ($asc >= -14090 && $asc <= -13319) {
            return 'S';
        }
        if ($asc >= -13318 && $asc <= -12839) {
            return 'T';
        }
        if ($asc >= -12838 && $asc <= -12557) {
            return 'W';
        }
        if ($asc >= -12556 && $asc <= -11848) {
            return 'X';
        }
        if ($asc >= -11847 && $asc <= -11056) {
            return 'Y';
        }
        if ($asc >= -11055 && $asc <= -10247) {
            return 'Z';
        }
        return null;
    }
}

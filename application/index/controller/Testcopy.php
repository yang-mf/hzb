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
    //测试方法
    public function data()
    {
        $result = model('TestHzbDataChangeTimes')->get_times();
        var_dump($result);
    }
    //首页展示
    public function test()
    {
        $batch[]=['batch_name'=>'一批','batch_value'=>1];
        $batch[]=['batch_name'=>'二批','batch_value'=>2];
        $batch[]=['batch_name'=>'专科','batch_value'=>4];
        $profession = model('TestHzbDataGetIndexAll')->get_profession();
        $province = model('TestHzbDataGetIndexAll')->get_province();
        $school_type = model('TestHzbDataGetIndexAll')->get_school_type();
        $school_name = model('TestHzbDataGetIndexAll')->get_school_name();
        $this->view->assign('all_batch_name_info',json_encode($batch,JSON_UNESCAPED_UNICODE));
        $this->view->assign('all_profession_name_info',json_encode($profession,JSON_UNESCAPED_UNICODE));
        $this->view->assign('all_province_name_info',json_encode($province,JSON_UNESCAPED_UNICODE));
        $this->view->assign('all_school_type_name_info',json_encode($school_type,JSON_UNESCAPED_UNICODE));
        $this->view->assign('all_school_name_info',json_encode($school_name,JSON_UNESCAPED_UNICODE));
        return $this->view->fetch('test/test');
    }
    //首页展示
    public function testcopy()
    {
        return $this->view->fetch('test/testcopy');
    }
    //判断批次
    public function check_batch()
    {
        $score =$_POST['score'];
        $type =$_POST['type'];
        $year =$_POST['year'];
        if( $score && $type && $year ) {
            $batch = model('TestHzbDataGetIndexAll')->get_batch($score,$type,$year);
            return $batch;
        }
        if($score>=500) {
            $batch[]=['batch_name'=>'一批','batch_value'=>1];
            $batch[]=['batch_name'=>'二批','batch_value'=>2];
            $batch[]=['batch_name'=>'专科','batch_value'=>4];
            return $batch=['batch_code'=>'1','batch_data'=>$batch];
        }else if($score<500 && $score>350){
            $batch[]=['batch_name'=>'二批','batch_value'=>2];
            $batch[]=['batch_name'=>'专科','batch_value'=>4];
            return $batch=['batch_code'=>'1','batch_data'=>$batch];
        }else if($score<=350 && $score>=180){
            $batch[]=['batch_name'=>'专科','batch_value'=>4];
            return $batch=['batch_code'=>'1','batch_data'=>$batch];
        }else if($score<180){
            $batch[]=['batch_code'=>'9','message'=>'抱歉，您输入的分数暂无对于批次'];
            return $batch;
        }
    }
    /**
     * 根据用户输入的条件获取用来展示的数据
     * $score  分数
     * $year   年份，
     * $type   文理科
     * $batch  批次
     * @return array  $data 获取用来展示的数据
     */
    public function get_ajax_info()
    {
        $score =$_POST['score'];
        $batch =$_POST['batch'];
        $type =$_POST['type'];
        $year =$_POST['year'];
        $rank =$_POST['rank'];
        $the_show_year =$_POST['the_show_year'];
        if(!$score){
            return $data=['code'=>2,'message'=>'请输入正确的分数'];
        }
//        if(!$rank){
//            return $data=['code'=>2,'message'=>'请输入位次'];
//        }
        if(!$type){
            return $data=['code'=>2,'message'=>'请选择文理科'];
        }
        if( !$year ) {
            return $data=['code'=>2,'message'=>'请选择年份'];
        }
//        if( !$batch ) {
//            return $data=['code'=>2,'message'=>'请选择批次'];
//        }
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
        $result = model('TestHzbDataBatch')->getBatchData($score,$type,$the_show_year,$rank,$year,$batch);

        if(empty($result))
        {
            return $data=['code'=>2,'message'=>'请重新输入分数，或选择批次'];
        }
        if($result['code'] == 2) {
            return $data=['code'=>2,'message'=>'请重新输入分数，或选择批次'];
        }
        $info = $result['info'];
        $checked_province = $_POST['checked_province'];
        $sta_profession = $_POST['sta_profession'];
        $sta_school = $_POST['sta_school'];
        $checked_school_type = $_POST['checked_school_type'];
        $the_show_year = $_POST['the_show_year'];
        $school_nature = $result['school_nature'];
        foreach ($info as $key => $value) {
            $school_num[] = $value['school_num'];
        }
        $batch = model('TestHzbDataTheShowYear')
            ->TheShowYear( $score, $year, $type, $batch, $the_show_year);
        if( $the_show_year ) {
            foreach ($info as $ok => $ov) {
                $info[$ok]['show_year'] = [];
            }
            $show_info = model('TestHzbDataTheShowYear')
                ->GetYearInfo($school_num
                    ,$type,$batch,$info,$year,$the_show_year);
        }else {
            $show_info = model('TestHzbDataTheShowYear')
                ->GetYearInfo($school_num
                    ,$type,$batch,$info,$year,$the_show_year);
        }
        //专业名称搜索
        if (!empty($sta_profession)) {
            $show_info = model('TestHzbDataSelectBatch')
                ->check_sta_profession($show_info,$sta_profession,$school_nature);
            if($show_info['code']==1) {
                $show_info = $show_info['show_new_info'];
            }else{
                return $show_info;
            }
        }
        //学校名称搜索
        if (!empty($sta_school)) {
            $show_info = model('TestHzbDataSelectBatch')->check_sta_school($show_info,$sta_school);
            if($show_info['code']==1) {
                $show_info = $show_info['show_new_info'];
            }else{
                return $show_info;
            }
        }
        //办学类型搜索
        if (!empty($checked_school_type)) {
            $show_info = model('TestHzbDataSelectBatch')
                ->check_checked_province_name($show_info,$checked_school_type);
            if($show_info['code']==1) {
                $show_info = $show_info['show_new_info'];
            }else{
                return $show_info;
            }
        }
        //学校省份搜索
        if (!empty($checked_province)) {
            $M_data = $this->groupByInitials($show_info, 'school_province');
            $province_show_info = $this->province($M_data);
            $province_show_info = model('TestHzbDataSelectBatch')
                ->check_province($province_show_info,$checked_province);
            if($province_show_info['code']==1) {
                $province_show_info = $province_show_info['show_new_info'];
            }else{
                return $province_show_info;
            }

            $province = $this->get_province($province_show_info);
            foreach ($province_show_info as $key => $value) {
                foreach ($value as $k => $v) {
                    $school_name[] = [
                        'school_name'=>$v['school_name'],
                        'school_num'=>$v['school_num'],
                    ];
                    $show_school_num[] = $v['school_num'];
                    $new_school_type[]=$v['school_type'];
                }
            }
            $school_name = array_column($school_name,null,'school_num');
            $school_name = array_values($school_name);
        } else {
            foreach ($show_info as $key => $value) {
                $school_name[] = [
                    'school_name'=>$value['school_name'],
                    'school_num'=>$value['school_num'],
                ];
                $new_school_type[]=$value['school_type'];
                $show_school_num[] = $value['school_num'];
            }
            $school_name = array_column($school_name,null,'school_num');
            $school_name = array_values($school_name);
            $M_data = $this->groupByInitials($show_info, 'school_province');
            $province_show_info = $this->province($M_data);
            $province = $this->get_province($province_show_info);
        }
        if(empty($province_show_info)) {
            return  $show_info=['code'=>2,'message'=>'请重新输入信息'];
        }
        $new_school_type=array_unique($new_school_type);
        foreach ( $new_school_type as $k => $v) {

            if($v=='公办'){
                $school_type[]=['school_type'=>$v,'school_type_num'=>1];
            }
            if($v=='民办'){
                $school_type[]=['school_type'=>$v,'school_type_num'=>2];
            }
            if($v=='中外合作办学'){
                $school_type[]=['school_type'=>$v,'school_type_num'=>3];
            }
            if($v=='内地与港澳台地区合作办学'){
                $school_type[]=['school_type'=>$v,'school_type_num'=>4];
            }
        }
        $show_school_num=array_unique($show_school_num);
        if(empty($province_show_info))
        {
            return $data=['code'=>2,'message'=>'请重新选择'];
        }
//        var_dump($school_type);die;
        $data = ['code'=>1,
            'show_info'=>$province_show_info,
//            'info'=>$result['info'],
            'info'=>$show_info,
            'province'=>$province,
            'school_nature'=>$result['school_nature'],
            'school_num'=>$show_school_num,
            'school_name'=>$school_name,
            'school_type'=>$school_type,
        ];
        return $data;
    }
    /**
     * 附加条件搜索
     * $info            //$info传参为第一次分数和位次搜索之后得出，一直不变
     * $show_info       //$show_info传参为搜索之后得出的数据
     * $province        //$province传参为搜索之后得出的用于排列的省份数据
     * @return array    $data 搜索条件之后，获取用来展示的数据
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
        $the_show_year = $_POST['the_show_year'];
        $batch = $_POST['batch'];
        $type = $_POST['type'];
        $year = $_POST['year'];
        $score = $_POST['score'];
        foreach ($info as $key => $value) {
            $school_num[] = $value['school_num'];
        }
        $batch = model('TestHzbDataTheShowYear')
            ->TheShowYear( $score, $year, $type, $batch, $the_show_year);
        if( $the_show_year ) {
            foreach ($info as $ok => $ov) {
                $info[$ok]['show_year'] = [];
            }
            $show_info = model('TestHzbDataTheShowYear')
                ->GetYearInfo($school_num
                    ,$type,$batch,$info,$year,$the_show_year);
        }else {
            $show_info = model('TestHzbDataTheShowYear')
                ->GetYearInfo($school_num
                    ,$type,$batch,$info,$year,$the_show_year);
        }
        //专业名称搜索
        if (!empty($sta_profession)) {
            $show_info = model('TestHzbDataSelectBatch')
                ->check_sta_profession($show_info,$sta_profession,$school_nature);
            if($show_info['code']==1) {
                $show_info = $show_info['show_new_info'];
            }else{
                return $show_info;
            }
        }
        //学校名称搜索
        if (!empty($sta_school)) {
            $show_info = model('TestHzbDataSelectBatch')->check_sta_school($show_info,$sta_school);
            if($show_info['code']==1) {
                $show_info = $show_info['show_new_info'];
            }else{
                return $show_info;
            }
        }
        //办学类型搜索
        if (!empty($checked_school_type)) {
            $show_info = model('TestHzbDataSelectBatch')
                ->check_checked_province_name($show_info,$checked_school_type);
            if($show_info['code']==1) {
                $show_info = $show_info['show_new_info'];
            }else{
                return $show_info;
            }
        }
        //学校省份搜索
        if (!empty($checked_province)) {
            $M_data = $this->groupByInitials($show_info, 'school_province');
            $province_show_info = $this->province($M_data);
            $province_show_info = model('TestHzbDataSelectBatch')
                ->check_province($province_show_info,$checked_province);
            if($province_show_info['code']==1) {
                $province_show_info = $province_show_info['show_new_info'];
            }else{
                return $province_show_info;
            }

            $province = $this->get_province($province_show_info);
            foreach ($province_show_info as $key => $value) {
                foreach ($value as $k => $v) {
                    $school_name[] = [
                        'school_name'=>$v['school_name'],
                        'school_num'=>$v['school_num'],
                    ];
                    $show_school_num[] = $v['school_num'];
                    $school_type[]=$v['school_type'];
                }
            }
            $school_name = array_column($school_name,null,'school_num');
            $school_name = array_values($school_name);
        } else {
            foreach ($show_info as $key => $value) {
                $school_name[] = [
                    'school_name'=>$value['school_name'],
                    'school_num'=>$value['school_num'],
                ];
                $school_type[]=$value['school_type'];
                $show_school_num[] = $value['school_num'];
            }
            $school_name = array_column($school_name,null,'school_num');
            $school_name = array_values($school_name);
            $M_data = $this->groupByInitials($show_info, 'school_province');
            $province_show_info = $this->province($M_data);
            $province = $this->get_province($province_show_info);
        }
        if(empty($province_show_info)) {
            return  $show_info=['code'=>2,'message'=>'请重新输入信息'];
        }
        $school_type=array_unique($school_type);
        $show_school_num=array_unique($show_school_num);
        if( $sta_school ) {
            $name = $sta_school['school_name'];
            $res = strpos($name,"（");
            if( $res ) {
                $data = ['code' => 3,
                    'show_info' => $province_show_info,
                    'school_num' => $show_school_num,
                    'school_name' => $school_name,
                    'school_type' => $school_type,
                    'info' => $info,
                    'province' => $province,
                ];
            }
        } else  {
            $data = ['code'=>1,
                'show_info'=>$province_show_info,
                'school_num'=>$show_school_num,
                'school_name'=>$school_name,
                'school_type'=>$school_type,
                'info'=>$info,
                'province'=>$province
            ];
        }
        return $data;
    }
    /**
     * 获取profession_name数据
     */
    public function get_profession_name()
    {
        $school_nature=$_POST['school_nature'];
        $school_num=$_POST['school_num'];
        $school_num=array_unique($school_num);
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')->getProfessionData($school_nature,$school_num,$word);
        return $result;
    }
    /**
     * 获取school_name数据
     */
    public function get_school_name()
    {

        $school_num=$_POST['school_num'];
        $school_num=array_unique($school_num);
        $result = model('TestHzbDataCategory')->getSchoolNameData($school_num);
        return $result;
    }
    /**
     * 根据客户输入的关键字查询获取全部school_name数据
     */
    public function get_select_school_name()
    {
        $info = $_POST['info'];
        $info = json_decode($info);
        $info = json_decode( json_encode( $info),true);
        $checked_province = $_POST['checked_province'];
        $checked_school_type = $_POST['checked_school_type'];
        $school_num=$_POST['school_num'];
        $school_num=array_unique($school_num);
        $score=$_POST['score'];
        $batch=$_POST['batch'];
        $type=$_POST['type'];
        $year=$_POST['year'];
        $word=$_POST['word'];
        $result = model('TestHzbDataCategory')->getSchoolNameSelectData($word,
            $school_num, $score, $batch, $type, $year, $info, $checked_province, $checked_school_type);
        return $result;
    }
    /**
     * 省份排序
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
            $province[]['school_province']=$k;
        }
        return $province;
    }
    /**
     * 二维数组根据首字母分组排序（来源于网络，谨慎修改）
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

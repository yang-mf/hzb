<?php

namespace app\index\controller;

use app\index\model\TestHzbDataSelectBatch;
use think\Controller;
use think\Request;
use app\common\controller\Frontend;
use app\common\model\Config;

class Batch extends Frontend
{
    /**
     * 测试模块
     *
     * @return \think\Response
     */
    protected $noNeedLogin = '*';
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
        $this->view->assign('all_batch_name_info',json_encode($batch,JSON_UNESCAPED_UNICODE));
        return $this->view->fetch('test/testshow');
    }
    public function getSelectAllInfo() {
        $profession = model('TestHzbDataGetIndexAll')->get_profession();
        $province = model('TestHzbDataGetIndexAll')->get_province();
        $school_type = model('TestHzbDataGetIndexAll')->get_school_type();
        $school_name = model('TestHzbDataGetIndexAll')->get_school_name();
        $data = [
            'all_profession_name_info'  =>$profession,
            'all_province_name_info'    =>$province,
            'all_school_type_name_info' =>$school_type,
            'all_school_name_info'      =>$school_name
        ];
        return $data;
    }
    //首页展示
    public function testcopy()
    {
        return $this->view->fetch('test/testcopy');
    }
    //判断批次
    public function check_batch()
    {
        $score = $_POST['score'];
        $type  = $_POST['type'];
        $year  = $_POST['year'];
        if( !$score || !$type || !$year ) {
            $batch=['batch_code'=>'8','message'=>'抱歉，请您先输入分数或文理科或年份'];
            return $batch;
        }
        if( $score && $type && $year ) {
            $batch = model('TestHzbDataGetIndexAll')->get_batch($score, $type, $year);
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
        $state_profession =$_POST['state_profession'];
        $state_school =$_POST['state_school'];
        $checked_province =$_POST['checked_province'];
        $checked_school_type =$_POST['checked_school_type'];
        $the_show_year =$_POST['the_show_year'];
        if(!$score){
            return $data=['code'=>2,'message'=>'请输入正确的分数'];
        }
        if(!$type){
            return $data=['code'=>2,'message'=>'请选择文理科'];
        }
        if( !$year ) {
            return $data=['code'=>2,'message'=>'请选择年份'];
        }
        if( !$batch ) {
            return $data=['code'=>2,'message'=>'请选择批次'];
        }
        session('batch_info',$batch);
        //获取数据
        $year   = date($year);
        //查询数据库获取数据
        $result = model('TestHzbDataBatch' )
            ->getBatchData( $score, $type, $rank, $year, $batch, $the_show_year);
        if( empty($result) ) {
            return $data = ['code' => 2,'message' => '抱歉，请重新输入分数或选择批次'];
        }
        if( $result['code'] == 2) {
            return $data = ['code' => 2,'message' => '抱歉，请重新输入分数，或选择批次'];
        }
        $show_info = $result['show_info'];
        $checked_state_profession = $state_profession;
        $checked_state_school = $state_school;

        //专业名称搜索
        if ( !empty( $state_profession ) ) {
            foreach ( $state_profession as $k => $v ) {
                $Professionnum = model('TestHzbDataCategory')
                    ->checkProfession( $v );
                if( $Professionnum == 1 ) {
                    $ProfessionInfo['und'][] = $v;
                }else if( $Professionnum == 2 ) {
                    $ProfessionInfo['spe'][] = $v;
                }
            }
            $show_info = model('TestHzbDataSelectBatch')
                ->check_sta_profession( $show_info , $ProfessionInfo);
            if( $show_info['code'] == 1 ) {
                $show_info = $show_info['show_new_info'];
            } else {
                return $show_info;
            }
        }
        //学校名称搜索
        if ( !empty($state_school) ) {
            foreach ( $state_school as $k => $v ) {
                $state_school[$k] = explode(',',$v);
            }
            foreach ($state_school as $k => $v) {
                foreach ($v as $kk => $vv) {
                    if($kk == 0 ) {
                        $a['school_num'] = $vv;
                    }
                    if($kk == 1 ) {
                        $a['school_name'] = $vv;
                    }
                    if($kk == 2 ) {
                        $a['type'] = $vv;
                    }
                    $state_school[$k]=$a;
                }
            }
            foreach ( $state_school as $k => $v ) {
//                if($v['type'] == 'undefined' ) {
//                    foreach ( $batch as $kk => $vv ) {
//                        if( $vv == 1 || $vv == 2  ) {
//                            $SchoolInfo['und'][] = $v;
//                        }else if( $vv == 4  ) {
//                            $SchoolInfo['spe'][] = $v;
//                        }
//                    }
//                    continue;
//                }
                if( $v['type'] == 1 || $v['type'] == 2  ) {
                    $SchoolInfo['und'][] = $v;
                }else if( $v['type'] == 4  ) {
                    $SchoolInfo['spe'][] = $v;
                }
            }
            $show_info = model('TestHzbDataSelectBatch' )->check_sta_school( $show_info , $SchoolInfo );
            if( $show_info['code'] == 1 ) {
                $show_info = $show_info['show_new_info'];
            }else{
                return $show_info;
            }
        }
        //办学类型搜索
        if ( !empty($checked_school_type) ) {
            $show_info = model('TestHzbDataSelectBatch')
                ->check_checked_province_name( $show_info , $checked_school_type );
            if( $show_info['code'] == 1 ) {
                $show_info = $show_info['show_new_info'];
            } else {
                return $show_info;
            }
        }
        //学校省份搜索
        if ( !empty($checked_province) ) {
            $M_data = $this->groupByInitials( $show_info , 'school_province' );

            $province_show_info = $this->province( $M_data );
            $province_show_info = model('TestHzbDataSelectBatch' )
                ->check_province( $province_show_info , $checked_province );
            if( $province_show_info['code'] == 1 ) {
                $province_show_info = $province_show_info['show_new_info'];
            } else {
                return $province_show_info;
            }
        } else {
            $M_data = $this->groupByInitials($show_info, 'school_province');
            $province_show_info = $this->province($M_data);
        }
        //公办
        foreach ( $result['school_type'] as $k => $v) {
            if( $v == '公办' ){
                $school_type[] = ['school_type_name' => $v , 'school_type_num' => 1];
            }
        }
        //民办
        foreach ( $result['school_type'] as $k => $v) {
            if( $v == '民办' ){
                $school_type[] = ['school_type_name' => $v , 'school_type_num' => 2];
            }
        }
        //中外合作办学
        foreach ( $result['school_type'] as $k => $v) {
            if( $v == '中外合作办学' ){
                $school_type[] = ['school_type_name' => $v , 'school_type_num' => 3];
            }
        }
        //内地与港澳台地区合作办学
        foreach ( $result['school_type'] as $k => $v) {
            if( $v == '内地与港澳台地区合作办学' ){
                $school_type[] = ['school_type_name' => $v , 'school_type_num' => 4];
            }
        }
        //分批次
        foreach ( $province_show_info as $k => $v ) {
            if( $k == 1 ) {
                $batch_num[]=$k;
                $batch_name = '一批次';
            }else if( $k == 2 ) {
                $batch_num[]=$k;
                $batch_name = '二批次';
            }else if( $k == 4 ) {
                $batch_num[]=$k;
                $batch_name = '专科批次';
            }
            $new_show_info[$batch_name] = $v;
        }
        $batch_num = array_unique($batch_num);
        //专业名称
        $school_num = $result['school_num'];
        foreach  ( $school_num as $k => $v ) {
            if( $k=='und' ) {
                $und_profession_name = model('TestHzbDataCategory')
                    ->getProfessionData(1,$v) ;
            }else if( $k=='spe' ) {
                $spe_profession_name = model('TestHzbDataCategory')
                    ->getProfessionData(4,$v) ;
            }
        }
        $profession_name = array_merge( $und_profession_name, $spe_profession_name );
        if( empty($province_show_info) )
        {
            return $data=['code'=>2,'message'=>'请重新选择'];
        }

        //返回值
        $data = [
            'code'=>1,
            'show_info'         => $new_show_info,
            'batch_num'         => $batch_num,
            'info'              => $result['forNextSelectInfo'],
            'profession_name'   => $profession_name,
            'province_name'     => $result['school_province'],
            'school_type'       => $school_type,
            'school_name'       => $result['school_name'],
        ];
        $user_id = $this->auth->id;
        model('TestHzbDataInsertUserChecked')
            ->InsertUserChecked($user_id, $score, $batch, $type, $year, $rank, $checked_state_profession,
        $checked_state_school, $checked_province, $checked_school_type, $the_show_year) ;
        return $data;
    }
    /**
     * 省份排序
     */
    public function province($info)
    {
        $new_info = [];
        foreach ( $info as $key => $value) {
            foreach ($value as $k => $v)
            {
                foreach ($v as $kk => $vv)
                {
                    $new_info[$key][$vv['school_province']][]=$vv;
                }
            }
        }
        return $new_info;
    }
    //info的处理，用于传值而不是展示
    public function provinceInfo($info)
    {
        $new_info = [];
        foreach ($info as $k => $v) {
            foreach ($v as $kk => $vv) {
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
        foreach ( $data as $key => $value ) {
            $data = array_map(function ($item) use ($targetKey) {
                return array_merge($item, [
                    'initials' => $this->getInitials($item[$targetKey]),
                ]);
            }, $value);
            $data = $this->sortInitials($data);
            $new_data[$key]=$data;
        }
        return $new_data;
    }
    //info的处理，用于传值而不是展示
    public function groupByInitialsInfo(array $data, $targetKey = 'name')
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

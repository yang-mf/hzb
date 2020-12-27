<?php

namespace app\index\controller;

use app\index\model\TestHzbDataSelectBatch;
use think\Controller;
use think\Db;
use think\Request;
use app\common\controller\Frontend;
use app\common\model\Config;
use PHPExcel_IOFactory;


class Test extends Frontend
{
    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    //测试方法
    public function test()
    {
        $user_id = $this->auth->id;
        $res = Db::table('yzx_hzb_user_checked_info')
            ->where('user_id', $user_id)
            ->max('check_num');
        $res = Db::table('yzx_hzb_user_checked_info')
            ->where('user_id', $user_id)
            ->where('check_num',$res)
            ->select();
        foreach ( $res as $k => $v ) {
            $year   = $v['year'];
            $score  = $v['score'];
            $batch  = $v['batch'];
            $type   = $v['type'];
            $rank   = $v['rank'];
            $the_show_year          = $v['the_show_year'];
            $state_profession       = $v['state_profession'];
            $state_school           = $v['state_school'];
            $checked_school_type    = $v['checked_school_type'];
            $checked_province       = $v['checked_province'];
        }
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

        //专业名称搜索
        if ( !empty( $state_profession ) ) {
            $state_profession = explode(',',$state_profession);
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
            $state_school = explode('+',$state_school);
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

        if( empty($province_show_info) )
        {
            return $data=['code'=>2,'message'=>'请重新选择'];
        }
        $excel = new \PHPExcel();
        $excel = $this->clientExcel($new_show_info, '', $year);
    }

    public function clientExcel($data = [], $name = 'excel', $Checkedyear)
    {

        $excel = new \PHPExcel(); //引用phpexcel
        iconv('UTF-8', 'gb2312//IGNORE', $name); //针对中文名转码
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->setTitle($name); //设置表名
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('M')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('N')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('R')->setWidth(30);
        //设置表头
        $excel->setActiveSheetIndex(0)
            ->setCellValue('A1', '')
            ->setCellValue('B1', '')
            ->setCellValue('C1', '')
            ->setCellValue('D1', '')
            ->setCellValue('E1', '')
            ->setCellValue('F1', '')
            ->setCellValue('G1', '')
            ->setCellValue('H1', '')
            ->setCellValue('I1', '')
            ->setCellValue('J1', '')
            ->setCellValue('K1', '')
            ->setCellValue('L1', '')
            ->setCellValue('M1', '')
            ->setCellValue('N1', '');
        $count = 2;
        $first_year = 2016;
        // 设置水平居中
        foreach ($data as $key => $value) {
            $excel->getActiveSheet()->getRowDimension('A'.( $count ))->setRowHeight(30);
            $excel->getActiveSheet()
                ->mergeCells( 'A'.( $count ).':F'.( $count ))
                ->setCellValue('A'.( $count ),$key);
            $excel->getActiveSheet()->getStyle('A'.( $count ))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $count = $count + 1;
            foreach ($value as $kk => $item) {
                $excel->getActiveSheet()->getRowDimension('A'.( $count ))->setRowHeight(15);
                $excel->getActiveSheet()
                    ->mergeCells( 'A'.( $count ).':F'.( $count ))
                    ->setCellValue('A'.( $count ),$kk);
                $excel->getActiveSheet()->getStyle('A'.( $count ))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                foreach ($item as $itemKey => $itemValue ) {
                    $count1 = $count + 1;
                    $itemValueCount = count($itemValue['show_year']);
                    if( $itemValueCount == 1 ) {
                        $excel->getActiveSheet()
                            ->mergeCells( 'G'.( $count ).':J'.( $count ))
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['the_year'] )
                            ->setCellValue('G'.( $count1 ), '计划' )
                            ->setCellValue('H'.( $count1 ), '最低分' )
                            ->setCellValue('I'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('J'.( $count1 ), '最低分与分数线差值' );
                    }
                    if( $itemValueCount == 2 ) {
                        $excel->getActiveSheet()
                            ->mergeCells( 'G'.( $count ).':J'.( $count ))
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['the_year'] )
                            ->mergeCells( 'K'.( $count ).':N'.( $count ))
                            ->setCellValue('K'.( $count ), $itemValue['show_year'][1]['the_year'] )
                            ->setCellValue('G'.( $count1 ), '计划' )
                            ->setCellValue('H'.( $count1 ), '最低分' )
                            ->setCellValue('I'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('J'.( $count1 ), '最低分与分数线差值' )
                            ->setCellValue('K'.( $count1 ), '计划' )
                            ->setCellValue('L'.( $count1 ), '最低分' )
                            ->setCellValue('M'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('N'.( $count1 ), '最低分与分数线差值' );
                    }
                    if( $itemValueCount == 3 ) {
                        $excel->getActiveSheet()
                            ->mergeCells( 'G'.( $count ).':J'.( $count ))
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['the_year'] )
                            ->mergeCells( 'K'.( $count ).':N'.( $count ))
                            ->setCellValue('K'.( $count ), $itemValue['show_year'][1]['the_year'] )
                            ->mergeCells( 'O'.( $count ).':R'.( $count ))
                            ->setCellValue( 'O'.( $count ), $itemValue['show_year'][2]['the_year'] )
                            ->setCellValue('G'.( $count1 ), '计划' )
                            ->setCellValue('H'.( $count1 ), '最低分' )
                            ->setCellValue('I'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('J'.( $count1 ), '最低分与分数线差值' )
                            ->setCellValue('K'.( $count1 ), '计划' )
                            ->setCellValue('L'.( $count1 ), '最低分' )
                            ->setCellValue('M'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('N'.( $count1 ), '最低分与分数线差值' )
                            ->setCellValue('O'.( $count1 ), '计划' )
                            ->setCellValue('P'.( $count1 ), '最低分' )
                            ->setCellValue('Q'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('R'.( $count1 ), '最低分与分数线差值' );
                    }
                    if( $itemValueCount == 4 ) {
                        $excel->getActiveSheet()
                            ->mergeCells( 'G'.( $count ).':J'.( $count ),$itemValue['show_year'][0]['the_year'])
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['the_year'] )
                            ->mergeCells( 'K'.( $count ).':N'.( $count ))
                            ->setCellValue('K'.( $count ), $itemValue['show_year'][1]['the_year'] )
                            ->mergeCells( 'O'.( $count ).':O'.( $count ))
                            ->setCellValue('O'.( $count ), $itemValue['show_year'][2]['the_year'] )
                            ->mergeCells( 'S'.( $count ).':V'.( $count ))
                            ->setCellValue('S'.( $count1 ), $itemValue['show_year'][3]['the_year'] )
                            ->setCellValue('G'.( $count1 ), '计划' )
                            ->setCellValue('H'.( $count1 ), '最低分' )
                            ->setCellValue('I'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('J'.( $count1 ), '最低分与分数线差值' )
                            ->setCellValue('K'.( $count1 ), '计划' )
                            ->setCellValue('L'.( $count1 ), '最低分' )
                            ->setCellValue('M'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('N'.( $count1 ), '最低分与分数线差值' )
                            ->setCellValue('O'.( $count1 ), '计划' )
                            ->setCellValue('P'.( $count1 ), '最低分' )
                            ->setCellValue('Q'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('R'.( $count1 ), '最低分与分数线差值' )
                            ->setCellValue('S'.( $count1 ), '计划' )
                            ->setCellValue('T'.( $count1 ), '最低分' )
                            ->setCellValue('U'.( $count1 ), '录取最低分位次' )
                            ->setCellValue('V'.( $count1 ), '最低分与分数线差值' );
                    }
                }
                $excel->getActiveSheet()->getStyle('G'.( $count ).':J'.( $count ) )->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//居中
                $excel->getActiveSheet()->getStyle('K'.( $count ).':N'.( $count ) )->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//居中
                $excel->getActiveSheet()->getStyle('O'.( $count ).':R'.( $count ) )->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//居中
                $count = $count + 1;
                $excel->getActiveSheet()
                    ->setCellValue('A'.( $count ),'省份' )
                    ->setCellValue('B'.( $count ),'城市' )
                    ->setCellValue('C'.( $count ),'院校名称' )
                    ->setCellValue('D'.( $count ),'批次' )
                    ->setCellValue('E'.( $count ),'科类' )
                    ->setCellValue('F'.( $count ),'办学类型' );
                $count = $count + 1;
                foreach ( $item as $itemKey => $itemValue ) {
                    $excel->getActiveSheet()
                        ->setCellValue('A'.( $count ), $itemValue['school_province'])
                        ->setCellValue('B'.( $count ), $itemValue['school_city'])
                        ->setCellValue('C'.( $count ), $itemValue['school_name'])
                        ->setCellValue('D'.( $count ), $itemValue['batch'].'批次')
                        ->setCellValue('E'.( $count ), $itemValue['type'] == 'reason' ? '理科' : '文科' )
                        ->setCellValue('F'.( $count ), $itemValue['school_type']);
                    $itemValueCount = count($itemValue['show_year']);
                    if( $itemValueCount == 1 ) {

                        $excel->getActiveSheet()
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['plan'] )
                            ->setCellValue('H'.( $count ), $itemValue['show_year'][0]['fraction_min'] )
                            ->setCellValue('I'.( $count ), $itemValue['show_year'][0]['ler'] )
                            ->setCellValue('J'.( $count ), $itemValue['show_year'][0]['msd'] );
                    }
                    if( $itemValueCount == 2 ) {
                        $excel->getActiveSheet()
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['plan'] )
                            ->setCellValue('H'.( $count ), $itemValue['show_year'][0]['fraction_min'] )
                            ->setCellValue('I'.( $count ), $itemValue['show_year'][0]['ler'] )
                            ->setCellValue('J'.( $count ), $itemValue['show_year'][0]['msd'] )
                            ->setCellValue('K'.( $count ), $itemValue['show_year'][1]['plan'] )
                            ->setCellValue('L'.( $count ), $itemValue['show_year'][1]['fraction_min'] )
                            ->setCellValue('M'.( $count ), $itemValue['show_year'][1]['ler'] )
                            ->setCellValue('N'.( $count ), $itemValue['show_year'][1]['msd'] );
                    }
                    if( $itemValueCount == 3 ) {
                        $excel->getActiveSheet()
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['plan'] )
                            ->setCellValue('H'.( $count ), $itemValue['show_year'][0]['fraction_min'] )
                            ->setCellValue('I'.( $count ), $itemValue['show_year'][0]['ler'] )
                            ->setCellValue('J'.( $count ), $itemValue['show_year'][0]['msd'] )
                            ->setCellValue('K'.( $count ), $itemValue['show_year'][1]['plan'] )
                            ->setCellValue('L'.( $count ), $itemValue['show_year'][1]['fraction_min'] )
                            ->setCellValue('M'.( $count ), $itemValue['show_year'][1]['ler'] )
                            ->setCellValue('N'.( $count ), $itemValue['show_year'][1]['msd'] )
                            ->setCellValue('O'.( $count ), $itemValue['show_year'][2]['plan'] )
                            ->setCellValue('P'.( $count ), $itemValue['show_year'][2]['fraction_min'] )
                            ->setCellValue('Q'.( $count ), $itemValue['show_year'][2]['ler'] )
                            ->setCellValue('R'.( $count ), $itemValue['show_year'][2]['msd'] );
                    }
                    if( $itemValueCount == 4 ) {
                        $excel->getActiveSheet()
                            ->setCellValue('G'.( $count ), $itemValue['show_year'][0]['plan'] )
                            ->setCellValue('H'.( $count ), $itemValue['show_year'][0]['fraction_min'] )
                            ->setCellValue('I'.( $count ), $itemValue['show_year'][0]['ler'] )
                            ->setCellValue('J'.( $count ), $itemValue['show_year'][0]['msd'] )
                            ->setCellValue('K'.( $count ), $itemValue['show_year'][1]['plan'] )
                            ->setCellValue('L'.( $count ), $itemValue['show_year'][1]['fraction_min'] )
                            ->setCellValue('M'.( $count ), $itemValue['show_year'][1]['ler'] )
                            ->setCellValue('N'.( $count ), $itemValue['show_year'][1]['msd'] )
                            ->setCellValue('O'.( $count ), $itemValue['show_year'][2]['plan'] )
                            ->setCellValue('P'.( $count ), $itemValue['show_year'][2]['fraction_min'] )
                            ->setCellValue('Q'.( $count ), $itemValue['show_year'][2]['ler'] )
                            ->setCellValue('R'.( $count ), $itemValue['show_year'][2]['msd'] )
                            ->setCellValue('S'.( $count ), $itemValue['show_year'][3]['plan'] )
                            ->setCellValue('T'.( $count ), $itemValue['show_year'][3]['fraction_min'] )
                            ->setCellValue('U'.( $count ), $itemValue['show_year'][3]['ler'] )
                            ->setCellValue('V'.( $count ), $itemValue['show_year'][3]['msd'] );
                    }
                    $excel->getActiveSheet()->getStyle('A'.( $count ).':V'.( $count ) )->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//居中
                    if( $itemValue['color'] == 'red' ) {
                        $color = 'FF0000';
                    }else if( $itemValue['color'] == 'blue' ) {
                        $color = '0000FF';
                    } else if( $itemValue['color'] == 'green' ) {
                        $color = '008000';
                    }
                    $excel->getActiveSheet()->getStyle( 'A'.( $count ).':V'.( $count ) )->getFont()->getColor()->setARGB($color);// 设置文字颜色
                    $count = $count + 1;
                }
                $count = $count + 1;
            }
        }
        //设置单元格边框
        $excel->getActiveSheet()->getStyle("A1:E" . (count($data) + 1))->getBorders()->getAllBorders()->setBorderStyle();
        //下载文件
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
        header('Cache-Control: max-age=0');
        $res_excel = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $res_excel->save('php://output');
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
}

<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class ForecastMajor extends Model
{

    /**
     * 预测专业
     *
     * @param string $user_id  会员ID
     * @param string $sign  标记 now-现在估分 log-记录估分
     * @param array $score 分数
     * @param array $type 类型
     * @param array $batch 批次
     * @param array $province 生源地
     * @param array $category 类别 
     * @param string $page  页码
     * @param string $num  数量
     * @return  array
     */
    public function getForecastMajor($user_id, $sign, $score, $type, $batch, $province, $category, $page, $num)
    {   
        if($sign == 'now' && $page == 1){
            $params = [
                'user_id'       =>  $user_id,
                'score'         =>  $score,
                'type'          =>  $type,
                'batch'         =>  $batch,
                'province'      =>  $province,
                'category'      =>  $category,
                'createtime'    =>  time(),
            ];
            Db::startTrans();
            try {
                Db::name('major_log')->insert($params);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
            }
        }

        // 本科提前批；本科一批；国家专项本科；地方专项本科（全部用一本公式）
        if($batch == 'adv_batch_un_major' || $batch == 'one_batch_un_major' || $batch == 'nation_batch_un_major' || $batch == 'place_batch_un_major')
        {
            $result = $this->firstMajor($score, $type, $batch, $province, $category, $page, $num);
        }
        // 本科二批
        if($batch == 'two_batch_un_major')
        {
            $result = $this->secondMajor($score, $type, $batch, $province, $category, $page, $num);
        }
        // 专科提前批；专科批次（全部用专科公式）
        if($batch == 'adv_batch_edu_major' || $batch == 'jun_batch_edu_major')
        {
            $result = $this->collegeMajor($score, $type, $batch, $province, $category, $page, $num);
        }

        return ['res'=>true,'data'=>$result];
    }

    /**
     * 获取往年分数线
     *
     * @param string $school 学校
     * @param string $major 专业
     * @param string $type 文科理科
     * @param string $batch 批次
     * @param string $province  省份
     * @param string $category  普通/艺术/体育
     * @return  string
     */
    public function getFormerGradeLine($school, $major, $type, $batch, $province)
    {    
        // 表名
        $table = 'data_' . $batch;
        
        // 往年分数线
        $line = Db::name($table)->where(['school'=>$school,'major'=>$major,'province'=>$province,'type'=>['LIKE','%'.$type.'%']])->order('year asc')->limit(3)->field('year,min')->select();
        // 学校信息
        $schools = Db::name('data_school')->where(['name'=>$school])->find();
        // 专业信息
        $majors = Db::name('data_major')->where(['school'=>$school,'major'=>$major])->find();

        // return $major;

        return ['line'=>$line,'school'=>$schools,'major'=>$majors];
    }


    /**
     * 一本院校
     *
     * @param string $score  分数
     * @param string $type  考生类型
     * @param string $batch  批次（表名）
     * @param string $province  平均分
     * @param string $category  考生类别
     * @param string $page  页码
     * @param string $num  数量
     * @return  string
     */
    private function firstMajor($score, $type, $batch, $province, $category, $page, $num)
    {   
        $site_name = 'site.first_specialty';
        $result = $this->forecastMajorFormula($site_name, $score, $type, $batch, $province, $category, $page, $num);
        return $result;
    }

    /**
     * 二本院校
     *
     * @param string $score  分数
     * @param string $type  考生类型
     * @param string $batch  批次（表名）
     * @param string $province  平均分
     * @param string $category  考生类别
     * @param string $page  页码
     * @param string $num  数量
     */
    private function secondMajor($score, $type, $batch, $province, $category, $page, $num)
    {   
        $site_name = 'site.second_specialty';
        $result = $this->forecastMajorFormula($site_name, $score, $type, $batch, $province, $category, $page, $num);
        return $result;
    }

    /**
     * 专科院校
     *
     * @param string $score  分数
     * @param string $type  考生类型
     * @param string $batch  批次（表名）
     * @param string $province  平均分
     * @param string $category  考生类别
     * @param string $page  页码
     * @param string $num  数量
     */
    private function collegeMajor($score, $type, $batch, $province, $category, $page, $num)
    {   
        $site_name = 'site.college_specialty';
        $result = $this->forecastMajorFormula($site_name, $score, $type, $batch, $province, $category, $page, $num);
        return $result;
    }

    /**
     * 估分公式
     *
     * @param string $site_name  配置名
     * @param string $score  分数
     * @param string $type  考生类型
     * @param string $batch  批次（表名）
     * @param string $province  平均分
     * @param string $category  考生类别
     * @return  string
     */
    private function forecastMajorFormula($site_name, $score, $type, $batch, $province, $category, $page, $num)
    {    
        $yearOne = date('Y')-1;// 去年
        $yearTwo = date('Y')-2;// 前年
        // $yearOne = 2019-1;// 去年
        // $yearTwo = 2019-2;// 前年
        $table = 'data_' . $batch;

        // 考生类型条件
        if($type == '文'){
            if($category == '普通'){
                $map = ['type' => [['EQ','文科'],['EQ','综合'],'or']];
                if($province == '内蒙古'){
                    $map = ['type' => [['EQ','文科'],['EQ','综合'],['EQ','蒙授文科'],'or']];
                }
            }else{
                $map = ['type' => [['EQ',$category.'类'],['EQ',$category.'文'],'or']];
                if($province == '内蒙古'){
                    $map = ['type' => [['EQ',$category.'类'],['EQ',$category.'文'],['EQ','蒙授文科'],['EQ','蒙授'.$category],'or']];
                }
            }
            
        }
        if($type == '理'){
            if($category == '普通'){
                $map = ['type' => [['EQ','理科'],['EQ','综合'],'or']];
                if($province == '内蒙古'){
                    $map = ['type' => [['EQ','理科'],['EQ','综合'],['EQ','蒙授理科'],'or']];
                }
            }else{
                $map = ['type' => [['EQ',$category.'类'],['EQ',$category.'理'],'or']];
                if($province == '内蒙古'){
                    $map = ['type' => [['EQ',$category.'类'],['EQ',$category.'理'],['EQ','蒙授理科'],['EQ','蒙授'.$category],'or']];
                }
            }
        }
        
        // 前年录取专业
        $yearTwoInfo = Db::name($table)->where(['province'=>$province,'year'=>$yearTwo])->where($map)->order('min desc, mean')->column('school,major');

        // 去年录取专业
        $yearOneInfo = Db::name($table)->where(['province'=>$province,'year'=>$yearOne])->where($map)->order('min desc, mean')->column('school,major');

        // 如果没有去年录取数据则用前年的数据
        if(!$yearOneInfo){
            // 两年录取专业
            $allInfo = $yearTwoInfo;
        }else{
            // 两年录取专业
            $allInfo = array_values(array_unique(array_merge($yearOneInfo,$yearTwoInfo)));
        }
        
        // 两年差值
        $grade_line = [];
        foreach ($allInfo as $school => $major) {
            // 两年平均分和最低分数据
            $yearOneInfo = Db::name($table)->where(['school'=>$school,'major'=>$major,'province'=>$province,'year'=>$yearOne])->field('mean,min')->find();
            $yearTwoInfo = Db::name($table)->where(['school'=>$school,'major'=>$major,'province'=>$province,'year'=>$yearTwo])->field('mean,min')->find();
            // 没有去年数据用前年数据
            if(!$yearOneInfo) $yearOneInfo = $yearTwoInfo;

            // 没有最低分用平均分
            if(!is_numeric($yearOneInfo['min'])) $yearOneInfo['min'] = floatval($yearOneInfo['mean']);
            if(!is_numeric($yearTwoInfo['min'])) $yearTwoInfo['min'] = floatval($yearTwoInfo['mean']);

            // 最低分不为0
            if($yearOneInfo['min'] != 0 && $yearTwoInfo['min'] != 0) {
                // 计算两年录取最低分差值
                $difference = abs($yearOneInfo['min'] - $yearTwoInfo['min']);
                if($difference > 25){
                    // 两年差值大算法
                    $max_ratio = 'gab_big_max_ratio';
                    $max_fill = 'gab_big_max_fill';
                    $min_ratio = 'gab_big_min_ratio';
                    $min_fill = 'gab_big_min_fill';
                    $scope = $this->getScope($site_name, $max_ratio, $max_fill, $min_ratio, $min_fill, $yearOneInfo['min']);
                    // 判断分数够不够
                    if($score >= floor($scope['min'])) $grade_line[] = ['school'=>$school,'max'=>$scope['max'],'min'=>$scope['min']];
                }else{
                    // 两年差值小算法
                    $max_ratio = 'gab_samll_max_ratio';
                    $max_fill = 'gab_samll_max_fill';
                    $min_ratio = 'gab_samll_min_ratio';
                    $min_fill = 'gab_samll_min_fill';
                    $scope = $this->getScope($site_name, $max_ratio, $max_fill, $min_ratio, $min_fill, $yearOneInfo['min']);
                    // 判断分数够不够
                    if($score >= floor($scope['min'])) $grade_line[] = ['school'=>$school,'major'=>$major,'max'=>$scope['max'],'min'=>$scope['min']];
                }
            }
        }
        
        // 分数线排序
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'max',       //排序字段
        );
        $arrSort = array();
        foreach($grade_line AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $grade_line);
        }
        
        $allPage = ceil(count($grade_line) / $num);

        // 根据页码返回值
        $grade_line = array_slice($grade_line,($page-1)*$num,$num);

        // 区分冲刺(红色)/稳妥(蓝色)/保底(绿色)
        $red = [];$blue = [];$green = [];
        foreach($grade_line as $row){
            // 冲刺专业
            if(abs($score - $row['min']) <= 1) {$red[] = $row;continue;}
            // 稳妥专业
            if($row['max'] > $score && $score > $row['min']) {$blue[] = $row;continue;}
            // 保底专业
            if($score >= $row['max']) {$green[] = $row;continue;}
        }
        
        $result = ['page'=>$allPage,'red'=>$red,'blue'=>$blue,'green'=>$green];
        return $result;
    }

    /**
     * 录取区间
     *
     * @param string $site_name  配置名
     * @param string $max_ratio  最高分比率
     * @param string $max_fill  最高分补值
     * @param string $min_ratio  最低分比率
     * @param string $min_fill  最低分补值
     * @param string $score  分数
     * @return  string
     */
    private function getScope($site_name, $max_ratio, $max_fill, $min_ratio, $min_fill, $score)
    {    
        // 估分参数
        $mark = Config::get($site_name);

        // 今年录取最高分
        $max = round(($score * $mark[$max_ratio] + $mark[$max_fill]),1);
        // 今年录取最低分
        $min = round(($score * $mark[$min_ratio] - $mark[$min_fill]),1);

        // 录取区间

        $result = ['max'=>$max,'min'=>$min];

        return $result;
    }

    
}

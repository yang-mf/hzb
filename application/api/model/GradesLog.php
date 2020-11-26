<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class GradesLog extends Model
{

    /**
     * 新增科目满分
     * @param   string $user_id
     * @param   string  $subjectArr
     * @param   string  $fullArr
     * @return  boole
     */
    public function createFull($user_id, $subjectArr, $fullArr)
    {
        
        $subjectData = Config::get('site.subject');
        foreach ($subjectArr as $key => $val) {
            $params[] = [
                'user_id'       =>  $user_id,
                'subject_id'    =>  $val,
                'name'          =>  $subjectData[$val],
                'full'          =>  $fullArr[$key],
            ];
        }
        // return $params;
        Db::startTrans();
        try {
            $result = Db::name('user_subject')->insertAll($params);
            Db::commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
            $result = false;
        }
        return $result;
    }

    /**
     * 添加成绩记录/并预估分数
     * @param string $province  省份
     * @param string $city 城市
     * @param string $area 县区
     * @param string $school 学校
     * @param string $user_id 会员ID
     * @param string $title 成绩名称
     * @param string $yw_score 语文成绩
     * @param string $sx_score 数学成绩
     * @param string $yy_score 英语成绩
     * @param string $zz_score 政治成绩
     * @param string $ls_score 历史成绩
     * @param string $dl_score 地理成绩
     * @param string $wl_score 物理成绩
     * @param string $hx_score 化学成绩
     * @param string $sw_score 生物成绩
     * @param string $wz_score 文综成绩
     * @param string $lz_score 理综成绩
     * @param string $event 事件 create--新增记录 forecast--新增记录并预估分数
     * @return  string
     */
    public function createGreadeLog($province, $city, $area, $school, $user_id, $title, $yw_score, $sx_score, $yy_score, $zz_score, $ls_score, $dl_score, $wl_score, $hx_score, $sw_score, $wz_score, $lz_score, $event='create')
    {
        if ($province == '' || $city == '' || $area == '' || $school == '') {
            return ['res'=>false,'msg'=>__('Invalid parameters')];
        }
        // 获取学校ID
        $school_id = Db::name('user_school')->where(['province'=>$province,'city'=>$city,'area'=>$area,'school'=>$school])->value('id');
        // 新增学校信息
        if(!$school_id){
            $school_id = $this->createSchool($province, $city, $area, $school);
        }

        // 获取学年ID
        $month = date('m');
        $tremOne = [9,10,11,12,1,2];
        $tremTwo = [3,4,5,6,7,8];

        if(in_array($month, $tremOne)){
            $academic_year = date('Y') . '--' . (date('Y') + 1) . '学年';
            $term = '第一学期';
        }
        elseif(in_array($month, $tremTwo)){
            $academic_year = (date('Y') - 1) . '--' . date('Y') . '学年';
            $term = '第二学期';
        }
        // 新增学年信息
        $academic_id = Db::name('user_academic')->where(['academic_year'=>$academic_year,'term'=>$term])->value('id');
        if(!$academic_id){
            $academic_id = $this->createAcademic($academic_year, $term);
        }

        // 判断传参是否正确
        if ($yw_score === '') {
            return ['res'=>false,'msg'=>__('请填写语文成绩')];
        }elseif($yw_score !== null){
            $name = '语文';
            $yw_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$yw_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $yw_score = floatval($yw_score);
            // 判断分数是不是正确
            if($yw_score < 0 || $yw_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$yw_id] = $yw_score;
            }
            $yw_pass = true;
        }
        if ($sx_score === '') {
            return ['res'=>false,'msg'=>__('请填写数学成绩')];
        }elseif($sx_score !== null){
            $name = '数学';
            $sx_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$sx_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $sx_score = floatval($sx_score);
            // 判断分数是不是正确
            if($sx_score < 0 || $sx_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$sx_id] = $sx_score;
            }
            $sx_pass = true;
        }
        if ($yy_score === '') {
            return ['res'=>false,'msg'=>__('请填写英语成绩')];
        }elseif($yy_score !== null){
            $name = '英语';
            $yy_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$yy_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $yy_score = floatval($yy_score);
            if($yy_score < 0 || $yy_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$yy_id] = $yy_score;
            }
            $yy_pass = true;
        }
        if ($zz_score === '') {
            return ['res'=>false,'msg'=>__('请填写政治成绩')];
        }elseif($zz_score !== null){
            $name = '政治';
            $zz_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$zz_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $zz_score = floatval($zz_score);
            // 判断分数是不是正确
            if($zz_score < 0 || $zz_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$zz_id] = $zz_score;
            }
            $zz_pass = true;
        }
        if ($ls_score === '') {
            return ['res'=>false,'msg'=>__('请填写历史成绩')];
        }elseif($ls_score !== null){
            $name = '历史';
            $ls_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$ls_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $ls_score = floatval($ls_score);
            // 判断分数是不是正确
            if($ls_score < 0 || $ls_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$ls_id] = $ls_score;
            }
            $ls_pass = true;
        }
        if ($dl_score === '') {
            return ['res'=>false,'msg'=>__('请填写地理成绩')];
        }elseif($dl_score !== null){
            $name = '地理';
            $dl_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$dl_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $dl_score = floatval($dl_score);
            if($dl_score < 0 || $dl_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$dl_id] = $dl_score;
            }
            $dl_pass = true;
        }
        if ($wl_score === '') {
            return ['res'=>false,'msg'=>__('请填写物理成绩')];
        }elseif($wl_score !== null){
            $name = '物理';
            $wl_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$wl_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $wl_score = floatval($wl_score);
            // 判断分数是不是正确
            if($wl_score < 0 || $wl_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$wl_id] = $wl_score;
            }
            $wl_pass = true;
        }
        if ($hx_score === '') {
            return ['res'=>false,'msg'=>__('请填写化学成绩')];
        }elseif($hx_score !== null){
            $name = '化学';
            $hx_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$hx_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $hx_score = floatval($hx_score);
            if($hx_score < 0 || $hx_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$hx_id] = $hx_score;
            }
            $hx_pass = true;
        }
        if ($sw_score === '') {
            return ['res'=>false,'msg'=>__('请填写生物成绩')];
        }elseif($sw_score !== null){
            $name = '生物';
            $sw_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$sw_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $sw_score = floatval($sw_score);
            // 判断分数是不是正确
            if($sw_score < 0 || $sw_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$sw_id] = $sw_score;
            }
            $sw_pass = true;
        }
        if ($wz_score === '') {
            return ['res'=>false,'msg'=>__('请填写文综成绩')];
        }elseif($wz_score !== null){
            $name = '文综';
            $wz_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$wz_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $wz_score = floatval($wz_score);
            if($wz_score < 0 || $wz_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$wz_id] = $wz_score;
            }
            $wz_pass = true;
        }
        if ($lz_score === '') {
            return ['res'=>false,'msg'=>__('请填写理综成绩')];
        }elseif($lz_score !== null){
            $name = '理综';
            $lz_id = model('Common')->getSubjectId($name);
            // 判断有没有设置科目总分
            $full = Db::name('user_subject')->where(['user_id'=>$user_id,'subject_id'=>$lz_id,'name'=>$name])->value('full');
            if(!$full) return ['res'=>false,'msg'=>__('请先设置'.$name.'满分')]; 
            $lz_score = floatval($lz_score);
            // 判断分数是不是正确
            if($lz_score < 0 || $lz_score > $full) return ['res'=>false,'msg'=>__('请先填写正确的'.$name.'分数')];
            // 判断是否需要估分，并组合所需数据
            if($event == 'forecast'){
                $forecast[$lz_id] = $lz_score;
            }
            $lz_pass = true;
        }

        // 判断是否需要估分,并保存估分数据
        if($event == 'forecast'){
            if(count($forecast) == 0){
                return ['res'=>false,'msg'=>__('请填选择科目并填写成绩')];
            }
            // 获取估分结果
            $forecastGrades = model('ForecastScore')->getForecastScore($user_id, $forecast);
            $params = [
                'user_id'               =>  $user_id,
                'grades'                =>  json_encode($forecastGrades),
                'createtime'            =>  time(),
            ];
            Db::startTrans();
            try {
                $forecast_id = Db::name('forecast_log')->insertGetId($params);
                $forecastRet = true;
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                return ['res'=>false,'msg'=>__($e->getMessage())];
            }
            $msg = '预测分数完成';
        }else{
            $forecast_id = 0;
            $forecastRet = true;
            $msg = '记录分数完成';
        }
        
        if(!$forecastRet) return ['res'=>false,'msg'=>__('参数有误，分数预估错误')];

        // $result = ['res'=>true,'msg'=>'测试','data'=>$forecastGrades];
        // return $result;

        // 各科成绩拼成数组并记录数据
        $grades = [];// 添加成绩数组
        
        if(isset($yw_pass) && $yw_pass === true){
            $sort = 'yw';
            $grade_id = $this->createGreadesLog($user_id, $sort, $yw_score);
            $grades[] = [$yw_id=>[$grade_id=>$yw_score]];
        }
        if(isset($sx_pass) && $sx_pass === true){
            $sort = 'sx';
            $grade_id = $this->createGreadesLog($user_id, $sort, $sx_score);
            $grades[] = [$sx_id=>[$grade_id=>$sx_score]];
        }
        if(isset($yy_pass) && $yy_pass === true){
            $sort = 'yy';
            $grade_id = $this->createGreadesLog($user_id, $sort, $yy_score);
            $grades[] = [$yy_id=>[$grade_id=>$yy_score]];
        }
        if(isset($zz_pass) && $zz_pass === true){
            $sort = 'zz';
            $grade_id = $this->createGreadesLog($user_id, $sort, $zz_score);
            $grades[] = [$zz_id=>[$grade_id=>$zz_score]];
        }if(isset($ls_pass) && $ls_pass === true){
            $sort = 'ls';
            $grade_id = $this->createGreadesLog($user_id, $sort, $ls_score);
            $grades[] = [$ls_id=>[$grade_id=>$ls_score]];
        }if(isset($dl_pass) && $dl_pass === true){
            $sort = 'dl';
            $grade_id = $this->createGreadesLog($user_id, $sort, $dl_score);
            $grades[] = [$dl_id=>[$grade_id=>$dl_score]];
        }if(isset($wl_pass) && $wl_pass === true){
            $sort = 'wl';
            $grade_id = $this->createGreadesLog($user_id, $sort, $wl_score);
            $grades[] = [$wl_id=>[$grade_id=>$wl_score]];
        }if(isset($hx_pass) && $hx_pass === true){
            $sort = 'hx';
            $grade_id = $this->createGreadesLog($user_id, $sort, $hx_score);
            $grades[] = [$hx_id=>[$grade_id=>$hx_score]];
        }if(isset($sw_pass) && $sw_pass === true){
            $sort = 'sw';
            $grade_id = $this->createGreadesLog($user_id, $sort, $sw_score);
            $grades[] = [$sw_id=>[$grade_id=>$sw_score]];
        }if(isset($wz_pass) && $wz_pass === true){
            $sort = 'wz';
            $grade_id = $this->createGreadesLog($user_id, $sort, $wz_score);
            $grades[] = [$wz_id=>[$grade_id=>$wz_score]];
        }if(isset($lz_pass) && $lz_pass === true){
            $sort = 'lz';
            $grade_id = $this->createGreadesLog($user_id, $sort, $lz_score);
            $grades[] = [$lz_id=>[$grade_id=>$lz_score]];
        }

        if(count($grades) == 0){
            return ['res'=>false,'msg'=>__('请填选择科目并填写成绩')];
        }

        // 添加成绩记录
        $params = [
            'school_id'             =>  $school_id,
            'academic_id'           =>  $academic_id,
            'user_id'               =>  $user_id,
            'name'                  =>  $title,
            'grades'                =>  json_encode($grades),
            'forecast_id'           =>  $forecast_id,
            'createtime'            =>  time(),
        ];

        Db::startTrans();
        try {
            Db::name('grades_log')->insert($params);
            $result = ['res'=>true,'msg'=>$msg];
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $result = ['res'=>false,'msg'=>__($e->getMessage())];
        }

        return $result;
    }

    /**
     * 获取成绩记录列表
     * @param   string  $user_id
     * @param   string  $page
     * @param   string  $num
     * @return  array
     */
    public function getGradesLogList($user_id, $page, $num)
    {
        $list = Db::name('grades_log')
            ->alias('grades')
            ->join('user_academic academic', 'grades.academic_id = academic.id')
            ->where(['user_id'=>$user_id])
            ->page($page, $num)
            ->field('grades.id,academic.academic_year,academic.term,grades.name,FROM_UNIXTIME(grades.createtime,"%Y-%m-%d") as createtime_txt')
            ->select();  
        
        return $list;
    }

    /**
     * 获取成绩记录详情
     * @param   string  $user_id
     * @return  array
     */
    public function getGradesLogDetails($id, $user_id)
    {
        $row = Db::name('grades_log')
            ->alias('grades')
            ->join('user_school school', 'grades.school_id = school.id', 'LEFT')
            ->join('user_academic academic', 'grades.academic_id = academic.id', 'LEFT')
            ->join('forecast_log forecast', 'grades.forecast_id = forecast.id', 'LEFT')
            ->where(['grades.id'=>$id,'grades.user_id'=>$user_id])
            ->field('grades.id,school.school,academic.academic_year,academic.term,grades.name,grades.grades,forecast.grades as forecast,FROM_UNIXTIME(grades.createtime,"%Y-%m-%d") as createtime_txt')
            ->find();
        $grades = json_decode($row['grades'],true);
        $total = 0;
        foreach($grades as $key => $val){
            $ret = $this->getScore($val);
            $scoreArr[] = $ret;
            $total += $ret['score'];
        }
        $scoreArr[] = ['id'=>0,'name'=>'总分','score'=>$total];
        $row['grades'] = $scoreArr;
        if($row['forecast']){
            $forecast = json_decode($row['forecast'],true);
            foreach($forecast as $key => $val){
                if($key != 0){
                    $name = model('Common')->getSubjectName($key);
                    $forecastArr[] = ['name'=>$name,'score'=>$val];
                }else{
                    $name = '总分';
                    $forecastArr[] = ['name'=>$name,'score'=>$val];
                }
            }
            $row['forecast'] = $forecastArr;
        }
        return $row;
    }

    /**
     * 修改录入成绩
     * @param   string  $id
     * @param   string  $user_id
     * @param   string  $name
     * @param   string  $subject_id
     * @param   string  $score
     * @return  array
     */
    public function changeGradesLog($id, $user_id, $name, $subject_id, $score)
    {
        $old = Db::name('grades_log')->where(['id'=>$id,'user_id'=>$user_id])->value('grades');
        if($old){
            $gradesArr = json_decode($old,true);
            // $score_id = $this->getScoreId($gradesArr);
            foreach($gradesArr as $row){
                foreach ($row as $key => $val) {
                    if($key == $subject_id){
                        $scoreArr = $val;
                        $row[$key] = $this->changeScore($subject_id, $val, $score);
                    }
                }
                $newArr[] = $row;
            }
            $new = json_encode($newArr);
            $params = [
                'name'                  =>  $name,
                'grades'                =>  $new,
                'createtime'            =>  time(),
            ];
            Db::startTrans();
            try {
                $result = Db::name('grades_log')->where(['id'=>$id,'user_id'=>$user_id])->update($params);
                Db::commit();
            } catch (Exception $e) {
                $this->error($e->getMessage());
                Db::rollback();
                $result = false;
            }
            $result = ['res'=>true,'data'=>$result];
        }else{
            $result = ['res'=>false,'msg'=>'参数有误'];
        }
        return $result;
        
    }

    /**
     * 新增会员学校
     * @param   string  $province
     * @param   string  $city
     * @param   string  $area
     * @param   string  $school
     * @return  string
     */
    private function createSchool($province, $city, $area, $school)
    {
        
        $params = [
            'province'          =>  $province,
            'city'              =>  $city,
            'area'              =>  $area,
            'school'            =>  $school,
            'createtime'        =>  time(),
        ];
        Db::startTrans();
        try {
            $result = Db::name('user_school')->insertGetId($params);
            Db::commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
            $result = false;
        }
        return $result;
    }

    /**
     * 新增会员学年
     * @param   string  $academic_year
     * @param   string  $term
     * @return  string
     */
    private function createAcademic($academic_year, $term)
    {
        
        $params = [
            'academic_year'     =>  $academic_year,
            'term'              =>  $term,
            'createtime'        =>  time(),
        ];
        Db::startTrans();
        try {
            $result = Db::name('user_academic')->insertGetId($params);
            Db::commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
            $result = false;
        }
        return $result;
    }

    /**
     * 各科成绩记录
     * @param   string  $name
     * @param   string  $name
     * @param   string  $score
     * @return  string
     */
    private function createGreadesLog($user_id, $name, $score)
    {
        // 科目是拼音缩写 yw-语文
        switch ($name)
        {
            case "yw":
                $table = 'grade_yw_log';
                break;
            case "sx":
                $table = 'grade_sx_log';
                break;
            case "yy":
                $table = 'grade_yy_log';
                break;
            case "zz":
                $table = 'grade_zz_log';
                break; 
            case "ls":
                $table = 'grade_ls_log';
                break; 
            case "dl":
                $table = 'grade_dl_log';
                break; 
            case "wl":
                $table = 'grade_wl_log';
                break; 
            case "hx":
                $table = 'grade_hx_log';
                break; 
            case "sw":
                $table = 'grade_sw_log';
                break; 
            case "wz":
                $table = 'grade_wz_log';
                break; 
            case "lz":
                $table = 'grade_lz_log';
                break;                                                                                 
            default:
            $table = '';
        }
        if($table){
            $params = [
                'user_id'       =>  $user_id,
                'score'         =>  $score,
                'createtime'    =>  time(),
            ];
            Db::startTrans();
            try {
                $result = Db::name($table)->insertGetId($params);
                Db::commit();
            } catch (Exception $e) {
                $this->error($e->getMessage());
                Db::rollback();
                $result = false;
            }
        }else{
            $result = true;
        }
        
        return $result;
    }

    /**
     * 获取成绩数组
     * @param   array  $data
     * @return  array
     */
    private function getScore($data)
    {
        foreach($data as $key => $val){
            $name = model('Common')->getSubjectName($key);
            foreach($val as $k => $v){
                $id = $k;
                $score = $v;
            }
        }
        $result = ['id'=>$id,'name'=>$name,'score'=>$score];
        return $result;
    }

    /**
     * 修改单科成绩
     * @param   string  $subject_id
     * @param   array   $data
     * @return  array
     */
    private function changeScore($subject_id, $data, $score)
    {
        $name = model('Common')->getSubjectName($subject_id);
        switch ($name)
        {
            case "语文":
                $table = 'grade_yw_log';
                break;
            case "数学":
                $table = 'grade_sx_log';
                break;
            case "英语":
                $table = 'grade_yy_log';
                break;
            case "政治":
                $table = 'grade_zz_log';
                break; 
            case "历史":
                $table = 'grade_ls_log';
                break; 
            case "地理":
                $table = 'grade_dl_log';
                break; 
            case "物理":
                $table = 'grade_wl_log';
                break; 
            case "化学":
                $table = 'grade_hx_log';
                break; 
            case "生物":
                $table = 'grade_sw_log';
                break; 
            case "文综":
                $table = 'grade_wz_log';
                break; 
            case "理综":
                $table = 'grade_lz_log';
                break;                                                                                 
            default:
            $table = '';
        }
        
        foreach($data as $key => $val){
            $params = [
                'score'                 =>  $score,
                'createtime'            =>  time(),
            ];
            Db::startTrans();
            try {
                $ret = Db::name($table)->where(['id'=>$key])->update($params);
                Db::commit();
            } catch (Exception $e) {
                $this->error($e->getMessage());
                Db::rollback();
                $ret = false;
            }
            if($ret){
                $result[$key] = $score;
            }
        }
        return $result;
    }

}

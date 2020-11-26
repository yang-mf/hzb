<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class Consulting extends Model
{

    /**
     * 预测专业
     *
     * @param string $user_id 会员ID
     * @param string $name  姓名
     * @param string $mobile 手机号
     * @param string $score 总分
     * @param string $rank 位次
     * @param string $type  文科理科
     * @param string $batch  批次
     * @param string $yw  语文成绩
     * @param string $sx  数学成绩
     * @param string $yy  英语成绩
     * @param string $zh  综合成绩
     * @param string $date  预约时间
     * @param string $createtime  创建时间
     * @return  boole
     */
    public function putConsulting($user_id, $name, $mobile, $score, $rank, $type, $batch, $yw, $sx, $yy, $zh, $date)
    {   
        $params = [
            'user_id'       =>  $user_id,
            'name'          =>  $name,
            'mobile'        =>  $mobile,
            'score'         =>  $score,
            'rank'          =>  $rank,
            'type'          =>  $type,
            'batch'         =>  $batch,
            'yw'            =>  $yw,
            'sx'            =>  $sx,
            'yy'            =>  $yy,
            'zh'            =>  $zh,
            'date'          =>  $date,
            'createtime'    =>  time(),
        ];
        Db::startTrans();
        try {
            $result = Db::name('consulting')->insert($params);
            Db::commit();
        } catch (Exception $e) {
            $result = false;
            Db::rollback();
        }
        
        return $result;
    }

    /**
     * 咨询记录
     *
     * @param string $user_id 会员ID
     * @return  array
     */
    public function getConsultingLog($user_id)
    {   
        $result = Db::name('consulting')->where(['user_id'=>$user_id])->field('id,date,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->select();
        return $result;
    }

    /**
     * 咨询详情
     *
     * @param string $id ID
     * @return  array
     */
    public function getConsultingDetails($id)
    {   
        $result = Db::name('consulting')->where(['id'=>$id])->field('*,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->find();
        return $result;
    }

    

    
}

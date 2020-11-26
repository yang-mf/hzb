<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class Pay extends Model
{

    /**
     * 创建订单
     *
     * @param string $amount 金额
     * @param string $type 支付方式 alipay-支付宝 wechat-微信
     * @param string $method 支付方法
     * @param string $openid 用户的OpenID
     * @param string $auth_code 验证码
     * @param string $user_id 会员ID
     * @param string $category 订单类型 score-估成绩 school-估学校 major-估专业
     * @param string $number 数量 
     * @param string $price 单价 
     * @param string $total 总价 
     * @param string $sale 优惠 
     * @param string $note 注释 
     * @return  array
     */
    public function createOrder($user_id, $category, $number, $price, $sale, $note, $amount, $type, $title)
    {   
        $priceInfo = Config::get('site.price');
        $priceData = $priceInfo[$category];
        // 比对产品单价
        if($price != $priceData)['res'=>false,'msg'=>'价格有误'];
        // 比对订单金额
        $total = $price * $number;
        if($amount != ($total - $sale))['res'=>false,'msg'=>'金额有误'];
        // 获取订单号
        $order_num = $this->getOrderNum();
        // 组合数据
        $params = [
            'order_num'       =>  $order_num,
            'user_id'         =>  $user_id,
            'title'           =>  $title,
            'type'            =>  $category,
            'number'          =>  $number,
            'price'           =>  $price,
            'total'           =>  $total,
            'total'           =>  $total,
            'sale'            =>  $sale,
            'amount'          =>  $amount,
            'payment'         =>  $type,
            'note'            =>  $note,
        ];
        
        Db::startTrans();
        try {
            $result = Db::name('order')->insert($params);
            Db::commit();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
            $result = false;
            $order_num = '';
        }
        return ['res'=>$result,'order_num'=>$order_num];
    }



    /**
     * 生成24位唯一订单号码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码
     *
     * @return  string
     */
    private function getOrderNum()
    {   
        //订购日期
        $order_date = date('Y-m-d');

        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('YmdHis') . rand(10000000,99999999);

        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for($i=0; $i<$order_id_len; $i++){
            $order_id_sum += (int)(substr($order_id_main,$i,1));
        }

        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);

        return $order_id;
    }
    
}

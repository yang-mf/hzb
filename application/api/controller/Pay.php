<?php

namespace app\api\controller;

use app\common\controller\Api;
use \addons\epay\library\Service;
use think\Db;

/**
 * 支付接口
 */
class pay extends Api
{

    protected $noNeedLogin = [''];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 支付
     *
     * @param string $amount 金额
     * @param string $type 支付方式 alipay-支付宝 wechat-微信
     * @param string $method 支付方法
     * @param string $openid 用户的OpenID （暂时不用未传）
     * @param string $auth_code 验证码 （暂时不用未传）
     * @param string $user_id 会员ID
     * @param string $category 订单类型 score-估成绩 school-估学校 major-估专业
     * @param string $number 数量 （暂时不用未传）
     * @param string $price 单价 （暂时不用未传）
     * @param string $sale 优惠 （暂时不用未传）
     * @param string $note 注释 
     */
    public function pay()
    {
        
        $amount = floatval($this->request->request('amount'));
        $type = $this->request->request('type');
        $method = $this->request->request('method');
        $openid = $this->request->request('openid');
        $auth_code = $this->request->request('auth_code');
        $user_id = $this->request->request('user_id');
        $category = $this->request->request('category');
        $number = intval($this->request->request('number') ? $this->request->request('number') : 1);
        $price = floatval($this->request->request('price') ? $this->request->request('price') : $amount);
        $sale = floatval($this->request->request('sale') ? $this->request->request('sale') : 0);
        $note = $this->request->request('note');

        if (!$amount) {
            $this->error(__('Invalid parameters'));
        }

        // 购买数量
        if($number < 1) $this->error('数量有误');

        // 根据订单类型 设置订单标题和注释
        $nickname = Db::name('user')->where(['id'=>$user_id,'status'=>'normal'])->value('nickname');
        if(!$nickname) $this->error('会员不存在');
        if($category == 'score') $title = '预测成绩';
        if($category == 'school') $title = '预估学校';
        if($category == 'major') $title = '预估专业';
        if(!$note){
            if($category == 'score')  $note = '会员：'.$nickname.$title;
            if($category == 'school') $note = '会员：'.$nickname.$title;
            if($category == 'major')  $note = '会员：'.$nickname.$title;
        }

        // 创建订单
        $order = model('pay')->createOrder($user_id, $category, $number, $price, $sale, $note, $amount, $type, $title);

        if($type == 'alipay'){$notifyurl = 'http://fw366.cn:81/addons/epay/api/notifyx/type/alipay';$returnurl = 'http://fw366.cn:81/addons/epay/api/notifyx/type/alipay';}
        if($type == 'wechat'){$notifyurl = 'http://fw366.cn:81/addons/epay/api/notifyx/type/wechat';$returnurl = 'http://fw366.cn:81/addons/epay/api/returnx/type/wechat';}

        if($order['res']){
            $params = [
                'amount'=>$amount,
                'orderid'=>$order['order_num'],
                'type'=>$type,
                'title'=>$title,
                'notifyurl'=>$notifyurl,
                'returnurl'=>$returnurl,
                'method'=>$method,
                'openid'=>$openid,
                'auth_code'=>$auth_code
            ];
            $this->success('OK',Service::submitOrder($params));
        }else{
            $this->error($order['msg']);
        }

    }

}

<?php

namespace addons\mobilesms;

use app\common\library\Menu;
use think\Addons;

/**
 * Mobilesms插件
 */
class Mobilesms extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {

        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {

        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {

        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {

        return true;
    }

    /**
     * 短信发送
     * @param Sms $params
     * @return mixed
     */
    public function smsSend(&$params)
    {
        $mobilesms = new library\Mobilesms();
        $result = $mobilesms->mobile($params['mobile'])->msg($params['event'],$params['code'])->send();
        
        return $result;
    }

    /**
     * 短信发送通知（msg参数直接构建实际短信内容即可）
     * @param   array $params
     * @return  boolean
     */
    public function smsNotice(&$params)
    {
        $mobilesms = new library\Mobilesms();
        $result = $mobilesms->mobile($params['mobile'])->msg($params['event'])->send();
        return $result;
    }

    /**
     * 检测验证是否正确
     * @param   Sms $params
     * @return  boolean
     */
    public function smsCheck(&$params)
    {
        return TRUE;
    }
}

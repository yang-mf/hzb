<?php

namespace addons\mobilesms\library;

class Mobilesms
{
    private $_params = [];
    protected $error = '';
    protected $config = [];

    public function __construct()
    {
        $this->config = get_addon_config('mobilesms');
        return $this->config;
    }


    /**
     * 立即发送短信
     *
     * @return boolean
     */
    public function send()
    {
        $this->error = '';
        $params = $this->_params();

        $config = $this->config;
        $configParams = $config['params'];
        $url = $configParams['url'];
        unset($configParams['url']);
        $postArr = $configParams + $params;
        // return $postArr;
        $options = [
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json; charset=utf-8'
            )
        ];
        $result = \fast\Http::sendRequest($url, $postArr, 'GET', $options);

        // 根据接口的返回信息写成功和失败的逻辑
        // 以下代码请根据实际情况修改或重写
        $obj = simplexml_load_string($result['msg'],"SimpleXMLElement", LIBXML_NOCDATA);
        $result = json_decode(json_encode($obj),true);

        if ($result['returnstatus'] == 'Success') {
            return TRUE;
        } else {
            // $this->error = $result['message'];// 调试短信配置使用
            return FALSE;
        }
        
    }

    private function _params()
    {
        
        return $this->_params;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 接收手机
     * @param   string $mobile 手机号码
     * @return mobilesms
     */
    public function mobile($mobile = '')
    {
        $this->_params['mobile'] = $mobile;
        return $this;
    }

    /**
     * 短信内容
     * @param   string $msg 短信内容
     * @return mobilesms
     */
    public function msg($event = 'code',$code = '')
    {
        $msgArr = $this->config['msg'];
        if(isset($msgArr[$event])){
            $msg = $msgArr[$event];
            if($event == 'code') $msg = str_replace('$code',$code,$msgArr['code']);
        }else{
            $msg = str_replace('$code',$code,$msgArr['code']);
        }

        $this->_params['content'] = '【' . $this->config['sign'] . '】' . $msg;
        return $this;
    }
}
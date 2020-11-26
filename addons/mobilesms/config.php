<?php

return array (
  0 => 
  array (
    'type' => 'string',
    'name' => 'sign',
    'title' => '标识',
    'value' => '云智选',
    'content' => '',
    'tip' => '【XXX】您的验证码为；就是里面的XXX',
    'rule' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'type' => 'array',
    'name' => 'params',
    'title' => '参数值',
    'value' => 
    array (
      'url' => 'http://sms.kingtto.com:9999/sms.aspx?action=send',
      'userid' => '37215',
      'account' => 'yunzhixuan',
      'password' => 'q5d1Dsdf58fGo',
    ),
    'content' => 
    array (
      'value1' => 'title1',
      'value2' => 'title2',
    ),
    'tip' => '',
    'rule' => '',
    'extend' => '',
  ),
  2 => 
  array (
    'type' => 'array',
    'name' => 'msg',
    'title' => '短信内容',
    'value' => 
    array (
      'code' => '验证码：$code ，切勿将验证码泄露于他人。如非本人操作，请忽略。',
    ),
    'content' => 
    array (
      'value1' => 'title1',
      'value2' => 'title2',
    ),
    'tip' => '',
    'rule' => '',
    'extend' => '',
  ),
  3 => 
  array (
    'name' => '__tips__',
    'title' => '温馨提示',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => '可以根据自己的需要增删改参数名称和值<br>目前只测试了发验证码的模板逻辑，如有需要可自行去 addnos/mobilesms/libary/Mpbilesms.php 下自行修改测试',
    'rule' => '',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
);

<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Config;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        //自己写的页面
        return $this->view->fetch('test/index');
        //原页面
//        return $this->view->fetch();
    }

}

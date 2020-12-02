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
        //前端展示查学校模块
        return $this->view->fetch();
    }
}

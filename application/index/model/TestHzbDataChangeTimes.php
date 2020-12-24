<?php

namespace app\index\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class TestHzbDataChangeTimes extends Model
{
    public function get_times() {
        $res = Db::name('hzb_data_change_times')
            ->where('id',1)
            ->value('times');
        return $res;
    }
}

<?php

namespace app\admin\model;

use think\Model;


class DataThrBatchUnMajor extends Model
{

    

    

    // 表名
    protected $name = 'data_thr_batch_un_major';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'year_text'
    ];
    

    
    public function getYearList()
    {
        return ['2018' => __('2018'), '2017' => __('2017')];
    }


    public function getYearTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['year']) ? $data['year'] : '');
        $list = $this->getYearList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}

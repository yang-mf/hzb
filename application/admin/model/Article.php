<?php

namespace app\admin\model;

use think\Db;
use think\Model;
use traits\model\SoftDelete;

class Article extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'article';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getCategoryList($operate, $category_id)
    {
        if($operate == 'add'){
            $list = Db::name('Category')->where(['type'=>['<>','navigation'],'status'=>'normal'])->order('weigh desc')->field('id,name,type')->select();
            $data = [];
            foreach ($list as $key => $row) {
                if($row['type'] == 'page'){
                    $article = Db::name('article')->where(['category_id'=>$row['id']])->value('id');
                    if($article){
                        continue;
                    }
                }
                $sun = Db::name('Category')->where(['pid'=>$row['id']])->value('id');
                if(!$sun){
                    $data[$row['id']] = $row['name'];
                }
            }
            $result = $data;
        }elseif($operate == 'edit'){
            $list = Db::name('Category')->where(['type'=>['<>','navigation'],'status'=>'normal'])->order('weigh desc')->field('id,name,type')->select();
            $data = [];
            foreach ($list as $key => $row) {
                if($row['type'] == 'page'){
                    $article = Db::name('article')->where(['category_id'=>$row['id']])->value('id');
                    if($article){
                        if($category_id != $row['id']){
                            continue;
                        }
                    }
                }
                $sun = Db::name('Category')->where(['pid'=>$row['id']])->value('id');
                if(!$sun){
                    $data[$row['id']] = $row['name'];
                }
            }
            $result = $data;
        }else{
            $data = Db::name('Category')->where(['type'=>['<>','navigation'],'status'=>'normal'])->order('weigh desc')->column('name','id');
            foreach ($data as $id => $name) {
                $sun = Db::name('Category')->where(['pid'=>$id])->value('id');
                if($sun){
                    unset($data[$id]);
                }
            }
            $result = $data;
        }
        return $result;
    }

    public function getCategoryType($id)
    {
        $category_id = Db::name('article')->where(['id'=>$id])->value('category_id');
        $type = Db::name('Category')->where(['id'=>$category_id])->value('type');
        return $type;
    }
    
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function category()
    {
        return $this->belongsTo('Category', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}

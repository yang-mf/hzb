<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 文章管理
 *
 * @icon fa fa-circle-o
 */
class Article extends Backend
{
    
    /**
     * Article模型对象
     * @var \app\admin\model\Article
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Article;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 分类
     */
    public function category($operate, $category_id)
    {
        return $this->model->getCategoryList($operate, $category_id);
    }

    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['admin','category'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['admin','category'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                
                $row->getRelation('admin')->visible(['nickname']);
				$row->getRelation('category')->visible(['name']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $this->view->assign('categoryList', build_select('row[category_id]', $this->category('add',''), '', ['class' => 'form-control selectpicker']));
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("categoryType", $this->model->getCategoryType($ids));
        $this->view->assign('categoryList', build_select('row[category_id]', $this->category('edit',$row['category_id']), $row['category_id'], ['class' => 'form-control selectpicker']));
        // return json($this->category('edit'));
        return parent::edit($ids);
    }
}

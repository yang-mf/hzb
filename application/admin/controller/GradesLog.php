<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 成绩记录
 *
 * @icon fa fa-circle-o
 */
class GradesLog extends Backend
{
    
    /**
     * GradesLog模型对象
     * @var \app\admin\model\GradesLog
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\GradesLog;

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
                    ->with(['userschool','useracademic','user','forecast'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['userschool','useracademic','user','forecast'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                
                $row->getRelation('userschool')->visible(['province','city','area','school']);
				$row->getRelation('useracademic')->visible(['academic_year','term']);
				$row->getRelation('user')->visible(['grade','nickname']);
				$row->getRelation('forecast')->visible(['grades']);
                
                $row['userschool']['province'] = $this->model->getAreaName($row['userschool']['province']);
                $row['userschool']['city'] = $this->model->getAreaName($row['userschool']['city']);
                $row['userschool']['area'] = $this->model->getAreaName($row['userschool']['area']);
                $row['user']['grade'] = $this->model->getGradeName($row['user']['grade']);
                $row['grades'] = $this->model->getGradesInit($row['grades']);
                $row['forecast']['grades'] = $this->model->getForecastInit($row['forecast']['grades']);
                // return json($row);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 详情
     */
    public function details($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row['userschool']['province'] = $this->model->getAreaName($row['userschool']['province']);
        $row['userschool']['city'] = $this->model->getAreaName($row['userschool']['city']);
        $row['userschool']['area'] = $this->model->getAreaName($row['userschool']['area']);
        $row['user']['grade'] = $this->model->getGradeName($row['user']['grade']);
        $row['grades'] = $this->model->getGradesInit($row['grades']);
        $row['forecast']['grades'] = $this->model->getForecastInit($row['forecast']['grades']);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}

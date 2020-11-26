<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Config;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->hidden(['password', 'salt']);
            }

            $grade = Config::get('site.grade');
            foreach($list as $key => $val){
                if($val['grade'] == 0){
                    $list[$key]['grade_txt'] = '--';
                }else{
                    $list[$key]['grade_txt'] = $grade[$val['grade']];
                }
                
            }

            $result = array("total" => $total, "rows" => $list);
            
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        $this->view->assign('gradeList', build_select('row[grade]', $this->grade(), $row['grade'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

    /**
     * 年级
     */
    public function grade()
    {
        $grade = Config::get('site.grade');
        return $grade;
    }

}

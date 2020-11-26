<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 高校信息
 *
 * @icon fa fa-circle-o
 */
class DataSchool extends Backend
{
    
    /**
     * DataSchool模型对象
     * @var \app\admin\model\DataSchool
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\DataSchool;

        // 高校类型
        $this->type = array_flip($this->model->column('type'));
        foreach ($this->type as $key => $value) {
            $this->type[$key] = $key;
        }
        // 高校性质
        $this->nature = array_flip($this->model->column('nature'));
        foreach ($this->nature as $key => $value) {
            $this->nature[$key] = $key;
        }
        // 211院校
        $this->is_211 = array_flip($this->model->column('is_211'));
        foreach ($this->is_211 as $key => $value) {
            $this->is_211[$key] = $key;
        }
        // 985院校
        $this->is_985 = array_flip($this->model->column('is_985'));
        foreach ($this->is_985 as $key => $value) {
            $this->is_985[$key] = $key;
        }
        // 双一流院校
        $this->classic = array_flip($this->model->column('classic'));
        foreach ($this->classic as $key => $value) {
            $this->classic[$key] = $key;
        }
        // 办学层次
        $this->renown = array_flip($this->model->column('renown'));
        foreach ($this->renown as $key => $value) {
            $this->renown[$key] = $key;
        }
        // 星级排名
        $this->rank = array_flip($this->model->column('rank'));
        foreach ($this->rank as $key => $value) {
            $this->rank[$key] = $key;
        }

    }
    
    /**
     * 高校类型
     */ 
    public function type(){
        return json($this->type);
    }

    /**
     * 高校性质
     */ 
    public function nature(){
        return json($this->nature);
    }

    /**
     * 211院校
     */ 
    public function is_211(){
        return json($this->is_211);
    }

    /**
     * 985院校
     */ 
    public function is_985(){
        return json($this->is_985);
    }

    /**
     * 双一流院校
     */ 
    public function classic(){
        return json($this->classic);
    }

    /**
     * 办学层次
     */ 
    public function renown(){
        return json($this->renown);
    }

    /**
     * 星级排名
     */ 
    public function rank(){
        return json($this->rank);
    }

    /**
     * 获取地区名
     */
    public function getAreaName($id){
        $name = Db::name('area')->where(['id'=>$id])->value('name');
        return $name;
    }

    /**
     * 获取地区名(地级市没有重名)
     */
    public function getAreaId($name){
        $id = Db::name('area')->where(['name'=>$name])->value('id');
        return $id;
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
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            foreach ($list as $key => $value) {
                $list[$key]['extend'] = '';
                if($value['is_211'] != '----'){
                    $list[$key]['extend'] .= $value['is_211'].',';
                }
                if($value['is_985'] != '----'){
                    $list[$key]['extend'] .= $value['is_985'].',';
                }
                if($value['classic'] != '----'){
                    $list[$key]['extend'] .= $value['classic'].',';
                }
                if($value['renown'] != '----'){
                    $list[$key]['extend'] .= $value['renown'].',';
                }
                if($value['rank'] != '----'){
                    $list[$key]['extend'] .= $value['rank'].',';
                }
            }

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
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $params['province'] = $this->getAreaName($params['province']);
                    $params['city'] = $this->getAreaName($params['city']);
                    if($params['classic'] == ''){
                        $params['classic'] = '----';
                    }
                    if($params['renown'] == ''){
                        $params['renown'] = '----';
                    }
                    if($params['rank'] == ''){
                        $params['rank'] = '----';
                    }
                    // return $params;
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        // 高校类型
        $this->view->assign('typeList', build_select('row[type]', $this->type, '', ['class' => 'form-control selectpicker']));
        // 高校性质
        $this->view->assign('natureList', build_select('row[nature]', $this->nature, '', ['class' => 'form-control selectpicker']));
        // 211院校
        $this->view->assign('is_211List', build_select('row[is_211]', $this->is_211, '', ['class' => 'form-control selectpicker']));
        // 985院校
        $this->view->assign('is_985List', build_select('row[is_985]', $this->is_985, '', ['class' => 'form-control selectpicker']));
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $params['province'] = $this->getAreaName($params['province']);
                    $params['city'] = $this->getAreaName($params['city']);
                    if($params['classic'] == ''){
                        $params['classic'] = '----';
                    }
                    if($params['renown'] == ''){
                        $params['renown'] = '----';
                    }
                    if($params['rank'] == ''){
                        $params['rank'] = '----';
                    }
                    // return $params;
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        // 高校类型
        $this->view->assign('typeList', build_select('row[type]', $this->type, '', ['class' => 'form-control selectpicker']));
        // 高校性质
        $this->view->assign('natureList', build_select('row[nature]', $this->nature, '', ['class' => 'form-control selectpicker']));
        // 211院校
        $this->view->assign('is_211List', build_select('row[is_211]', $this->is_211, '', ['class' => 'form-control selectpicker']));
        // 985院校
        $this->view->assign('is_985List', build_select('row[is_985]', $this->is_985, '', ['class' => 'form-control selectpicker']));
        $row['province'] = $this->getAreaId($row['province']);
        $row['city'] = $this->getAreaId($row['city']);
        // return $row;
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}

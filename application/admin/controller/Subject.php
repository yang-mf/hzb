<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 年级学科
 *
 * @icon fa fa-circle-o
 */
class Subject extends Backend
{
    
    /**
     * Subject模型对象
     * @var \app\admin\model\Subject
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Subject;

        $this->view->assign('gradeList', Config::get('site.grade'));
        $this->view->assign('subjectList', Config::get('site.subject'));

    }

    /**
     * 科目
     */
    
    public function subject()
    {
        $subject = Config::get('site.subject');
        $i = 0;
        foreach ($subject as $key => $val) {
            $list[$i]['id'] = $key;
            $list[$i]['name'] = $val;
            $i++;
        }
        $result = ['list'=>$list,'total'=>count($list)];
        return json($result);
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

            $grade = Config::get('site.grade');
            $subject = Config::get('site.subject');
            foreach($list as $key => $val){
                // 年级
                $list[$key]['grade_txt'] = $grade[$val['grade_id']];
                // 学科
                $subArr = json_decode($val['subject'],true);
                $subTxt = '';
                foreach($subArr as $k => $v){
                    $id = $v['id'];
                    $subTxt .= $subject[$id] . ':' . $v['full'] . ' | ';
                }
                $list[$key]['subject_txt'] = $subTxt;
                
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
                    $grade = Config::get('site.grade');
                    if(!isset($params['subject'])){
                        $text = $grade[$params['grade_id']];
                        $this->error(__('请选择'.$text.'学科'));
                    }
                    $subject = Config::get('site.subject');
                    foreach($params['subject'] as $val){
                        if($val['full'] == ''){
                            $text = $subject[$val['id']];
                            $this->error(__('请填写'.$text.'满分'));
                        }
                    }
                    $params['subject'] = json_encode($params['subject']);
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
                    $grade = Config::get('site.grade');
                    if(!isset($params['subject'])){
                        $text = $grade[$params['grade_id']];
                        $this->error(__('请选择'.$text.'学科'));
                    }
                    $subject = Config::get('site.subject');
                    foreach($params['subject'] as $val){
                        if($val['full'] == ''){
                            $text = $subject[$val['id']];
                            $this->error(__('请填写'.$text.'满分'));
                        }
                    }
                    $params['subject'] = json_encode($params['subject']);
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
        
        
        $strToArr = json_decode($row['subject'],true);
        foreach($strToArr as $val){
            $k = $val['id'];
            $v = $val['full'];
            $subjectInfo[$k] = $v;
        }
        foreach(Config::get('site.subject') as $key => $val){
            if(!isset($subjectInfo[$key])){
                $subjectInfo[$key] = '';
            }
        }
        $row['subject_arr'] = $subjectInfo;
        // echo '<pre>';
        // print_r($row['subject_arr']);
        // exit;
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}

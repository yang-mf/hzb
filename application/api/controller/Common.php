<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Area;
use app\common\model\Version;
use fast\Random;
use fast\Imgcompress;// 图片压缩
use think\Config;
use think\Db;

/**
 * 公共接口
 */
class Common extends Api
{
    protected $noNeedLogin = ['init','grade','subject'];
    protected $noNeedRight = '*';

    /**
     * 加载初始化
     *
     * @param string $version 版本号
     * @param string $lng     经度
     * @param string $lat     纬度
     */
    public function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');
            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => Config::get('upload'),
                'coverdata'   => Config::get("cover"),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 上传文件
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证是否为图片文件
        $imagewidth = $imageheight = 0;
        if (in_array($fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($fileInfo['tmp_name']);
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                $this->error(__('Uploaded file is not a valid image'));
            }
            // 图片大于10M不能上传
            if($fileInfo['size'] > 10485760){
                $this->error(__('Image too large'));
            }
            $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
            $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);

        // 图片文件判断是否压缩
        if (in_array($fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            
            // 图片大于300K压缩
            if($fileInfo['size'] > 307200){
                $source = ROOT_PATH . '/public' . $uploadDir . $fileName;
                $dst_img = ROOT_PATH . '/public' . $uploadDir . $fileName; //可存放路径,覆盖原图
                // 大于1M 0.3倍压缩
                if($fileInfo['size'] > 1048576){
                    $percent = 0.3;
                }else{
                    $percent = 0.5;
                }
                $image = (new Imgcompress($source,$percent))->compressImg($dst_img);
                $new_fileINfo = getimagesize($dst_img);
                $size = filesize($dst_img);
                $imagewidth = $new_fileINfo[0];
                $imageheight = $new_fileINfo[1];
                $fileInfo['size'] = $size;
            }
            
        }

        if ($splInfo) {
            $params = array(
                'admin_id'    => 0,
                'user_id'     => (int)$this->auth->id,
                'filesize'    => $fileInfo['size'],
                'imagewidth'  => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $fileInfo['type'],
                'url'         => $uploadDir . $splInfo->getSaveName(),
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
            );
            $attachment = model("attachment");
            $attachment->data(array_filter($params));
            $attachment->save();
            \think\Hook::listen("upload_after", $attachment);
            $this->success(__('Upload successful'), [
                'url' => $uploadDir . $splInfo->getSaveName()
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 年级列表
     */
    public function grade()
    {
        $grade = Config::get('site.grade');
        // $gradeArr = 1;
        if(is_array($grade)){
            foreach($grade as $key => $val){
                $data[] = ['value'=>$key,'label'=>$val];
            }
            $this->success('OK', $data);
        }else{
            $this->error(__('暂无数据'));
        }
    }

    /**
     * 学科列表
     * @param string $grade 年级ID
     */
    public function subject()
    {
        $grade = $this->request->request('grade');
        $subjectData = Config::get('site.subject');
        $subject = Db::name('subject')->where(['grade_id'=>$grade])->value('subject');
        if($subject){
            $subjectArr = json_decode($subject,true);
            foreach ($subjectArr as $key => $val) {
                $subject_id = $val['id'];
                $subject_full = $val['full'];
                $result[] = [
                    'subject_id'    =>  $subject_id,
                    'name'          =>  $subjectData[$subject_id],
                    // 'full'          =>  $subject_full,
                ];
            }
            $this->success('OK', $result);
        }else{
            $this->error(__('暂无数据'));
        }
    }

    /**
     * 地区列表
     * @param string $pid 父级ID
     * @param string $level 级别 1-省 2-市 3-县/区
     */
    public function getAreaList()
    {
        $pid = $this->request->request('pid') ? $this->request->request('pid') : 0;
        $level = $this->request->request('level') ? $this->request->request('level') : 1;
        $result = Db::name('area')->where(['pid'=>$pid,'level'=>$level])->field('id,name')->select();
        if($result){
            $this->success('OK', $result);
        }else{
            $this->error(__('暂无数据'));
        }
    }

    /**
     * 地区编号/名称
     * @param string $type 类型 num-查编号 name-查名称
     * @param string $param 参数
     */
    public function getAreaInfo()
    {
        $type = $this->request->request('type') ? $this->request->request('type') : 'num';
        $param = $this->request->request('param');
        if($type == 'num'){
            $where = ['name'=>$param];
            $value = 'id';
        }
        else{
            $where = ['id'=>$param];
            $value = 'name';
        }
        
        $result = Db::name('area')->where($where)->value($value);
        
        if($result){
            $this->success('OK', $result);
        }else{
            $this->error(__('暂无数据'));
        }
    }

    
}

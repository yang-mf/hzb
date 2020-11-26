<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 导航
     *
     * @param string $pid 父级ID
     * @param string $type 导航类型
     */
    public function navigation()
    {
        $pid = $this->request->request('pid') ? $this->request->post("pid") : 0;
        $type = $this->request->request('type');
        if(!$type) $this->error('参数有误');
        $result = model('Index')->getNavigation($pid, $type);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 文章列表
     *
     * @param string $category_id 导航ID
     * @param string $page 页码
     * @param string $num 数量
     */
    public function articleList()
    {
        $category_id = $this->request->request('category_id');
        $page = $this->request->request('page') ? $this->request->post("page") : 1;
        $num = $this->request->request('num') ? $this->request->post("num") : 10;

        if (!$category_id) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('Index')->getArticleList($category_id, $page, $num);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 文章详情
     *
     * @param string $article_id 文章ID
     */
    public function articleDetails()
    {
        $article_id = $this->request->request('article_id');

        if (!$article_id) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('Index')->getArticleDetails($article_id);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 热门文章
     *
     * @param string $page 页码
     * @param string $num 数量
     */
    public function hotArticle()
    {
        $page = $this->request->request('page') ? $this->request->post("page") : 1;
        $num = $this->request->request('num') ? $this->request->post("num") : 7;


        $result = model('Index')->getHotArticle($page, $num);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 友情链接
     *
     * @param string $location 位置
     */
    public function friendLink()
    {
        $location = $this->request->request('location');

        $result = model('Index')->getFriendLink($location);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 站点信息
     *
     * @param string $name 名称
     */
    public function siteInfo()
    {
        $name = $this->request->request('name');

        $result = model('Index')->getSiteInfo($name);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 轮播图
     *
     * @param string $atter 类型 inner-内链 outer-外联 none-无链接
     */
    public function banner()
    {
        $atter = $this->request->request('atter');

        $result = model('Index')->getBanner($atter);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 广告列表
     *
     * @param string $province 省份
     * @param string $city 城市
     * @param string $area 县区
     * @param string $page 页码
     * @param string $num 数量
     */
    public function advertList()
    {
        $province = $this->request->request('province');
        $city = $this->request->request('city');
        $area = $this->request->request('area');
        $page = $this->request->request('page') ? $this->request->post("page") : 1;
        $num = $this->request->request('num') ? $this->request->post("num") : 7;

        if (!$province) {
            $this->error(__('Invalid parameters'));
        }

        if($area){
            if (!$city) {
                $this->error(__('Invalid parameters'));
            } 
        }

        $result = model('Index')->getAdvertList($province, $city, $area, $page, $num);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 广告详情
     *
     * @param string $advert_id 广告ID
     */
    public function advertDetails()
    {
        $advert_id = $this->request->request('advert_id');

        if (!$advert_id) {
            $this->error(__('Invalid parameters'));
        }

        $result = model('Index')->getAdvertDetails($advert_id);
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

    /**
     * 用户协议
     *
     * @param string $atter 类型 inner-内链 outer-外联 none-无链接
     */
    public function agreement()
    {
        $result = Config::get('site.agreement');
        
        if($result){
            $this->success('OK',$result);
        }else{
            $this->error('暂无数据');
        }
    }

}

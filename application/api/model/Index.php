<?php

namespace app\api\model;

use think\Model;
use think\Config;
use think\Exception;
use think\Db;

class Index extends Model
{

    /**
     * 获取导航
     *
     * @param string $pid 父级ID
     * @param string $type 导航类型(查询的类型)
     * @return  array
     */
    public function getNavigation($pid, $type)
    {   
        if($type){
            $where = ['pid'=>$pid,'status'=>'normal','type'=>['in',$type]];
        }else{
            $where = ['pid'=>$pid,'status'=>'normal'];
        }
        // return $where;
        $parentNav = Db::name('category')->where($where)->order('weigh desc')->field('id,type,name,pid,image')->select();
        $result = $this->getChildren($parentNav);
        return $result;
    }

    /**
     * 获取文章列表
     *
     * @param string $category_id 导航ID
     * @return  array
     */
    public function getArticleList($category_id, $page, $num)
    {   
        $categoryInfo = Db::name('category')->where(['id'=>$category_id,'status'=>'normal'])->field('pid,type')->find();
        // 文章分类获取列表
        if($categoryInfo['type'] == 'article'){
            if($categoryInfo['pid'] == 0){
                $list = Db::name('article')->where(['category_id'=>$category_id,'status'=>'normal'])->field('id,title,image,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->order('weigh desc, createtime')->page($page,$num)->select();
                if($list){
                    $result = ['nav'=>[],'list'=>$list];
                }else{
                    $result = $list;
                }
            }else{
                $nav = Db::name('category')->where(['pid'=>$categoryInfo['pid'],'status'=>'normal'])->field('id,name')->select();
                $list = Db::name('article')->where(['category_id'=>$nav[0]['id'],'status'=>'normal'])->field('id,title,image,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->order('weigh desc, createtime')->page($page,$num)->select();
                $count = Db::name('article')->where(['category_id'=>$nav[0]['id'],'status'=>'normal'])->count();
                $result = ['nav'=>$nav,'list'=>$list,'count'=>$count];
            }
        }
        // 单页分类获取文章
        if($categoryInfo['type'] == 'page'){
            if($categoryInfo['pid'] == 0){
                $list = Db::name('article')->where(['category_id'=>$category_id,'status'=>'normal'])->field('id,title,content,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->find();
                if($list){
                    $result = ['nav'=>[],'list'=>$list];
                }else{
                    $result = $list;
                }
            }else{
                $nav = Db::name('category')->where(['pid'=>$categoryInfo['pid'],'status'=>'normal'])->field('id,name')->select();
                $list = Db::name('article')->where(['category_id'=>$nav[0]['id'],'status'=>'normal'])->field('id,title,content,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->find();
                $count = Db::name('article')->where(['category_id'=>$nav[0]['id'],'status'=>'normal'])->count();
                $result = ['nav'=>$nav,'list'=>$list,'count'=>$count];
            }
        }
        return $result;
    }

    /**
     * 获取文章详情
     *
     * @param string $article_id 文章ID
     * @return  array
     */
    public function getArticleDetails($article_id)
    {   
        Db::name('article')->where(['id'=>$article_id,'status'=>'normal'])->setInc('views');
        $data = Db::name('article')->where(['id'=>$article_id,'status'=>'normal'])->field('id,title,content,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->find();
        $prev = Db::name('article')->where(['id'=>['<',$article_id],'status'=>'normal'])->order('id desc')->field('id,title')->find();
        $next = Db::name('article')->where(['id'=>['>',$article_id],'status'=>'normal'])->order('id asc')->field('id,title')->find();
        return ['prev'=>$prev,'next'=>$next,'data'=>$data];
    }

    /**
     * 获取热门文章
     *
     * @param string $category_id 导航ID
     * @return  array
     */
    public function getHotArticle($page, $num)
    {   
        $result = Db::name('article')->where(['status'=>'normal'])->field('id,title,image,views,description,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->order('views desc, weigh,createtime')->page($page,$num)->select();
        return $result;
    }

    /**
     * 获取友情链接
     *
     * @param string $location 位置
     * @return  array
     */
    public function getFriendLink($location)
    {   
        if($location){
            $where = ['location'=>$location,'status'=>'normal'];
        }
        else{
            $where = ['status'=>'normal'];
        }
        $result = Db::name('friend_link')->where($where)->field('id,name,href')->order('weigh desc')->select();
        return $result;
    }

    /**
     * 获取站点信息
     *
     * @param string $name 名称
     * @return  array
     */
    public function getSiteInfo($name)
    {   
        if($name){
            $where = ['name'=>['in',$name],'group'=>'basic','name'=>['<>','fixedpage']];
        }
        else{
            $where = ['group'=>'basic','name'=>['<>','fixedpage']];
        }
        $result = Db::name('config')->where($where)->field('name,value')->select();
        return $result;
    }

    /**
     * 获取轮播图
     *
     * @param string $name 名称
     * @return  array
     */
    public function getBanner($atter)
    {   
        if($atter){
            $where = ['atter'=>$atter,'status'=>'normal'];
        }
        else{
            $where = ['status'=>'normal'];
        }
        $result = Db::name('banner')->where($where)->field('image,url,attr')->order('weigh desc')->select();
        return $result;
    }

    /**
     * 获取广告
     *
     * @param string $province 省份
     * @param string $city 城市
     * @param string $area 县区
     * @return  array
     */
    public function getAdvertList($province, $city, $area, $page, $num)
    {   
        $where = ['province'=>$province,'status'=>'normal'];
        if($city){
            $where = $where + ['city'=>$city];
        }
        if($area){
            $where = $where + ['area'=>$area];
        }
        $result = Db::name('advert')->where($where)->field('id,title,image,lng,lat')->order('weigh desc')->page($page, $num)->select();
        return $result;
    }

    /**
     * 获取广告详情
     *
     * @param string $advert_id 广告ID
     * @return  array
     */
    public function getAdvertDetails($advert_id)
    {   
        Db::name('advert')->where(['id'=>$advert_id,'status'=>'normal'])->setInc('views');
        $result = Db::name('advert')->where(['id'=>$advert_id,'status'=>'normal'])->field('id,title,content,FROM_UNIXTIME(createtime,"%Y-%m-%d") as createtime')->find();
        return $result;
    }





    /**
     * 子导航
     *
     * @param array $data 父级数据
     * @return  array
     */
    private function getChildren($data){
        foreach ($data as $key => $val){
            // 类型为单页（page）和导航（navigation）的不查子级
            if($val['type'] != 'page' && $val['type'] != 'navigation'){
                $parentNav = Db::name('category')->where(['pid'=>$val['id'],'status'=>'normal'])->order('weigh desc')->field('id,type,name,pid,image')->select();
                if($parentNav){
                    $data[$key]['son_nav'] = $parentNav;
                }else{
                    $data[$key]['son_nav'] = [];
                }
            }else{
                $data[$key]['son_nav'] = [];
            }
        }
        // 下面的循环是子级无限分类，隐藏以后就只有一层子级
        foreach ($data as $k => $v){
            if(count($v['son_nav']) != 0){
                $data[$k]['son_nav'] = $this->getChildren($v['son_nav']);
            }
        }
        return $data;
    }


    
}

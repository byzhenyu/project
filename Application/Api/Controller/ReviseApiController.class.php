<?php
/**
 * @desc 闪荐二期修改接口功能
 */
namespace Api\Controller;
use Common\Controller\ApiUserCommonController;

class ReviseApiController extends ApiUserCommonController{

    /**
     * @desc 更改求职状态
     * @param incumbency 1、接收推荐 0、不接受推荐
     * @extra TODO 短信发送
     */
    public function setIncumbency(){
        $user_model = D('Admin/User');
        $incumbency = I('incumbency', 0, 'intval');
        $save = array('is_incumbency' => $incumbency);
        $res = $user_model->where(array('user_id' => UID))->save($save);
        if(false !== $res){
            $this->apiReturn(V(1, '求职状态更改成功！'));
        }
        else{
            $this->apiReturn(V(0, '求职状态更改失败！'));
        }
    }

    /**
     * @desc 获取公告详情
     */
    public function getArticleInfo(){
        $article_id = I('id', 0, 'intval');
        $this->apiReturn(V(1, '', C('IMG_SERVER').'/index.php/Api/PublicApi/noticeInfo/id/'.$article_id));
    }

    /**
     * @desc 申请成为合伙人
     */
    public function applyPartner(){
        $data = I('post.', '');
        $model = D('Admin/Partner');
        $create = $model->create($data, 1);
        if(false !== $create){
            $res = $model->add($data);
            if($res){
                $this->apiReturn(V(1, '合伙人申请成功！'));
            }
        }
        $this->apiReturn(V(0, $model->getError()));
    }

    /**
     * @desc 获取首页数据
     * @param position_id int 导航栏目位置 1、HR  0、求职者
     * @param ad_position int banner图位置 1、首页[默认] 2、合伙人
     * @extra nav:导航栏目 notice:公告列表 banner:banner轮播图
     */
    public function getHomeData(){
        $return_list = array();
        $return_list['nav'] = $this->navList();
        $return_list['notice'] = $this->noticeList();
        $return_list['banner'] = $this->bannerList();
        $this->apiReturn(V(1, '首页数据', $return_list));
    }

    /**
     * @desc 获取导航栏目列表
     * @param 1、HR端 0、求职者端
     */
    private function navList(){
        $nav_model = D('Admin/Nav');
        $position = I('position', 1, 'intval');
        $nav_where = array('position' => $position);
        $list = $nav_model->navList($nav_where);
        return $list;
    }

    /**
     * @desc 获取公告列表
     */
    private function noticeList(){
        $model = D('Admin/Article');
        $notice_where = array('article_cat_id' => 6, 'display' => 1);
        $list = $model->getArticleList($notice_where);
        return $list['articlelist'];
    }

    /**
     * @desc 轮播图列表
     */
    private function bannerList(){
        $model = D('Admin/Ad');
        $position = I('ad_position', 1, 'intval');
        $where = array('position_id' => $position, 'display' => 1);
        $field = 'title,content';
        $list = $model->getAdlist($where, $field);
        return $list['info'];
    }
}
<?php
/**
 * 悬赏管理控制器
 */
namespace Admin\Controller;
use Think\Controller;
class RecruitController extends CommonController {

    //悬赏列表
    public function listRecruit(){
        $keywords = I('keyword', '', 'trim');
        $where = array();
        $model = D('Admin/Recruit');
        if($keywords) $where['position_name|job_area'] = array('like', '%'.$keywords.'%');
        $list = $model->getRecruitList($where);
        $user_model = D('Admin/User');
        foreach($list['info'] as &$val){
            $where = array('user_id' => $val['hr_user_id']);
            $user_info = $user_model->getUserInfo($where, 'nickname,user_name');
            $val['release_name'] = !empty($user_info['nickname']) ? $user_info['nickname'] : $user_info['user_name'];
        }
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->keyword = $keywords;
        $this->display();
    }

    /**
     * @desc 悬赏详情
     */
    public function seeRecruitDetail(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id);
        $model = D('Admin/Recruit');
        $userModel = D('Admin/User');
        $info = $model->getRecruitInfo($where);
        $user_where = array('user_id' => $info['hr_user_id']);
        $user_info = $userModel->getUserInfo($user_where, 'nickname,user_name');
        $info['release_name'] = !empty($user_info['nickname']) ? $user_info['nickname'] : $user_info['user_name'];
        $this->info = $info;
        $this->display();
    }

    /**
     * @desc 悬赏推荐列表
     */
    public function listRecruitResume(){
        $recruit_id = I('id', 0, 'intval');
        $keywords = I('keyword', '', 'trim');
        $model = D('Admin/RecruitResume');
        $where = array('r.recruit_id' => $recruit_id);
        if($keywords) $where['r.recommend_label'] = array('like', '%'.$keywords.'%');
        $list = $model->getResumeListByPage($where);
        $this->list = $list['info'];
        $this->keyword = $keywords;
        $this->page = $list['page'];
        $this->display();
    }

    public function del(){
        $this->_del('Recruit', 'id');
    }
}
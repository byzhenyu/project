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
        $info['experience'] = C('WORK_EXP')[$info['experience']];
        $education = D('Admin/Education')->getEducationInfo(array('id' => $info['degree']));
        $info['education_name'] = $education['education_name'] == '不限' ? '不限' : $education['education_name'].'或'.$education['education_name'].'以上';
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

    /**
     * @desc 悬赏佣金情况
     */
    public function seeRecruitAccountLog(){
        $recruit_id = I('recruit_id', 0, 'intval');
        $recruit_resume_model = D('Admin/RecruitResume');
        $recruit_resume = $recruit_resume_model->recruitResumeStatistic(array('recruit_id' => $recruit_id));
        if(count($recruit_resume) > 0){
            $order_sn = array();
            $keywords = I('keyword', '', 'trim');
            foreach($recruit_resume as &$val) $order_sn[] = $val['id']; unset($val);
            $where = array('change_type' => array('in', array(2,3,7)), 'order_sn' => array('in', $order_sn));
            if($keywords) $where['u.mobile|u.nickname'] = array('like', '%'.$keywords.'%');
            $data = D('Admin/AccountLog')->getRecruitAccountList($where);
            foreach($data['info'] as &$val){
                $val['user_money'] = fen_to_yuan($val['user_money']);
                $val['diss_string'] = '冻结中';
                if($val['diss'] == 1) $val['diss_string'] = '已结算';
                $val['change_time'] = time_format($val['change_time'], 'Y-m-d');
            }
            unset($val);
        }
        $this->info = $data['info'];
        $this->page = $data['page'];
        $this->display();
    }

    public function del(){
        $this->_del('Recruit', 'id');
    }
}
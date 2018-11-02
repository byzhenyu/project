<?php
/**
 * 悬赏管理控制器
 */
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class RecruitController extends HrCommonController {
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
        $this->info = $info;
        $this->display();
    }
}
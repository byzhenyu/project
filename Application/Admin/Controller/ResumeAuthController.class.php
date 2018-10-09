<?php
/**
 * 简历认证控制器
 */
namespace Admin\Controller;
use Think\Controller;
class ResumeAuthController extends CommonController {

    /**
     * @desc 简历认证列表
     */
    public function getResumeAuthList(){
        $model = D('Admin/ResumeAuth');
        $keywords = I('keyword', '', 'trim');
        $where = array();
        if($keywords) $where['r.true_name|u.user_name|u.nickname'] = array('like', '%'.$keywords.'%');
        $field = 'a.add_time,a.auth_result,r.head_pic,r.true_name,a.resume_id,a.id,r.age,r.sex,r.update_time,u.nickname,u.user_name,r.job_area,r.job_intension,r.first_degree';
        $list = $model->getResumeAuthList($where, $field);
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->keyword = $keywords;
        $this->display();
    }
}
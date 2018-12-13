<?php
/**
 * 人才库控制器
 */
namespace Admin\Controller;
use Think\Controller;
class HrResumeController extends CommonController {

    /**
     * @desc 获取简历人才库列表
     */
    public function getHrResumeList(){
        $model = D('Admin/HrResume');
        $keywords = I('keyword', '', 'trim');
        $where = array();
        $field = 'h.id,h.resume_id,r.true_name,r.head_pic,h.add_time,r.age,r.sex,u.nickname,u.user_name,r.job_area,r.job_intension,first_degree,r.position_id';
        if($keywords) $where['r.true_name|u.user_name'] = array('like', '%'.$keywords.'%');
        $list = $model->getHrResumeList($where, $field);
        $position = D('Admin/Position');
        foreach($list['info'] as &$val){
            if(!$val['job_intension']) $val['job_intension'] = $position->getPositionField(array('id' => $val['position_id']), 'position_name');
        }
        unset($val);
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->keyword = $keywords;
        $this->display();
    }
}
<?php
/**
 * 简历控制器
 */
namespace Admin\Controller;
use Think\Controller;
class ResumeController extends CommonController {

    public function listHrResume(){
        $user_id = I('user_id', 0, 'intval');
        $where = array('h.hr_user_id' => $user_id);
        $model = D('Admin/HrResume');
        $keywords = I('keywords', '', 'trim');
        if($keywords) $where['r.true_name'] = array('like', '%'.$keywords.'%');
        $field = 'h.id,h.resume_id,r.true_name,r.head_pic,h.add_time,r.age,r.sex,r.job_intension,r.job_area,r.career_label';
        $list = $model->getHrResumeList($where, $field);
        foreach($list['info'] as &$val){
            $val['add_time'] = time_format($val['add_time']);
        }
        $this->keyword = $keywords;
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    /**
     * @desc 获取简历详情
     */
    public function getResumeDetail(){
        $user_id = I('user_id', 0, 'intval');
        $resume_id = I('resume_id', 0, 'intval');
        $resumeModel = D('Admin/Resume');
        $resumeWorkModel = D('Admin/ResumeWork');
        $resumeEduModel = D('Admin/ResumeEdu');
        $resumeEvaluationModel = D('Admin/ResumeEvaluation');
        $resume_where = array('user_id' => $user_id);
        if($resume_id) $resume_where = array('id' => $resume_id);
        $resumeDetail = $resumeModel->getResumeInfo($resume_where);
        $where = array('resume_id' => $resume_id);
        $resumeWorkList = $resumeWorkModel->getResumeWorkList($where);
        $resumeEduList = $resumeEduModel->getResumeEduList($where);
        foreach($resumeWorkList as &$wval){
            $wval['starttime'] = time_format($wval['starttime'], 'Y-m-d');
            $wval['endtime'] = time_format($wval['endtime'], 'Y-m-d');
        }
        unset($wval);
        foreach($resumeEduList as &$eval){
            $eval['starttime'] = time_format($eval['starttime'], 'Y-m-d');
            $eval['endtime'] = time_format($eval['endtime'], 'Y-m-d');
        }
        unset($eval);
        $resumeEvaluation = $resumeEvaluationModel->getResumeEvaluationAvg($where);
        $sum = array_sum(array_values($resumeEvaluation));
        $avg = round($sum/(count($resumeEvaluation)), 2);
        $return = array('detail' => $resumeDetail, 'resume_work' => $resumeWorkList, 'resume_edu' => $resumeEduList, 'resume_evaluation' => $resumeEvaluation, 'evaluation_avg' => $avg);
        $this->info = $return;
        $this->display();
    }

    public function del(){
        $this->_del('Resume', 'id');
    }
}
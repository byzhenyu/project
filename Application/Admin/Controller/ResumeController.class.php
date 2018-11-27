<?php
/**
 * 简历控制器
 */
namespace Admin\Controller;
use Think\Verify;

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
        $userModel = D('Admin/User');
        if(IS_POST){
            $is_audit = I('is_audit', 0, 'intval');
            $audit_where = array('id' => $resume_id);
            $save = array('is_audit' => $is_audit);
            $res = $resumeModel->saveResumeData($audit_where, $save);
            if(false !== $res){
                if($is_audit == 1){
                    $resume_audit_info = $resumeModel->getResumeInfo($audit_where);
                    //审核通过 更新HR tags标签/更新HR人才库
                    $hr_data = array('resume_id' => $resume_id, 'hr_user_id' => $resume_audit_info['user_id']);
                    $hrModel = D('Admin/HrResume');
                    $hr_create = $hrModel->create($hr_data, 1);
                    if(false !== $hr_create){
                        $hr_res = $hrModel->add($hr_data);
                        if(false !== $hr_res){
                            $task_id = 3;
                            //TODO 简历审核任务完成情况 根据简历更新验证任务完成次数
                            add_task_log($resume_audit_info['user_id'], $task_id, '', $resume_audit_info['update_time']);
                            header('Content-Type:application/json; charset=utf-8');
                            echo json_encode(V(1, '审核成功！', $resume_id));
                            fastcgi_finish_request();
                            set_time_limit(0);
                            refreshUserTags($resume_audit_info['user_id'], $res);
                        }
                        else{
                            $this->ajaxReturn(V(1, '审核成功！'));
                        }
                    }
                    else{
                        $this->ajaxReturn(V(1, '审核成功！'));
                    }
                }
                else{
                    $this->ajaxReturn(V(1, '审核成功！'));
                }
            }
            else{
                $this->ajaxReturn(V(0, $resumeModel->getError()));
            }
        }
        else{
            $resume_where = array('user_id' => $user_id);
            if($resume_id) $resume_where = array('id' => $resume_id);
            $resumeDetail = $resumeModel->getResumeInfo($resume_where);
            $where = array('resume_id' => $resume_id);
            $resumeWorkList = $resumeWorkModel->getResumeWorkList($where);
            $resumeEduList = $resumeEduModel->getResumeEduList($where);
            foreach($resumeWorkList as &$wval){
                $wval['starttime'] = time_format($wval['starttime'], 'Y-m-d');
                $wval['endtime'] = $wval['endtime'] ? time_format($wval['endtime'], 'Y-m-d') : '至今';
            }
            unset($wval);
            foreach($resumeEduList as &$eval){
                $eval['starttime'] = time_format($eval['starttime'], 'Y-m-d');
                $eval['endtime'] = $eval['endtime'] ? time_format($eval['endtime'], 'Y-m-d') : '至今';
            }
            unset($eval);
            $resumeEvaluation = $resumeEvaluationModel->getResumeEvaluationAvg($where);
            $sum = array_sum(array_values($resumeEvaluation));
            $avg = round($sum/(count($resumeEvaluation)), 2);
            $user_where = array('user_id' => $resumeDetail['user_id']);
            $user_type = $userModel->getUserField($user_where, 'user_type');
            if($user_type == 1 && $resumeDetail['is_audit'] != 1) $resumeDetail['can_audit'] = 1;
            $return = array('detail' => $resumeDetail, 'resume_work' => $resumeWorkList, 'resume_edu' => $resumeEduList, 'resume_evaluation' => $resumeEvaluation, 'evaluation_avg' => $avg);
            $this->info = $return;
            $this->display();
        }
    }

    /**
     * @desc 闪荐二期  新增简历审核功能
     */
    public function auditResumeList(){
        $resume_model = D('Admin/Resume');
        $keywords = I('keyword', '', 'trim');
        $where = array('u.user_type' => 1, 'r.is_audit' => array('neq', 1));
        if($keywords) $where['u.nickname|u.mobile'] = array('like', '%'.$keywords.'%');
        $list = $resume_model->getAuditResumeList($where);
        foreach($list['info'] as &$val){
            $val['update_time'] = time_format($val['update_time'], 'Y-m-d H:i');
            $val['nickname'] = $val['nickname'] ? $val['nickname'] : $val['user_name'];
        }
        unset($val);
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->keyword = $keywords;
        $this->display();
    }

    public function del(){
        $this->_del('Resume', 'id');
    }
}
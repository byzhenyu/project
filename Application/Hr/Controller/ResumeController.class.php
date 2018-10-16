<?php
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class ResumeController extends HrCommonController {

    /**
     * @desc 获取hr简历人才库列表
     */
    public function listHrResume(){
        $resume_name = I('keywords', '', 'trim');
        $model = D('Admin/HrResume');
        $where = array('h.hr_user_id' => HR_ID);
        //多功能检索处理
        $true_name = I('true_name', '', 'trim');
        $mobile = I('mobile', '', 'trim');
        if($true_name) $resume_name = $true_name;
        if($mobile) $resume_name = $mobile;
        $email = I('email', '', 'trim');
        if($email) $where['r.email'] = array('like', '%'.$email.'%');
        $sex = I('sex', 0, 'intval');
        if(in_array($sex, array(1, 2))) $where['r.sex'] = $sex;
        $age_min = I('age_min', 0, 'intval');
        $age_max = I('age_max', 0, 'intval');
        if($age_min && $age_max){
            $where['r.age'] = array('between', array($age_min, $age_max));
        }
        else if($age_min){
            $where['r.age'] = array('egt', $age_min);
        }
        else if($age_max){
            $where['r.age'] = array('elt', $age_max);
        }
        $post_nature = I('post_nature', '', 'trim');
        if($post_nature) $where['r.post_nature'] = $post_nature;
        $job_intension = I('job_intension', '', 'trim');
        if($job_intension) $where['r.job_intension'] = array('like', '%'.$job_intension.'%');
        $job_area = I('job_area', '', 'trim');
        if($job_area) $where['r.job_area'] = array('like', '%'.$job_area.'%');
        $career_label = I('career_label', '', 'trim');
        if($career_label) $where['r.career_label'] = array('like', '%'.$career_label.'%');

        if($resume_name) $where['r.true_name|r.mobile'] = array('like', '%'.$resume_name.'%');
        $list = $model->getHrResumeList($where, 'h.id as hr_resume_id,h.hr_user_id,r.*');
        foreach($list['info'] as &$val){
            if($val['hr_user_id'] == $val['user_id']) $val['is_edit'] = 1;
        }
        $this->keywords = $resume_name;
        $this->page = $list['page'];
        $this->info = $list['info'];
        $this->display();
    }

    /**
     * @desc 编辑简历
     */
    public function editResume(){
        $model = D('Admin/Resume');
        $resume_id = I('resume_id', 0, 'intval');
        $data = I('post.');
        if(IS_POST){
            $op_arr = array('job_intension', 'job_area', 'career_label');
            foreach($op_arr as &$op){
                if($data[$op]) $data[$op] = implode(',', $data[$op]);
            }
            $data['user_id'] = HR_ID;
            if($resume_id){
                $create = $model->create($data, 1);
                if(false !== $create){
                    $res = $model->where(array('id' => $resume_id))->save($data);
                    if(false !== $res){
                        $this->ajaxReturn(V(1, '保存成功！'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
                else{
                    $this->ajaxReturn(V(0, $model->getError()));
                }
            }
            else{
                M()->startTrans();
                $create = $model->create($data, 2);
                if(false !== $create){
                    $res = $model->add($data);
                    if(false !== $res){
                        $tag_recommend = I('post.recommend');
                        if($tag_recommend) $tag_recommend = implode(',', $tag_recommend);
                        $hr_data = array('resume_id' => $res, 'hr_user_id' => HR_ID, 'recommend_label' => $tag_recommend);
                        $hrModel = D('Admin/HrResume');
                        $hr_create = $hrModel->create($hr_data, 1);
                        if(false !== $hr_create){
                            $hr_res = $hrModel->add();
                            if(false !== $hr_res){
                                M()->commit();
                                $this->ajaxReturn(V(1, '保存成功！'));
                            }
                            else{
                                M()->rollback();
                                $this->ajaxReturn(V(0, $hrModel->getError()));
                            }
                        }
                        else{
                            M()->rollback();
                            $this->ajaxReturn(V(0, $hrModel->getError()));
                        }
                    }
                    else{
                        M()->rollback();
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
                else{
                    M()->rollback();
                    $this->ajaxReturn(V(0, $model->getError()));
                }
            }
        }
        $work_nature = C('WORK_NATURE');
        $arr_values = array_values($work_nature);
        $nature_arr = array();
        $tags_where = array('tags_type' => array('in', array(1,2,5)));
        $tagsModel = D('Admin/Tags');
        $tags_info = $tagsModel->getTagsList($tags_where, 'id,tags_name,tags_type');
        $tags_intension = array();
        $tags_career = array();
        $tags_recommend = array();
        foreach($tags_info as &$val){
            if(1 == $val['tags_type']) $tags_career[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            if(2 == $val['tags_type']) $tags_intension[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            if(5 == $val['tags_type']) $tags_recommend[] = array('id' => $val['id'], 'name' => $val['tags_name']);
        }
        unset($val);
        foreach($arr_values as &$val){
            $nature_arr[] = array('id' => $val, 'name' => $val);
        }
        unset($val);
        $area = D('Admin/Region')->getRegionList(array('level' => 2), 'id,name');
        $resume_where = array('id' => $resume_id);
        $info = $model->getResumeInfo($resume_where);
        $this->info = $info;
        $this->recommend = $tags_recommend;
        $this->area = $area;
        $this->intension = $tags_intension;
        $this->career = $tags_career;
        $this->nature = $nature_arr;
        $this->display();
    }

    /**
     * 简历详情
     */
    public function seeResumeDetail(){
        $resume_id = I('id', 0, 'intval');
        $resumeModel = D('Admin/Resume');
        $resumeWorkModel = D('Admin/ResumeWork');
        $resumeEduModel = D('Admin/ResumeEdu');
        $resumeEvaluationModel = D('Admin/ResumeEvaluation');
        $resume_where = array('id' => $resume_id);
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

    /**
     * @desc 检索条件
     */
    public function researchResume(){
        $work_nature = C('WORK_NATURE');
        $arr_values = array_values($work_nature);
        $nature_arr = array();
        $tags_where = array('tags_type' => array('in', array(1,2,5)));
        $tagsModel = D('Admin/Tags');
        $tags_info = $tagsModel->getTagsList($tags_where, 'id,tags_name,tags_type');
        $tags_intension = array();
        $tags_career = array();
        $tags_recommend = array();
        foreach($tags_info as &$val){
            if(1 == $val['tags_type']) $tags_career[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            if(2 == $val['tags_type']) $tags_intension[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            if(5 == $val['tags_type']) $tags_recommend[] = array('id' => $val['id'], 'name' => $val['tags_name']);
        }
        unset($val);
        foreach($arr_values as &$val){
            $nature_arr[] = array('id' => $val, 'name' => $val);
        }
        unset($val);

        $area = D('Admin/Region')->getRegionList(array('level' => 2), 'id,name');
        $this->recommend = $tags_recommend;
        $this->area = $area;
        $this->intension = $tags_intension;
        $this->career = $tags_career;
        $this->nature = $nature_arr;
        $this->display();
    }

    /**
     * @desc 意见反馈
     */
    public function editFeedBack(){
        $data = I('post.', '');
        $data['user_id'] = HR_ID;
        if(IS_POST){
            $model = D('Admin/FeedBack');
            $create = $model->create($data, 1);
            if(false !== $create){
                $res = $model->add($data);
                if($res){
                    $this->ajaxReturn(V(1, '反馈成功！'));
                }
                else{
                    $this->ajaxReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->ajaxReturn(V(0, $model->getError()));
            }
        }
        $this->display();
    }
}
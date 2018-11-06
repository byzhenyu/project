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
        $age_min = I('age_min', '', 'trim');
        $age_max = I('age_max', '', 'trim');
        if($age_min && $age_max){
            $age_min = strtotime($age_min);
            $age_max = strtotime($age_max);
            $where['r.age'] = array('between', array($age_min, $age_max));
        }
        else if($age_min){
            $age_min = strtotime($age_min);
            $where['r.age'] = array('egt', $age_min);
        }
        else if($age_max){
            $age_max = strtotime($age_max);
            $where['r.age'] = array('elt', $age_max);
        }
        $post_nature = I('post_nature', '', 'trim');
        if($post_nature) $where['r.post_nature'] = $post_nature;
        $province = I('province', '', 'trim');
        $job_area = I('job_area', '', 'trim');
        $county = I('county', '', 'trim');
        if($county) $where['r.job_intension'] = $province.','.$job_area.','.$county;
        $job_area = I('job_area', '', 'trim');
        if($job_area) $where['r.job_area'] = array('like', '%'.$job_area.'%');
        $career_label = I('career_label', '', 'trim');
        if($career_label) $where['r.career_label'] = array('like', '%'.$career_label.'%');

        if($resume_name) $where['r.true_name|r.mobile'] = array('like', '%'.$resume_name.'%');
        $list = $model->getHrResumeList($where, 'h.id as hr_resume_id,h.hr_user_id,r.*');
        foreach($list['info'] as &$val){
            if($val['hr_user_id'] == $val['user_id']) $val['is_edit'] = 1;
        }
        unset($val);
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
            $data['user_id'] = HR_ID;
            $data['age'] = strtotime($data['age']);
            $data['job_area'] = $data['province'].','.$data['job_area'].','.$data['county'];
            $data['address'] = $data['t_province'].','.$data['t_job_area'].','.$data['t_county'].' '.$data['address'];
            $op_arr = array('career_label', 'language_ability');
            foreach($op_arr as &$op){
                if($data[$op]) $data[$op] = implode(',', $data[$op]);
            }
            if($resume_id){
                $create = $model->create($data, 1);
                $check_res = D('Admin/HrResume')->checkHrResumeMobile($data['mobile'], $resume_id, HR_ID);
                if($check_res) $this->ajaxReturn(V(0, '该手机号已存在！'));
                if(false !== $create){
                    $res = $model->where(array('id' => $resume_id))->save($data);
                    if(false !== $res){
                        header('Content-Type:application/json; charset=utf-8');
                        echo json_encode(V(1, '保存成功', $resume_id));
                        fastcgi_finish_request();
                        set_time_limit(0);
                        refreshUserTags(false, $resume_id, array('job_position' => $data['position_id'], 'job_area' => $data['job_area']));
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
                $check_res = D('Admin/HrResume')->checkHrResumeMobile($data['mobile'], false, HR_ID);
                if($check_res) $this->ajaxReturn(V(0, '该手机号已存在！'));
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
                                refreshUserTags(HR_ID, $res);
                                M()->commit();
                                //$this->addResumeWorkEducation($res);
                                $this->ajaxReturn(V(1, '保存成功！', $res));
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
        else{
            $work_nature = C('WORK_NATURE');
            $language = C('SHAN_LANGUAGE');
            $arr_values = array_values($work_nature);
            $lang_values = array_values($language);
            $nature_arr = array();
            $language_arr = array();
            $tags_where = array('tags_type' => array('in', array(1,2,5)));
            $tagsModel = D('Admin/Tags');
            $tags_info = $tagsModel->getTagsList($tags_where, 'id,tags_name,tags_type');
            $tags_career = array();
            $tags_recommend = array();
            foreach($tags_info as &$val){
                if(1 == $val['tags_type']) $tags_career[] = array('id' => $val['id'], 'name' => $val['tags_name']);
                if(5 == $val['tags_type']) $tags_recommend[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            }
            unset($val);
            foreach($arr_values as &$val){
                $nature_arr[] = array('id' => $val, 'name' => $val);
            }
            unset($val);
            foreach($lang_values as &$val){
                $language_arr[] = array('id' => $val, 'name' => $val);
            }
            unset($val);
            $area = D('Admin/Region')->getRegionList(array('level' => 2), 'id,name');
            $resume_where = array('id' => $resume_id);
            $edu_list = D('Admin/Education')->getEducationList();
            $info = $model->getResumeInfo($resume_where);
            if(!$info['age']) $info['age'] = time();
            if($info['address']){
                $address = explode(' ' ,$info['address']);
                if(count($address) > 0){
                    $info['address_p'] = $address[0];
                    $add_help = explode(',', $address[0]);
                    $info['address1'] = $add_help[0];
                    $info['address2'] = $add_help[1];
                    $info['address3'] = $add_help[2];
                    unset($address[0]);
                    $info['address'] = str_replace($info['address_p'].' ', '', $info['address']);
                }
            }
            $industry = D('Admin/Industry')->getIndustryList();
            if($info['language_ability']){
                $language = explode(',', $info['language_ability']);
                foreach($language_arr as &$lan_val){
                    $lan_val['sel'] = 0;
                    if(in_array($lan_val['name'], $language)) $lan_val['sel'] = 1;
                }
                unset($lan_val);
            }
            $info['position_parent'] = 0;
            if(!$info['position_id']) $info['position_id'] = 0;
            if($info['position_id']) $info['position_parent'] = D('Admin/Position')->getPositionField(array('id' => $info['position_id']), 'parent_id');
            if($info['job_area']){
                $job_area = explode(',', $info['job_area']);
                $info['job_area1'] = $job_area[0];
                $info['job_area2'] = $job_area[1];
                $info['job_area3'] = $job_area[2];
            }

            //教育经历
            $resume_edu_where = array('resume_id' => $resume_id);
            $resume_edu_model = D('Admin/ResumeEdu');
            $resume_edu_list = $resume_edu_model->getResumeEduList($resume_edu_where);
            foreach($resume_edu_list as &$val){
                $val['starttime'] = time_format($val['starttime'], 'Y-m-d');
                if($val['endtime']){
                    $val['endtime'] = time_format($val['endtime'], 'Y-m-d');
                }
                else{
                    $val['endtime'] = '至今';
                }
            }
            unset($val);
            //工作经历
            $resume_work_where = array('resume_id' => $resume_id);
            $resume_work_model = D('Admin/ResumeWork');
            $resume_work_list = $resume_work_model->getResumeWorkList($resume_work_where);
            foreach($resume_work_list as &$val){
                $val['starttime'] = time_format($val['starttime'], 'Y-m-d');
                if($val['endtime']){
                    $val['endtime'] = time_format($val['endtime'], 'Y-m-d');
                }
                else{
                    $val['endtime'] = '至今';
                }
            }

            $this->resume_edu_list = $resume_edu_list;
            $this->resume_work_list = $resume_work_list;

            $this->industry = $industry;
            $this->info = $info;
            $this->lang = $language_arr;
            $this->edu_list = $edu_list;
            $this->recommend = $tags_recommend;
            $this->area = $area;
            $this->career = $tags_career;
            $this->nature = $nature_arr;
            $this->display();
        }
    }

    /**
     * @desc 新建简历   新增教育经历/工作经历
     * @param $resume_id int 简历id
     * @return bool
     */
    private function addResumeWorkEducation($resume_id){
        if(!$resume_id) return false;
        $edu_model = D('Admin/ResumeEdu');
        $work_model = D('Admin/ResumeWork');
        $data = I('post.', '');
        $edu_field = array('resume_id' => $resume_id, 'school_name' => $data['school_name'], 'degree' => $data['degree'], 'major' => $data['major'], 'starttime' => strtotime($data['edu_starttime']), 'endtime' => strtotime($data['edu_endtime']), 'describe' => $data['edu_describe']);
        $work_field = array('resume_id' => $resume_id, 'company_name' => $data['company_name'], 'position' => $data['position'], 'starttime' => strtotime($data['work_starttime']), 'endtime' => strtotime($data['work_endtime']), 'describe' => $data['work_describe']);
        if($data['edu_is_current'] == 1) $edu_field['endtime'] = 0;
        if($data['work_is_current'] == 1) $work_field['endtime'] = 0;
        $edu_create = $edu_model->create($edu_field, 1);
        if(false !== $edu_create){
            $edu_model->add($edu_field);
        }
        $work_create = $work_model->create($work_field, 1);
        if(false !== $work_create){
            $work_model->add($work_field);
        }
        return true;
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
        $resumeDetail['age'] = time_to_age($resumeDetail['age']);
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

        $industry = D('Admin/Industry')->getIndustryList();
        $this->recommend = $tags_recommend;
        $this->industry = $industry;
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
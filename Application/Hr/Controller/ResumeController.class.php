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
     * @desc 获取简历详情
     */
    public function seeResumeDetail(){
        $resumeModel = D('Admin/Resume');
        $hrModel = D('Admin/HrResume');
        $resume_id = I('id', 0, 'intval');
        $hr_where = array('hr_user_id' => HR_ID, 'resume_id' => $resume_id);
        $resume_where = array('id' => $resume_id);
        $info = $resumeModel->getResumeInfo($resume_where, '*');
        $hrResumeInfo = $hrModel->getHrResumeInfo($hr_where);
        $info['recommend_label'] = $hrResumeInfo['recommend_label'];
        $this->info = $info;
        $this->display();
    }

    /**
     * @desc 获取简历教育经历列表
     */
    public function getResumeEduList()
    {
        $resume_id = I('resume_id', 0, 'intval');
        $keywords = I('keywords', '', 'trim');
        $resume_edu_where = array('resume_id' => $resume_id);
        $resume_edu_model = D('Admin/ResumeEdu');
        if ($keywords) $resume_edu_where['school_name'] = array('like', '%'.$keywords.'%');
        $list = $resume_edu_model->getResumeEduList($resume_edu_where);
        $this->list = $list;
        $this->keywords = $keywords;
        $this->display();
    }

    /**
     * @desc 获取简历工作经历列表
     */
    public function getResumeWorkList(){
        $resume_id = I('resume_id', 0, 'intval');
        $keywords = I('keywords', '', 'trim');
        $resume_work_where = array('resume_id' => $resume_id);
        $resume_work_model = D('Admin/ResumeWork');
        if($keywords) $resume_work_where['company_name'] = array('like', '%'.$keywords.'%');
        $list = $resume_work_model->getResumeWorkList($resume_work_where);
        $this->list = $list;
        $this->keywords = $keywords;
        $this->display();
    }
}
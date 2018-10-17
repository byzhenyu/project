<?php
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class ResumeEduController extends HrCommonController {

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
        $this->resume_id = $resume_id;
        $this->list = $list;
        $this->keywords = $keywords;
        $this->display();
    }

    /**
     * @desc 填写简历教育经历
     */
    public function editResumeEdu(){
        $data = I('post.');
        $data['resume_id'] = I('resume_id', 0, 'intval');
        $id = I('id', 0, 'intval');
        $model = D('Admin/ResumeEdu');
        $is_c = I('is_current', 0, 'intval');
        $data['starttime'] = strtotime($data['starttime']);
        $data['endtime'] = $is_c ? 0 : strtotime($data['endtime']);
        if(IS_POST){
            if($id > 0){
                $create = $model->create($data, 2);
                if(false !== $create){
                    $res = $model->save($data);
                    if(false !== $res){
                        $this->ajaxReturn(V(1, '学历信息保存成功！'));
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
                $create = $model->create($data, 1);
                if(false !== $create){
                    $res = $model->add($data);
                    if($res > 0){
                        $this->ajaxReturn(V(1, '学历信息保存成功！'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
                else{
                    $this->ajaxReturn(V(0, $model->getError()));
                }
            }
        }
        $info = $model->getResumeEduInfo(array('id' => $id));
        if(!$info['starttime']) $info['starttime'] = time();
        if(!$info['endtime']) $info['endtime'] = time();
        $info['resume_id'] = $data['resume_id'];
        $this->info = $info;
        $this->display();
    }

    public function del(){
        $this->_del('ResumeEdu', 'id');
    }
}
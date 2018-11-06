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
        foreach($list as &$val){
            $val['starttime'] = time_format($val['starttime'], 'Y-m-d');
            if($val['endtime']){
                $val['endtime'] = time_format($val['endtime'], 'Y-m-d');
            }
            else{
                $val['endtime'] = '至今';
            }
        }
        unset($val);
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
                        $info = $model->getResumeEduInfo(array('id' => $id));
                        $this->ajaxReturn(V(1, '学历信息保存成功！', $info['resume_id']));
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
                if(!$data['resume_id']) $this->ajaxReturn(V(0, '请先提交以上基本信息'));
                $create = $model->create($data, 1);
                if(false !== $create){
                    $res = $model->add($data);
                    if($res > 0){
                        $this->ajaxReturn(V(1, '学历信息保存成功！', $data['resume_id']));
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
        $info['is_current'] = 0;
        if(!$info['endtime'] && $id){
            $info['endtime'] = time();
            $info['is_current'] = 1;
        }
        if(!$info['endtime']) $info['endtime'] = time();
        $info['resume_id'] = $data['resume_id'] ? $data['resume_id'] : $info['resume_id'];

        $edu_list = D('Admin/Education')->getEducationList();
        $this->edu_list = $edu_list;
        $this->info = $info;
        $this->display();
    }

    public function delResumeEdu(){
        $id = I('id', 0, 'intval');
        $res = D('Admin/ResumeEdu')->where(array('id' => $id))->delete();
        if(false !== $res){
            $this->ajaxReturn(V(1, '删除成功！'));
        }
        else{
            $this->ajaxReturn(V(0, '操作错误！'));
        }
    }

    public function del(){
        $this->_del('ResumeEdu', 'id');
    }
}
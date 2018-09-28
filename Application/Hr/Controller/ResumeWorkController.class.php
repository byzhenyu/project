<?php
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class ResumeWorkController extends HrCommonController {

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
        $this->resume_id = $resume_id;
        $this->list = $list;
        $this->keywords = $keywords;
        $this->display();
    }

    /**
     * @desc 写工作经历
     */
    public function editResumeWork(){
        $data = I('post.');
        $id = I('id', 0, 'intval');
        $data['resume_id'] = I('resume_id', 0, 'intval');
        $model = D('Admin/ResumeWork');
        if(IS_POST){
            if($id > 0){
                $create = $model->create($data, 2);
                if(false !== $create){
                    $res = $model->save($data);
                    if(false !== $res){
                        $this->ajaxReturn(V(1, '保存成功！'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
            }
            else{
                $create = $model->create($data, 1);
                if (false !== $create){
                    $res = $model->add($data);
                    if($res > 0){
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
        }
        $where = array('id' => $id);
        $info = $model->getResumeWorkInfo($where);
        $info['resume_id'] = $data['resume_id'];
        if(!$info['starttime']) $info['starttime'] = time();
        if(!$info['endtime']) $info['endtime'] = time();
        $this->info = $info;
        $this->resume_id = $data['resume_id'];
        $this->display();
    }

    public function del(){
        $this->_del('ResumeWork', 'id');
    }
}
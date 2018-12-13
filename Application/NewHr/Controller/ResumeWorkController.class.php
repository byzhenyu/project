<?php
namespace NewHr\Controller;
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
        foreach($list as &$val){
            $val['starttime'] = time_format($val['starttime'], 'Y-m-d');
            if($val['endtime']){
                $val['endtime'] = time_format($val['endtime'], 'Y-m-d');
            }
            else{
                $val['endtime'] = '至今';
            }
        }
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
        $is_c = I('is_current', 0, 'intval');
        $data['starttime'] = strtotime($data['starttime']);
        $data['endtime'] = $is_c ? 0 : strtotime($data['endtime']);
        if(!$is_c) {
            if($data['starttime'] > $data['endtime']) $this->ajaxReturn(V(0, '结束时间不能小于开始时间'));
        }
        if(IS_POST){
            if($id > 0){
                $create = $model->create($data, 2);
                if(false !== $create){
                    $res = $model->save($data);
                    if(false !== $res){
                        $info = $model->getResumeWorkInfo(array('id' => $id));
                        $this->ajaxReturn(V(1, '保存成功！', $info['resume_id']));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
            }
            else{
                if(!$data['resume_id']) $this->ajaxReturn(V(0, '请先提交以上基本信息'));
                $create = $model->create($data, 1);
                if (false !== $create){
                    $res = $model->add($data);
                    if($res > 0){
                        $this->ajaxReturn(V(1, '保存成功！', $res));
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
        if(!$info['starttime']) $info['starttime'] = time();
        $info['is_current'] = 0;
        if(!$info['endtime'] && $id){
            $info['endtime'] = time();
            $info['is_current'] = 1;
        }
        if(!$info['endtime']) $info['endtime'] = time();
        $info['resume_id'] = $data['resume_id'] ? $data['resume_id'] : $info['resume_id'];
        $this->info = $info;
        $this->resume_id = $info['resume_id'];
        $this->display();
    }

    public function delResumeWork(){
        $id = I('id', 0, 'intval');
        $res = D('Admin/ResumeWork')->where(array('id' => $id))->delete();
        if(false !== $res){
            $this->ajaxReturn(V(1, '删除成功！'));
        }
        else{
            $this->ajaxReturn(V(0, '操作错误！'));
        }
    }

    public function del(){
        $this->_del('ResumeWork', 'id');
    }
}
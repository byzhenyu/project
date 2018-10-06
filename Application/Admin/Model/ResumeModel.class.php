<?php
/**
 * 简历模型类
 */
namespace Admin\Model;

use Think\Model;

class ResumeModel extends Model {
    protected $insertFields = array('user_id', 'initials', 'true_name', 'mobile', 'email', 'head_pic', 'sex', 'age', 'job_intension', 'job_area', 'post_nature', 'first_degree', 'second_degree', 'language_ability', 'address', 'introduced', 'introduced_voice', 'career_label', 'hr_id');
    protected $updateFields = array('user_id', 'initials', 'true_name', 'mobile', 'email', 'head_pic', 'sex', 'age', 'job_intension', 'job_area', 'post_nature', 'first_degree', 'second_degree', 'language_ability', 'address', 'introduced', 'introduced_voice', 'career_label', 'hr_id', 'update_time', 'id');
    protected $_validate = array(
        array('true_name', 'require', '真实姓名不能为空！', 1,'regex',3),
        array('true_name', '1,50', '真实姓名控制在50个字以内！',1,'length',3),
        array('email', 'is_email', '请输入正确的邮箱格式！', 1, 'function', 3),
        array('mobile', 'isMobile', '请输入正确的手机号格式！',1,'function',3),
        array('sex', array(0,1,2), '性别值范围不正确！',1,'in',3)
    );

    /**
     * @desc 获取简历基本资料
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getResumeInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    protected function _before_insert(&$data, $option){
        if(!$data['user_id']) $data['user_id'] = UID;
        $data['update_time'] = NOW_TIME;
        $saveData = array();
        if($data['head_pic']) $saveData['head_pic'] = $data['head_pic'];
        if($data['true_name']) $saveData['nickname'] = $data['true_name'];
        if($data['sex']) $saveData['sex'] = $data['sex'];
        $userModel = D('Admin/User');
        $user_where = array('user_id' => $data['user_id']);
        if(count($saveData) > 0){
            $user_res = $userModel->saveUserData($user_where, $saveData);
            if(false === $user_res){
                $this->error = '主表信息修改失败！';
                return false;
            }
        }
        /*$user_info = $userModel->getUserField($user_where, 'user_type');
        $resumeInfo = $this->getResumeInfo($user_where);
        //普通用户简历验证
        if($resumeInfo && !$user_info){
            $this->error = '您已经创建过简历！';
            return false;
        }
        if(!check_is_auth($data['user_id'])){
            $this->error = '请先通过实名认证！';
            return false;
        }*/
        $data['initials'] = rev_pinyin($data['true_name']);
    }

    protected function _before_update(&$data, $option){
        $data['update_time'] = NOW_TIME;
        if(!$data['job_area']) unset($data['job_area']);
        if(!$data['job_intension']) unset($data['job_intension']);
        if(!$data['career_label']) unset($data['career_label']);
        $saveData = array();
        if($data['head_pic']) $saveData['head_pic'] = $data['head_pic'];
        if($data['true_name']) $saveData['nickname'] = $data['true_name'];
        if($data['sex']) $saveData['sex'] = $data['sex'];
        $userModel = D('Admin/User');
        $user_where = array('user_id' => $data['user_id']);
        if(count($saveData) > 0){
            $user_res = $userModel->saveUserData($user_where, $saveData);
            if(false === $user_res){
                $this->error = '主表信息修改失败！';
                return false;
            }
        }
        $data['initials'] = rev_pinyin($data['true_name']);
    }

    /**
     * @desc 简历字段
     * @param $where
     * @param $field
     * @return mixed
     */
    public function getResumeField($where, $field){
        $res = $this->where($where)->getField($field);
        return $res;
    }
}
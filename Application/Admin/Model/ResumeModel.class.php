<?php
/**
 * 简历模型类
 */
namespace Admin\Model;

use Think\Model;

class ResumeModel extends Model {
    protected $insertFields = array('user_id', 'initials', 'true_name', 'mobile', 'email', 'head_pic', 'sex', 'age', 'job_intension', 'job_area', 'post_nature', 'first_degree', 'second_degree', 'language_ability', 'address', 'introduced', 'introduced_voice', 'career_label', 'hr_id', 'industry_id', 'position_id', 'is_audit', 'expect_salary', 'is_incumbency');
    protected $updateFields = array('user_id', 'initials', 'true_name', 'mobile', 'email', 'head_pic', 'sex', 'age', 'job_intension', 'job_area', 'post_nature', 'first_degree', 'second_degree', 'language_ability', 'address', 'introduced', 'introduced_voice', 'career_label', 'hr_id', 'update_time', 'id', 'hide_mobile', 'industry_id', 'position_id', 'expect_salary', 'is_incumbency');
    protected $_validate = array(
        array('true_name', 'require', '真实姓名不能为空！', 1,'regex',3),
        array('true_name', '1,12', '真实姓名控制在12个字以内！',1,'length',3),
        array('email', 'is_email', '请输入正确的邮箱格式！', 2, 'function', 3),
        array('mobile', 'isMobile', '请输入正确的手机号格式！',1,'function',3),
        array('sex', array(0,1,2), '性别值范围不正确！',1,'in',3),
        array('is_marry', array(0,1,2), '婚姻状态值范围不正确！',2,'in',3),
        array('industry_id', 'require', '请选择行业', 1, 'regex', 3),
        array('position_id', 'require', '请选择职业', 1, 'regex', 3),
        array('expect_salary', '0,50', '薪资范围长度不能超过50', 1, 'length', 3)
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
        if(!$data['job_intension']) $data['job_intension'] = D('Admin/Position')->getPositionField(array('id' => $data['position_id']), 'position_name');
        $saveData = array();
        if($data['head_pic']) $saveData['head_pic'] = $data['head_pic'];
        if($data['true_name']) $saveData['nickname'] = $data['true_name'];
        if($data['sex']) $saveData['sex'] = $data['sex'];
        $userModel = D('Admin/User');
        $user_where = array('user_id' => $data['user_id']);
        $user_type = $userModel->getUserField($user_where, 'user_type');
        if(count($saveData) > 0 && 0 == $user_type){
            $user_res = $userModel->saveUserData($user_where, $saveData);
            if(false === $user_res){
                $this->error = '主表信息修改失败！';
                return false;
            }
        }
        $resumeInfo = $this->getResumeInfo(array('mobile' => $data['mobile']));
        if($resumeInfo){
            $this->error = '简历库已有此手机号，请前往小程序认证获得！';
            return false;
        }
        //$data['initials'] = rev_pinyin($data['true_name']);
        $data['initials'] = '';
    }

    protected function _before_update(&$data, $option){
        $data['update_time'] = NOW_TIME;
        if(!$data['job_area']) unset($data['job_area']);
        if(!$data['job_intension']) $data['job_intension'] = D('Admin/Position')->getPositionField(array('id' => $data['position_id']), 'position_name');;
        if(!$data['career_label']) unset($data['career_label']);
        $saveData = array();
        if($data['head_pic']) $saveData['head_pic'] = $data['head_pic'];
        if($data['true_name']) $saveData['nickname'] = $data['true_name'];
        if($data['sex']) $saveData['sex'] = $data['sex'];
        $userModel = D('Admin/User');
        $user_where = array('user_id' => $data['user_id']);
        $user_type = $userModel->getUserField($user_where, 'user_type');
        if(count($saveData) > 0 && $user_type == 0){
            $user_res = $userModel->saveUserData($user_where, $saveData);
            if(false === $user_res){
                $this->error = '主表信息修改失败！';
                return false;
            }
        }
        $resumeInfo = $this->getResumeInfo(array('mobile' => $data['mobile'], 'id' => array('neq', $data['id'])));
        if($resumeInfo){
            $this->error = '简历库已有此手机号！';
            return false;
        }
        //$data['initials'] = rev_pinyin($data['true_name']);
        $data['initials'] = '';
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

    /**
     * @desc 保存简历数据
     * @param $where
     * @param $data
     * @return bool
     */
    public function saveResumeData($where, $data){
        $result = $this->where($where)->save($data);
        return $result;
    }
}
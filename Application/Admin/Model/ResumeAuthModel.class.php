<?php
/**
 * 简历认证表
 */
namespace Admin\Model;

use Think\Model;

class ResumeAuthModel extends Model {
    protected $insertFields = array('user_id', 'hr_id', 'hr_name', 'hr_mobile', 'resume_id', 'add_time');
    protected $updateFields = array('user_id', 'hr_id', 'hr_name', 'hr_mobile', 'resume_id', 'add_time', 'auth_result', 'auth_time');
    protected $_validate = array(
    );

    /**
     * @desc 工作经历新增验证
     * @param $data
     * @return bool|mixed
     */
    public function changeResumeAuth($data){
        $authRes = $this->add($data);
        return $authRes;
    }

    /**
     *
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
     * @desc 保存简历认证信息
     * @param $where
     * @param $data
     * @return bool
     */
    public function saveResumeAuthData($where, $data){
        if(!is_array($where) || !is_array($data)) return false;
        $res = $this->where($where)->save($data);
        return $res;
    }

    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
        if(isMobile($data['mobile'])) return false;
        $hr_info = D('Admin/User')->getUserInfo(array('user_id' => $data['user_id']));
        if($hr_info) $data['hr_id'] = $hr_info['user_id'];
    }
    protected function _before_update(&$data, $option){
    }

}
<?php
/**
 * 用户验证信息模型类
 */
namespace Admin\Model;

use Think\Model;

class UserAuthModel extends Model {
    protected $insertFields = array('true_name', 'cert_type', 'idcard_number', 'idcard_pic', 'hand_pic', 'add_time');
    protected $updateFields = array();
    protected $_validate = array(
        array('true_name', 'require', '真是姓名不能为空！', 1, 'regex', 3),
        array('true_name', 'checkTypeLength', '真是姓名不能超过18字！', 2, 'callback', 3),
        array('cert_type', 'require', '证件类型不能为空！', 1, 'regex', 3),
        array('idcard_number', 'require', '身份证号不能为空！', 1, 'regex', 3),
        array('idcard_number', 'isCard', '不是合法的身份证', 1, 'regex', 3),
        array('idcard_up', 'require', '身份证正面照不能为空！', 1, 'regex', 3),
        array('idcard_down', 'require', '身份证反面照不能为空！', 1, 'regex', 3),
        array('hand_pic', 'require', '手持身份证不能为空!', 1, 'regex', 3)
    );

    /**
     * 验证过滤词长度
     */
    protected function checkTypeLength($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 18) {
            return false;
        }
        return true;
    }

    /**
     * @desc 获取身份验证信息
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getAuthInfo($where, $field = false){
        $info = $this->where($where)->field($field)->find();
        return $info;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $uid = UID;
        $data['add_time'] = NOW_TIME;
        $where = array('user_id' => $uid);
        $res = $this->where($where)->find();
        if($res) $this->where($where)->delete();
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){

    }

}
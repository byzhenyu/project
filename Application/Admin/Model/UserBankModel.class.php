<?php
/**
 * 用户银行卡模型类
 */
namespace Admin\Model;

use Think\Model;

class UserBankModel extends Model {
    protected $insertFields = array('user_id', 'bank_name', 'bank_num', 'bank_branch', 'cardholder', 'open_bank');
    protected $updateFields = array('user_id', 'bank_name', 'bank_num', 'bank_branch', 'cardholder', 'open_bank', 'id');
    protected $_validate = array(
        array('bank_name', 'require', '银行名称不能为空！', 1, 'regex', 3),
        array('bank_name', '1,50', '银行名称控制在50个字之内！', 1, 'length', 3),
        array('bank_num', 'require', '银行卡号不能为空！', 1,'regex', 3),
        array('bank_num', '16,22','银行卡号长度16-22位', 1, 'length', 3),
        array('cardholder', 'require', '持卡人信息不能为空！', 1, 'regex', 3),
        array('cardholder', '1,18','持卡人信息长度不能超过18位！', 1, 'length', 3)
    );

    /**
     * @desc 用户银行卡号列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getUserBankList($where, $field = false, $order = 'id desc'){
        if(!$field) $field = 'id,bank_name,cardholder,bank_num';
        $number = $this->where($where)->count();
        $page = get_web_page($number);
        $list = $this->where($where)->field($field)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * @desc 获取银行卡号详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getUserBankInfo($where, $field = false){
        if(!$field) $field = 'id,bank_num,bank_name,cardholder';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    public function _before_delete(&$data, $option){}

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['user_id'] = UID;
        $bank_num = $data['bank_num'];
        $res = $this->where(array('bank_num' => $bank_num, 'user_id' => UID))->find();
        if($res){
            $this->error = '银行卡号已经添加过！';
            return false;
        }
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
        $bank_num = $data['bank_num'];
        $res = $this->where(array('bank_num' => $bank_num, 'user_id' => UID))->find();
        if($res){
            $this->error = '银行卡号已经添加过！';
            return false;
        }
    }

}
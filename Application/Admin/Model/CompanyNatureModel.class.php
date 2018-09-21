<?php
/**
 * 公司性质模型
 */
namespace Admin\Model;

use Think\Model;

class CompanyNatureModel extends Model {
    protected $insertFields = array('nature_name');
    protected $updateFields = array('nature_name', 'id');
    protected $_validate = array(
        array('nature_name', 'require', '公司性质名称不能为空！', 1, 'regex', 3),
        array('nature_name', '1,50', '公司性质名称保持在1-100字！', 1, 'length', 3)
    );

    /**
     * @desc 获取公司性质列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return mixed
     */
    public function getCompanyNatureList($where, $field = false, $order = ''){
        if(!$field) $field = '*';
        $list = $this->where($where)->field($field)->order($order)->select();
        return $list;
    }

    /**
     * @desc 获取公司性质详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getCompanyNatureInfo($where, $field = false){
        if(!$field) $field = '';
        $info = $this->where($where)->field($field)->find();
        return $info;
    }

    protected function _before_insert(&$data, $option){
        $education_name = $data['nature_name'];
        $res = $this->getCompanyNatureInfo(array('nature_name' => $education_name));
        if($res) {
            $this->error = '性质名称已经存在！';
            return false;
        }
    }
    protected function _before_update(&$data, $option){
        $education_name = $data['nature_name'];
        $res = $this->getCompanyNatureInfo(array('nature_name' => $education_name, 'id' => array('neq', $data['id'])));
        if($res){
            $this->error = '性质名称已经存在！';
            return false;
        }
    }

}
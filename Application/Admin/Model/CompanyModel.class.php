<?php
/**
 * 公司模型
 */
namespace Admin\Model;

use Think\Model;

class CompanyModel extends Model {
    protected $insertFields = array('company_name');
    protected $updateFields = array('company_name', 'id');
    protected $_validate = array(
    );

    /**
     * @desc 获取公司列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getCompanyList($where, $field = false, $order = 'id desc'){
        if(!$field) $field = '*';
        $number = $this->where($where)->count();
        $page = get_web_page($number, 20);
        $list = $this->where($where)->field($field)->order($order)->limit($page['limit'])->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $company_name = $data['company_name'];
        $res = $this->where(array('company_name' => $company_name))->find();
        if($res) return false;
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}
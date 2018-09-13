<?php
/**
 * 公司资料模型
 */
namespace Admin\Model;

use Think\Model;

class CompanyInfoModel extends Model {
    protected $insertFields = array('id','user_id','company_name','company_size','company_nature','company_mobile','company_email','company_industry','company_address','company_pic');
    protected $updateFields = array('id','user_id','company_name','company_size','company_nature','company_mobile','company_email','company_industry','company_address','company_pic');
    protected $_validate = array(
        array('company_name', 'require', '公司名称不能为空', 1, 'regex', 3),
        array('company_name', '1,100', '公司名称保持在1-100字！', 1, 'length', 3),
        array('company_size', 'require', '请填写公司规模！', 1, 'regex', 3),
        array('company_nature', 'require', '请选择公司性质！', 1, 'regex', 3),
        array('company_mobile', 'require', '请填写公司联系电话！', 1, 'regex', 3),
        array('company_email', 'require', '请填写公司联系邮箱！', 1, 'regex', 3),
        array('company_industry', 'require', '公司所属行业不能为空！', 1, 'regex', 3),
        array('company_address', 'require', '请填写公司地址！', 1, 'regex', 3),
        array('company_address', '1,150', '公司名称保持在1-150字！', 1, 'length', 3),
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

    /**
     * @desc 公司信息详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getCompanyInfoInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $companyModel = M('Company');
        $count = $companyModel->where(array('company_name'=>$data['company_name']))->count();
        if (!$count) {
            $companyData['company_name'] = $data['company_name'];
            $companyModel->data($companyData)->add();
        }
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
        $companyModel = M('Company');
        $count = $companyModel->where(array('company_name'=>$data['company_name']))->count();
        if (!$count) {
            $companyData['company_name'] = $data['company_name'];
            $companyModel->data($companyData)->add();
        }
    }

}
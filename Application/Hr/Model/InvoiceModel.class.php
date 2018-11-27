<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/24 0024 10:44
 * @CreateBy       PhpStorm
 */

namespace Hr\Model;
use Think\Model;
class InvoiceModel extends Model {
    protected $updateFields = array('*');
    protected $findFields = array('*');
    protected $selectFields = array('*');
    protected $_validate = array(
        array('invoice_amount', 'number', '请填写发票金额', 1, 'regex', 3),
        array('company_name', 'require', '公司名称不能为空', 1, 'regex', 3),
        array('business_license', 'require', '公司营业执照不能为空!', 1, 'regex', 3),
        array('bank_name', 'require', '银行名称不能为空', 1, 'regex', 3),
        array('bank_name', 'require', '银行名称不能为空', 1, 'regex', 3),
        array('bank_no', 'number', '银行账号字段有错误', 1, 'regex', 3),
        array('business_address', 'require', '营业执照所在地不能为空', 1, 'regex', 3),
        array('contacts_name', 'require', '联系人名称不能为空', 1, 'regex', 3),
        array('contacts_mobile', 'require', '联系人手机不能为空', 1, 'regex', 3),
        array('contacts_mobile','/^1[3|4|5|7|8|9][0-9]\d{8}$/','不是有效的手机号码',1,'regex', 3),
        array('invoice_type', array(0,1), '发票类型错误', 1, 'in', 2),
        array('business_address', 'require', '营业执照所在地不能为空', 1, 'regex', 3)
    );
    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
    }
    /**
    * @desc  发票管理
    * @param   HR_ID
    * @return mixed
    */
    public function invoiceList($where = [], $field = null, $sort = 'i.add_time DESC'){
        $count  = $this->alias('i')
                 ->join('__USER__ as u on u.user_id = i.hr_user_id', 'LEFT')
                 ->where($where)
                 ->count();
        $page = get_page($count);
        $list = $this->alias('i')
                ->join('__USER__ as u on u.user_id = i.hr_user_id', 'LEFT')
                ->field('i.* , u.nickname, u.invoice_amount as user_invoice_amount')
                ->where($where)
                ->order($sort)
                ->limit($page['limit'])
                ->select();
        return array(
             'list' => $list,
             'page' => $page['page']
        );
    }
    /**
    * @desc 获取发票信息
    * @param  id
    * @return mixed
    */
    public function getInvoiceInfo($where = [], $filed = null){
        $info  = $this->alias('i')
            ->join('__USER__ as u on u.user_id = i.hr_user_id', 'LEFT')
            ->field('i.* , u.nickname, u.invoice_amount as user_invoice_amount')
            ->where($where)
            ->find();
        return $info;
    }
}
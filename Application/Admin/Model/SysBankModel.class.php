<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/27 0027 9:49
 * @CreateBy       PhpStorm
 */

namespace Admin\Model;
use Think\Model;
class SysBankModel extends Model{
    protected $insertFields = array('bank_name', 'bank_no', 'bank_holder','bank_opening');
    protected $updateFields = array('bank_name', 'bank_no', 'bank_holder', 'bank_opening', 'id');
    protected $findFields = array('bank_name', 'bank_name', 'bank_name', 'bank_holder', 'id');
    protected $_validate = array(
        array('bank_name', 'require', '银行名称不能为空！', 1, 'regex', 3),
        array('bank_no', 'require', '银行卡号不能为空！', 1, 'regex', 3),
        array('bank_holder', 'require', '持卡人姓名不能为空！', 1, 'regex', 3),
        array('bank_opening', 'require', '开户行不能为空！', 1, 'regex', 3)
    );
    /**
    * @desc  银行卡号列表
    * @param
    * @return mixed
    */
    public function getBankList($where = [], $field = null ,$sort = 'add_time DESC'){
        if (is_null($field)) {
            $field = $this->selectFields;
        }
        $count = $this->where($where)->count();
        $page = get_page($count);
        $list = $this
            ->field($field)
            ->where($where)
            ->limit($page['limit'])
            ->order($sort)
            ->select();
        return array(
            'info'=>$list,
            'page'=>$page['page']
        );
    }
    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
    }
}
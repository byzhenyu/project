<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description    转账 Model
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/27 0027 11:28
 * @CreateBy       PhpStorm
 */

namespace Hr\Model;
use Think\Model;
class TransferAccountModel extends Model{
    protected $updateFields =  array('bank_id','transfer_amount','transfer_img','audit_desc','transfer_remark','user_id');
    protected $findFields =  array('id','bank_id','transfer_amount','transfer_img','audit_desc','transfer_remark','user_id');
    protected $insertFields = array('bank_id','transfer_amount','transfer_img','audit_desc','transfer_remark','user_id');
    protected $selectFields = array('*');
    protected $_validate = array(
        array('bank_id', 'require', '转账银行ID不能为空', 1, 'regex', 3),
        array('transfer_amount', 'number', '转账金额有误', 1, 'regex', 3),
        array('transfer_img', 'require', '转账截图不能为空!', 1, 'regex', 3),
        array('user_id', 'require', '用户ID不能为空!', 1, 'regex', 3),
    );
    protected function _before_insert(&$data, $option){
        $data['transfer_time'] = NOW_TIME;
    }
    /**
    * @desc  获取转账信息
    * @param  $where
    * @return mixed
    */
    public function getAccounts($where = [], $field = null, $sort = 'transfer_time DESC'){
          $count = $this->alias('t')
                   ->join('__USER__ as u on u.user_id = t.user_id', 'LEFT')
                   ->join('__SYS_BANK__ as s on s.id = t.bank_id', 'LEFT')
                   ->where($where)
                   ->count();
          $page = get_page($count);
          $list = $this->alias('t')
                  ->join('__USER__ as u on u.user_id = t.user_id', 'LEFT')
                  ->join('__SYS_BANK__ as s on s.id = t.bank_id', 'LEFT')
                  ->field($field)
                  ->limit($page['limit'])
                  ->order($sort)
                  ->where($where)
                  ->select();
          return array(
               'list' => $list,
               'page' => $page['page']
          );
    }
}
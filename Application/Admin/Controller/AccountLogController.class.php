<?php
/**
 * 账单控制器
 */
namespace Admin\Controller;
use Think\Controller;
class AccountLogController extends CommonController {

    //账单列表
    public function listAccountLog(){
        $type = I('type', -1, 'intval');
        $model = D('Admin/AccountLog');
        $where = array();
        if($type >= 0) $where['change_type'] = $type;
        $list = $model->getAccountLogByPage($where);
        $this->info = $list['info'];
        $this->page = $list['page'];
        p($list);
    }

    // 放入回收站
    public function del(){
        $this->_del('AccountLog', 'log_id');  //调用父类的方法
    }
}
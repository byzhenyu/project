<?php
/**
 * 账单控制器
 */
namespace Admin\Controller;
use Think\Controller;
class AccountLogController extends CommonController {

    // 放入回收站
    public function del(){
        $this->_del('AccountLog', 'log_id');  //调用父类的方法
    }
}
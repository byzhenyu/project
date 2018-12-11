<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/27 0027 18:47
 * @CreateBy       PhpStorm
 */

namespace Hr\Controller;
use Common\Controller\HrCommonController;
class UserAccountController extends HrCommonController{
    protected function _initialize()
    {
        $this->TransferAccount = D("Admin/UserAccount");
    }
    /**
    * @desc  获取充值信息
    * @param  HR_ID
    * @return mixed
    */
    public function getAccount(){
        $where['u.user_id'] = HR_ID;
        $list = $this->TransferAccount->getUserAccountList($where);
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }
    //账单列表
    public function listUserAccount(){

    }
    // 放入回收站
    public function del(){
        $this->_del('UserAccount', 'id');
    }
}
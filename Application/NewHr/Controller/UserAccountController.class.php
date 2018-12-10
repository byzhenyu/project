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

namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class UserAccountController extends HrCommonController{
    protected function _initialize()
    {
        $this->TransferAccount = D("Admin/UserAccount");
        $this->User = D("Hr/User");
    }
    /**
    * @desc  获取充值信息
    * @param  HR_ID
    * @return mixed
    */
    public function getAccount(){
        $where['u.user_id'] = HR_ID;
        $list = $this->TransferAccount->getUserAccountList($where);
        $SysBankModel = D("Admin/SysBank");
        $SysBankList = $SysBankModel->getBankList();
        $userMoney =  $this->User->where(array( 'user_id' => HR_ID))->getField('user_money');
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->SysBankList = $SysBankList['info'];
        $this->userMoney = $userMoney;
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
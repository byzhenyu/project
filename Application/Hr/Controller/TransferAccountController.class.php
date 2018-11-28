<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description     转账管理 控制器
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/27 0027 13:19
 * @CreateBy       PhpStorm
 */
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class TransferAccountController extends HrCommonController{
    protected function _initialize()
    {
        $this->TransferAccount = D("Hr/TransferAccount");
    }
    /**
     * @desc 转账
     * @param
     * @return mixed
     */
    public function accounts(){
        if (IS_POST) {
            $data = I('post.');
            $data['user_id'] = 5;
            if ($this->TransferAccount->create($data) === false) {
                $this->ajaxReturn(V(0, $this->TransferAccount->getError()));
            }
            if ( $this->TransferAccount->add() !== false) {
                $this->ajaxReturn(V(1, '提交成功'));
            }
            $this->ajaxReturn(V(0,  $this->TransferAccount->getDbError()));
        }
    }
    /**
    * @desc  转账查看
    * @param  HR_ID
    * @return mixed
    */
    public function getMyAccounts(){
        $keyword = I('keyword', '');
        if ($keyword) {
            $where['t.transfer_amount'] = array('like','%'.$keyword.'%');
        }
        $where['t.user_id'] = HR_ID;
        $field = 't.*, s.bank_name, s.bank_no, s.bank_holder, s.bank_opening';
        $AccountsInfo = $this->TransferAccount->getAccounts($where, $field);
        $this->list = $AccountsInfo['list'];
        $this->page = $AccountsInfo['page'];
        $this->display();
    }
    /**
    * @desc  转账详情
    * @param  id
    * @return mixed
    */
    public function accountDetail(){
        $where['t.id'] = I('id', 0, 'intval');
        $field = 't.*, s.bank_name, s.bank_no, s.bank_holder, s.bank_opening';
        $AccountsInfo =  $this->TransferAccount->getAccounts($where, $field);
        $this->info = $AccountsInfo['list'][0];
        $this->display();
    }
    /**
    * @desc  删除信息
    * @param  id
    * @return mixed
    */
    /*删除*/
    public function recycle() {
        $this->_recycle('TransferAccount','id');
    }
    public function del(){
        $this->_del('TransferAccount', 'id');
    }
    public function pdf(){
        $where['t.id'] = I('id', 0, 'intval');
        $field = 'u.nickname, u.mobile,u.head_pic, u.user_money, c.company_name, c.company_mobile, c.company_email,c.company_address, t.*, s.bank_name, s.bank_no, s.bank_holder, s.bank_opening';
        $AccountsInfo =  $this->TransferAccount->getAccounts($where, $field);
        $this->TransferAccount->pdf('这是一个PDF','哈哈哈哈哈哈',$AccountsInfo['list'][0]);
    }
}
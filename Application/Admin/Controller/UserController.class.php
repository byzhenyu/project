<?php
/**
 * 用户管理类 
 */
namespace Admin\Controller;
use Think\Controller;
class UserController extends CommonController {
    //会员HR列表
    public function listUser(){
        $keyword = I('keyword', '');
        $type = I('type', 0, 'intval');
        $userModel = D('Admin/User');
        $where = array('user_type' => $type, 'rank_id' => 1);
        if ($keyword) {
            $where['mobile|user_name'] = array('like','%'.$keyword.'%');
        }

        // 查询所有的用户
        $data = $userModel->getUsersList($where);
        $this->userslist = $data['userslist'];
        $this->page = $data['page'];
        $this->keyword = $keyword;
        $this->user_type = $type;
        $this->display('listUsers');
    }

    /**
     * 用户详情
     */
    public function userDetail(){
        $user_id = I('user_id', 0, 'intval');
        $where['user_id'] = $user_id;
        $userModel = D('Admin/User');
        $authModel = D('Admin/UserAuth');
        $userInfo = $userModel->getUserInfo($where);
        $auth = $authModel->getAuthInfo($where);
        $this->userInfo = $userInfo;
        $this->info = $auth;

        $this->display();
    }

    /**
     *用户管理启用，禁用方法
     */
    public function changeDisabled(){
        $user_id = I('user_id', 0, 'intval');
        $updateInfo = D('Admin/User')->changeDisabled($user_id);
        $this->ajaxReturn($updateInfo);
    }


    //充值列表
    public function rechargeList(){
        $keyword  = I('mobile', '', 'trim');
    	$where['ua.type'] = 0;
        $where['ua.status'] = 1;
        if ($keyword) {
            $where['u.mobile'] = array('like','%'.$keyword.'%');
        }
        $field = 'ua.id,ua.money,ua.add_time,ua.user_note,ua.payment,u.mobile';
        $list = D('Admin/UserAccount')->getUserAccountList($where, $field);
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    //会员提现列表
    public function withdrawList(){
        $keyword  = I('mobile', '', 'trim');
        $accountType = I('type', 0, 'intval');
        $where['ua.type'] = 1;
        $where['ua.status'] = 1;
        if ($keyword) {
            $where['u.mobile'] = array('like','%'.$keyword.'%');
        }
        if ($accountType == 0) {
            $field = 'ua.id, ua.money, ua.add_time, ua.state, u.user_name, u.user_money';
            $list = D('Admin/UserAccount')->getUserAccountList($where, $field);
        }
        
        $this->list = $list['info'];
        $this->page = $list['page'];
        if ($accountType == 1 || $accountType == 3) {
            $tpl = 'withdrawList';
        } 
        $this->account_type = $accountType;
        $this->display($tpl);
    }

    //会员提现列表
    public function withdrawAgentList(){
        $keyword  = I('keyword', '', 'trim');
        $accountType = I('account_type', 0, 'intval');
        $where['ua.account_type'] = 2;
        $where['ua.type'] = 1;
        $where['ua.status'] = 1;
        if ($keyword) {
            $where['g.agent_name'] = array('like','%'.$keyword.'%');
        }
        $field = 'ua.id, ua.money, ua.add_time, ua.state, g.agent_name, g.account_amout, g.province, g.city, g.district';
        $list = D('Admin/UserAccount')->getAgentAccountList($where, $field);
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    /***
     * @desc 用户身份验证
     */
    public function editUsersAuth() {
        $id = I('user_id', 0, 'intval');
        $where['user_id'] = $id;
        if (IS_POST) {
            if ($id > 0) {
                $data['is_auth'] = I('state', 0, 'intval');
                $result = D('Admin/User')->where($where)->save($data);
                if(false !== $result) $this->ajaxReturn(V(1, '操作成功'));
                $this->ajaxReturn(V(0, '修改失败请稍后重试！'));
            }
        } else {
            $result = D('Admin/UserAuth')->getAuthInfo($where);
            $this->assign('info', $result);
            $this->display();
        }
        
    }

    //审核提现
    public function editWithdraw(){
        $id = I('id', 0, 'intval');
        $UserAccountModel = D('Admin/UserAccount');
        if (IS_POST) {
            $state = I('state', 0);
            if ($id > 0) {
                if($UserAccountModel->create()){
                    M()->startTrans();// 开启事务
                    $result = $UserAccountModel->save();
                    if ($result === false) {
                        M()->rollback(); // 事务回滚
                        $this->ajaxReturn(V(0, '操作失败'));
                    }
                    if ($state == 1) { //完成审核
                        $where['id'] = $id;
                        $accountInfo = D('Admin/UserAccount')->getUserAccountDetail($where, 'user_id, money');

                        //减少会员冻结余额
                        $setUserMoney = D('Admin/User')->where('user_id='.$accountInfo['user_id'])->setDec('frozen_money', $accountInfo['money']);
                        if ($setUserMoney === false) {
                            M()->rollback(); // 事务回滚
                            $this->ajaxReturn(V(0, '操作失败'));
                        }
                        M('AccountLog')->where(array('order_sn'=>"$id"))->setField('change_desc', '提现-已完成');

                    }
                    else if ($state == 2) { //驳回
                        $where['id'] = $id;
                        $accountInfo = D('Admin/UserAccount')->getUserAccountDetail($where, 'user_id, money');

                        //减少会员冻结余额
                        $setUserForzenMoney = D('Admin/User')->where('user_id='.$accountInfo['user_id'])->setDec('frozen_money', $accountInfo['money']);
                        if ($setUserForzenMoney === false) {
                            M()->rollback(); // 事务回滚
                            $this->ajaxReturn(V(0, '操作失败'));
                        }
                        //增加会员余额
                        $setUserMoney = D('Admin/User')->where('user_id='.$accountInfo['user_id'])->setInc('user_money', $accountInfo['money']);
                        if ($setUserMoney === false) {
                            M()->rollback(); // 事务回滚
                            $this->ajaxReturn(V(0, '操作失败'));
                        }
                        //更新资金记录
                        M('AccountLog')->where(array('order_sn'=>"$id"))->setField('change_desc', '提现-被拒绝');
                    }
                    M()->commit(); // 事务提交
                    $this->ajaxReturn(V(1, '操作成功', $id));
                } else {
                    $this->ajaxReturn(V(0, $UserAccountModel->getError()));
                }

            }
        } else {
            $where['id'] = $id;
            $accountInfo = $UserAccountModel->getUserAccountInfo($where);
            $this->assign('info', $accountInfo);
            $this->display();
        }

    }

    public function del() {
        $id = I('id', 0);
        $result = V(0, '删除失败, 未知错误');
        if($id != 0){
            $where['user_id'] = array('in', $id);
            $data['status'] = 0;
            if( M('User')->data($data)->where($where)->save() !== false){
                $result = V(1, '删除成功');
            }
        }
        $this->ajaxReturn($result);
    }
}
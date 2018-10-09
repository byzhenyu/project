<?php
namespace Core\Controller;
use Common\Controller\CommonController;
/**
 * 悬赏佣金结算功能
 */
class TokenLogController extends CommonController {

    public function payOffTokenLog(){
        $time_limit = NOW_TIME - 7*86400;
        $where = array('l.is_pay' => 0, 'l.add_time' => array('lt', $time_limit));
        $field = 'l.user_id,l.token_num,l.type,l.recruit_id,l.id';
        $tokenLogModel = D('Admin/TokenLog');
        $tokenLogList = $tokenLogModel->getTokenLogList($where, $field);
        $tokenLogList = $tokenLogList['info'];
        set_time_limit(0);
        $accountLogModel = D('Admin/AccountLog');
        $recruitModel = D('Admin/Recruit');
        $userModel = D('Admin/User');
        $type_arr = array(1 => 2, 2 => 3);
        $type_string = array(1 => '获取简历令牌', 2 => '入职简历令牌');
        foreach($tokenLogList as &$val){
            M()->startTrans();
            //减少悬赏发布人冻结资金
            $recruit_where = array(array('id' => $val['recruit_id']));
            $recruit_info = $recruitModel->getRecruitInfo($recruit_where, 'hr_user_id');
            $release_res = $userModel->decreaseUserFieldNum($recruit_info['hr_user_id'], 'frozen_money', $val['token_num']);
            //增加用户资金/可提现资金
            $token_log_res = $userModel->increaseUserFieldNum($val['user_id'], 'user_money', $val['token_num']);
            $token_log_res2 = $userModel->increaseUserFieldNum($val['user_id'], 'withdrawable_amount', $val['token_num']);
            $token_account_data = array(
                'user_id' => $val['user_id'],
                'user_money' => $val['token_num'],
                'change_desc' => $type_string[$val['type']],
                'change_type' => $type_arr[$val['type']],
                'order_sn' => $val['id'].','.$val['recruit_id']
            );
            //增加资金记录
            $token_account_res = $accountLogModel->add($token_account_data);
            if(false !== $release_res && false !== $token_log_res && false !== $token_log_res2 && false !== $token_account_res){
                //修改佣金结算状态
                $tokenLogModel->where(array('id' => $val['id']))->save(array('is_pay' => 1));
                M()->commit();
            }
            else{
                M()->rollback();
            }
        }
    }
}
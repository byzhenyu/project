<?php
namespace Core\Controller;
use Common\Controller\CommonController;
/**
 * 结算
 */
class SettleController extends CommonController {
    public function __construct(){
        parent::__construct();
        set_time_limit(0);
        ini_set('memory_limit','3072M');
    }


    /**
     * @desc 用户冻结金额/可提现金额变动
     */
    public function userFrozenMoneyRelieve(){
        $account_model = D('Admin/AccountLog');
        $user_model = D('Admin/User');
        $account_time_limit = NOW_TIME - ( 5 * 86400);
        $account_where = array('diss' => 0, 'change_type' => array('in', array(2, 3, 6)), 'change_time' => array('lt', $account_time_limit));
        $now_time = NOW_TIME;
        $list = $account_model->getAccountLogFrozenList($account_where);
        foreach($list as &$val){
            $t_time_days = get_days($val['change_time'], $now_time);
            if($t_time_days >= 7){
                M()->startTrans();
                //释放用户冻结金额
                $decrease_res = $user_model->decreaseUserFieldNum($val['user_id'], 'frozen_money', $val['user_money']);
                //增加用户可提现金额
                $increase_res = $user_model->increaseUserFieldNum($val['user_id'], 'withdrawable_amount', $val['user_money']);
                //修改用户资金记录状态
                $account_res = $account_model->where(array('id' => $val['id']))->save(array('diss' => 1));
                if(false !== $decrease_res && false !== $increase_res && false !== $account_res){
                    M()->commit();
                }
                else{
                    M()->rollback();
                }
            }
            else{
                continue;
            }
        }
        unset($val);
    }

    /**
     * @desc 悬赏30日自动入职
     */
    public function recruitInterview(){
    }

    /**
     * @desc 未认证简历短信发送提醒
     */
    public function resumeAuth(){
        $model = D('Admin/ResumeAuth');
        $limit_time = NOW_TIME - 30 * 86400;
        $where = array('auth_result' => 0, 'add_time' => array('lt', $limit_time));
    }
}
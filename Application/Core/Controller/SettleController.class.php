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
        ini_set('memory_limit','4096M');
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
        $interviewModel = D('Admin/Interview');
        $recruitResumeModel = D('Admin/RecruitResume');
        $recruitModel = D('Admin/Recruit');
        $limit_time = NOW_TIME - 30 * 86400;
        $interview_where = array('state' => 0, 'update_time' => array('lt', $limit_time));
        $list = $interviewModel->interviewList($interview_where);
        foreach($list as &$val){
            $recruitResumeInfo = $recruitResumeModel->getRecruitResumeInfo(array('id' => $val['recruit_resume_id']));
            $recruitInfo = $recruitModel->getRecruitInfo(array('id' => $recruitResumeInfo['recruit_id']));
        }
        unset($val);
    }

    /**
     * @desc 未认证简历短信发送提醒
     */
    public function resumeAuth(){
        $model = D('Admin/ResumeAuth');
        $messageModel = D('Admin/SmsMessage');
        $limit_time = NOW_TIME - 30 * 86400;
        $where = array('auth_result' => 0, 'add_time' => array('lt', $limit_time));
        $list = $model->resumeAuthList($where);
        $start = mktime(0,0,0,date('m'),1,date('Y'));
        $end = mktime(23,59,59,date('m'),date('t'),date('Y'));
        foreach($list as &$val){
            $message_limit = $messageModel->where(array('mobile' => $val['hr_mobile'], 'add_time' => array('between', array($start, $end)), 'type' => 99))->find();
            if($message_limit) continue;
            $message_content = '《闪荐科技》简历认证邀请您进行认证！';
            $send_res = sendMessageRequest($val['hr_mobile'], $message_content);
            if($send_res['status']){
                $data = array(
                    'mobile' => $val['hr_mobile'],
                    'sms_content' => $message_content,
                    'sms_code' => '0000',
                    'add_time' => NOW_TIME,
                    'send_status' => $send_res['status'],
                    'user_type' => 1,
                    'type' => 99,
                    'send_response_msg' => $send_res['info']
                );
                $messageModel->add($data);
            }
        }
        unset($val);
    }
}
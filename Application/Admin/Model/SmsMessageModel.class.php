<?php
/**
 * Created by PhpStorm.
 */
namespace Admin\Model;
use Think\Model;
class SmsMessageModel extends Model
{
    // 短信记录添加
    public function addSmsMessage($data){
        $data['add_time'] = NOW_TIME;
        $this->data($data)->add();
    }

    /**
     * 验证短信验证是否正确
     * @param $code
     * @param $mobile
     * @param $user_type
     * @param $type
     * @return mixed
     */
    public function checkSmsMessage($code, $mobile, $user_type = 0, $type = 1) {
        if (strlen($code) != C('SMS_CODE_LEN')){
            return V(0, '短信验证码长度有误');
        }
        if (!isMobile($mobile)){
            return V(0, '手机号码长度有误');
        }
        $where['sms_code'] = $code;
        $where['mobile'] = $mobile;
        $where['add_time'] = array('EGT', NOW_TIME - C('SMS_EXPIRE_TIME') * 60);
        $where['is_used'] = 0;
        $where['user_type'] = $user_type;
        $smsInfo = $this->where($where)->find();

        if (count($smsInfo) > 0) {
            $this->where($where)->setField('is_used', 1);
            return V(1, '短信验证码正确', $mobile);
        } else {
            return V(0, '短信验证码不正确');
        }
    }
}
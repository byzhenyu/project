<?php
/**
 * Created by liuniukeji.com
 * 公用接口
 * @author songgy <1661745274@qq.com>
*/
namespace Api\Controller;
use Common\Controller\ApiCommonController;
use Common\Tools\RongCloud;
use Think\Verify;

class PublicApiController extends ApiCommonController {

    /**
     * 登录接口
     */
    public function login() {
        $mobile = I('post.username', '');
        $password = I('post.password', '');
        $userType = I('post.user_type', 0);//用户类型
        $loginInfo = D('Home/User')->dologin($mobile, $password, '', $userType);

        if( $loginInfo['status'] == 1 ){ //登录成功
            $loginInfo['data']['shop_id'] = (int)$loginInfo['data']['shop_id'];
            $loginInfo['data']['register_time'] = time_format($loginInfo['register_time'], 'Y-m-d');
            $loginInfo['data']['user_level'] = 'VIP1';
            $loginInfo['data']['pay_password'] = $loginInfo['data']['pay_password'] != '' ? 1 : 0;
            unset($loginInfo['password']);
            $this->apiReturn($loginInfo);
        } else {
            $this->apiReturn(V(0, $loginInfo['info']));
        }
    }

    /**
     * 注册接口
     */
    public function register() {
        $mobile = I('mobile', '');
        $sms_code = I('sms_code', '');
        $invitation_code = I('invitation_code', '');
        $userModel = D('Home/User');
        if (isMobile($mobile)  != true) {
            $this->apiReturn(V(0, '请填写正确的手机格式'));
        }
        if (!$invitation_code) {
            $this->apiReturn(V(0,'请填写邀请码'));
        }
        if ($invitation_code != '') {
            $invitation_uid = $userModel->getRecommendUidBaseCode($invitation_code);
            if (!$invitation_uid)
                $this->apiReturn(V(0, '邀请码不存在'));
        }
        //$check_sms = D('Home/SmsMessage')->checkSmsMessage($sms_code, $mobile);

        //if ($check_sms['status'] == 0) {
            //$this->apiReturn($check_sms);
        //}
   
        $data = I('post.');
        $data['user_type'] = 0;
        if ($userModel->create($data, 1) !== false) {
            $user_id = $userModel->add();
            if ($user_id > 0) {
                //注册成功、推荐双方各得5元优惠券
                if($invitation_code) $userModel->allInviteCoupon($invitation_code, $user_id);

                $loginInfo = D('Home/User')->dologin($data['mobile'], $data['password'], '', 0);

                if( $loginInfo['status'] == 1 ){ //登录成功

                    $loginInfo['data']['shop_id'] = (int)$loginInfo['data']['shop_id'];
                    $loginInfo['data']['register_time'] = time_format($loginInfo['register_time'], 'Y-m-d');
                    $loginInfo['data']['user_level'] = 'VIP1';
                    $loginInfo['data']['pay_password'] = $loginInfo['data']['pay_password'] != '' ? 1 : 0;
                    unset($loginInfo['password']);
                    $this->apiReturn($loginInfo);
                } else {
                    $this->apiReturn(V(0, $loginInfo['info']));
                }
                exit;
            }
            else{
                $this->apiReturn(V(0, $userModel->getError()));
            }
        } else {
            $this->apiReturn(V(0, $userModel->getError()));
        }
    }

    /**
     * 获取短信接口
     * user_type 0普通会员1、骑手 2、商家端
     * type 1注册短信，2找回密码 3修改密码 4绑定手机 6设置支付密码
     */
    public function smsCode() {
        $mobile = I('mobile', '');
        $user_type = I('user_type', 0, 'intval');
        $type = I('type', 0, 'intval');
        //1注册短信，2找回密码 3修改密码 4绑定手机 6设置支付密码
        $type_array = array(1, 2 , 3, 4, 5, 6); 
        if (!in_array($type, $type_array)) {
            $this->apiReturn(V(0, '参数错误'));
        }
        $user_type_array = array(0, 1,2);
        if (!in_array($user_type, $user_type_array)) {
            $this->apiReturn(V(0, '用户类型参数错误'));
        }
        if (empty($mobile) || !isMobile($mobile)) {
            $this->apiReturn(V(0, '请输入有效的手机号码'));
            exit;
        }
        //验证手机号码是否已经验证
        $info['mobile'] = $mobile;
        $info['user_type'] = $user_type;
        $result = D('Home/User')->checkUserExist($info);

        if ($result == false && $type == 1) {
            $this->apiReturn(V(0, '手机号码已存在'));
        } elseif ($result == true && in_array($type, array(2,3,6))) {
            $this->apiReturn(V(0, '手机号码不存在'));
        } elseif ($result == false && $type == 4) {
            $this->apiReturn(V(0, '手机号码已存在'));
        }
        // 短信内容
        $sms_code = randCode(C('SMS_CODE_LEN'), 1);

        switch ($type) {
            case 1: //注册短信
                $msg = '注册验证码';
                $sms_content = C('SMS_REGISTER_MSG') . $sms_code;
                break;
            case 2: //找回密码
                $msg = '找回密码验证码';
                $sms_content = C('SMS_FINDPWD_MSG') . $sms_code;
                break;
            case 3: //修改密码
                $msg = '修改密码验证码';
                $sms_content = C('SMS_MODPWD_MSG') . $sms_code;
                break;
            case 4: //绑定手机号码
                $msg = '绑定手机号验证码';
                $sms_content = C('SMS_MODMOBILE_MSG') . $sms_code;
                break;
            case 5: //修改提现密码
                $msg = '修改提现密码验证码';
                $sms_content = C('SMS_WITHDRAW_MSG') . $sms_code;
                break;
            case 6: //设置支付密码
                $msg = '设置支付密码验证码';
                $sms_content = C('SMS_PAY_MSG') . $sms_code;
                break;
            default:
                break;
        }        

        $send_result = sendMessageRequest($mobile, $sms_content);

        // 保存短信信息
        $data['sms_content'] = $sms_content;
        $data['sms_code'] = $sms_code;
        $data['mobile'] = $mobile;
        $data['type'] = $msg;
        $data['send_status'] = $send_result['status'];
        $data['send_response_msg'] = $send_result['info'];
        $data['user_type'] = $user_type;
        D('Home/SmsMessage')->addSmsMessage($data);

        if ($send_result['status'] == 1) {
            $this->apiReturn(V(1, '发送成功'));
        } else {
            $this->apiReturn(V(0, '发送失败:'. $send_result['info']));
        }
    }
    /**
     * 验证短信码是否正确
     */
    public function checkSmsCode() {
        $mobile = I('mobile', '');
        $sms_code = I('sms_code', '');
        $user_type = I('user_type', 0, 'intval');
        $result = D('Home/SmsMessage')->checkSmsMessage($sms_code, $mobile,$user_type);
        $this->apiReturn($result);
    }

    /**
     * 找回密码 --- 验证手机号码
     */
    public function checkFindpwdMobile() {
        $mobile = I('mobile', '');
        $result = D('Home/User')->checkUserExist($mobile);
        if ($result == false) { // 不存在
            $this->apiReturn(V(0, '手机号码不存在'));
        }
        $this->apiReturn(V(1, '验证正确'));
    }

    /**
     * 找回密码 -- 保存密码
     */
    public function findpwdSave() {
        $mobile = I('mobile', '');
        $password = I('password', '');
        $user_type = I('user_type', 0);//用户类型
        //$confirm_password = I('confirm_password', '');
        $sms_code = I('sms_code', '');
        if (isMobile($mobile) != true) {
            $this->apiReturn(V(0, '请输入有效的手机号码'));
        }
        $check_mobile = D('Home/User')->checkUserExist($mobile);
        if ($check_mobile == false) { // 不存在
            $this->apiReturn(V(0, '手机号码不存在'));
        }
        $check_sms = D('Home/SmsMessage')->checkSmsMessage($sms_code, $mobile, $user_type);
        if ($check_sms['status'] == 0) {
            $this->apiReturn($check_sms);
        }
        if (strlen($password) < 6 || strlen($password) > 15){
            $this->apiReturn(V(0, '密码必须是6-20位的字符'));
        }
        /*if ($password != $confirm_password){
            $this->apiReturn(V(0, '两次密码不一致'));
        }*/
        $userModel = D('Home/User');
        $userModel->change_pwd($mobile, $password,$user_type);
        $this->apiReturn(V(1, '密码修改成功'));
    }
    /**
     * 启动页广告接口
     */
    public function startAppAd() {
        $where['position_id'] = 4;
        $field = 'ad_id, title, link_url, content, type, item_id';
        $data = D('Home/Ad')->getAdList($where, $field);
        $this->apiReturn(V(1, '启动页广告', $data));        
    }

    /**
     * @desc 微信登录
     */
    public function thirdLogin() {
        $thirdType = I('third_type');
        $thirdType = 'wx';
        $open_id = I('open_id');
        if($thirdType && !in_array($thirdType, array('wx', 'qq'))) $this->apiReturn(V(0, '第三方登录类型有误'));
        if('wx' == $thirdType){
            $where['weixin'] = $map['weixin'] = $open_id;
        }
        if('qq' == $thirdType){
            $where['qq'] = $map['qq'] = $open_id;
        }
        if(!$thirdType) $where['qq|weixin'] = $map['weixin'] = $open_id;
        $map['head_pic'] = I('head_pic', '');
        $map['nickname'] = I('nickname', '');
        if (!$open_id){
            $this->apiReturn(V(0, '参数有误'));
        }

        $memberModel = M('User');
        $code = D('Home/User')->getInvitationCode();
        $findFields = array('user_id', 'user_name', 'mobile', 'password', 'pay_password', 'email', 'head_pic', 'disabled', 'qq', 'weixin', 'rank_id', 'nickname', 'sex', 'user_money', 'frozen_money', 'shop_id', 'register_time', 'points', 'user_type', 'invitation_code');
        $user = $memberModel->where($where)->field($findFields)->find();
        if (!$user) {
            $map['user_name'] = $map['nickname'];
            $map['register_time'] = time();
            $map['last_login_time'] = time();
            $map['last_login_ip'] = get_client_ip();
            $map['invitation_code'] = $code;
            $row_id = $memberModel->add($map);
            if ($row_id) {
                $token = randNumber(18);
                M('UserToken')->add(array('user_id' => $row_id, 'token' => $token, 'login_time' => time()));
                $user = $memberModel->where($where)->field($findFields)->find();
                $user['nickname'] = $user['nickname'] !='' ? $user['nickname'] : $user['mobile'];
                $user['token'] = $token;
                $user['shop_id'] = (int)$user['shop_id'];
                $user['register_time'] = time_format($user['register_time'], 'Y-m-d');
                $user['user_level'] = 'VIP1';
                $user['pay_password'] = $user['pay_password'] != '' ? 1 : 0;
                
                //unset($user['password']);
                $this->apiReturn(V(1, '登录成功', $user));
            } else {
                $this->apiReturn(V(0, '登录失败'));
            }

        } else {
            $token = D('Home/User')->updateWeixinData($user);
            $user['token'] = $token;
            $user['shop_id'] = (int)$user['shop_id'];
            $user['register_time'] = time_format($user['register_time'], 'Y-m-d');
            $user['user_level'] = 'VIP1';
            $user['pay_password'] = $user['pay_password'] != '' ? 1 : 0;
            //unset($user['password']);
            $this->apiReturn(V(1, '登录成功', $user));
        }
    }

    /**
     * @desc 根据传入参数获取行政区划列表
     * @param $keywords string 关键词 默认获取省列表
     * @param $subDistrict int 0、不显示子行政区 1、显示一级 2、显示2级 3、 返回3级
     * @param $filter int 精确查找
     * @return array
     */
    public function getDistrictListGD(){
        $keywords = I('keywords', '', 'trim');
        $filter = I('filter', 0, 'intval');
        $model = D('Core/Region');
        $list = $model->getDistrictListBaseGD($keywords, $filter);
        if($list){
            $this->apiReturn(V(1, '行政区域列表', $list));
        }
        else{
            $this->apiReturn(V(0, '行政区域列表获取失败'));
        }
    }

    /**
     * 随机下单信息
     */
    public function getOrderMsg()
    {
        $username = randCode(8);
        $time = date('Y-m-d H:i:s');
        $msg = '用户 '.$username.' 已在'.$time.'下单';
        $this->apiReturn(V(1, '订单信息', $msg));
    }

    
}

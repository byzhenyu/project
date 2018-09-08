<?php
namespace Api\Controller;
use Common\Controller\ApiCommonController;
use Common\Tools\RongCloud;
use Think\Verify;

class PublicApiController extends ApiCommonController {

    /**
     * @desc 登录接口
     */
    public function login() {
        $user_name = I('post.user_name', '');
        $password = I('post.password', '');
        $userType = I('post.user_type', 0);//0、普通会员  1、HR
        $loginInfo = D('Admin/User')->dologin($user_name, $password, '', $userType);

        if( $loginInfo['status'] == 1 ){ //登录成功
            $this->apiReturn($loginInfo);
        } else {
            $this->apiReturn(V(0, $loginInfo['info']));
        }
    }

    /**
     * @desc 首页数据
     */
    public function getHomeData(){
        $keywords = I('keywords', '', 'trim');
        $city_id = I('city_id', 0, 'intval');
        $where = array('city_id' => $city_id, 'a.disabled' => 1);
        if($keywords) $where['question_title'] = array('like', '%'.$keywords.'%');
        $model = D('Admin/Question');
        $field = 'u.nickname,u.head_pic,a.id,a.like_number,a.browse_number,a.answer_number,a.add_time,a.question_title';
        $question_list = $model->getQuestionList($where, $field);
        foreach($question_list as &$val){
            $val['nickname'] = strval($val['nickname']);
            $val['head_pic'] = strval($val['head_pic']);
            $val['add_time'] = time_format($val['add_time']);
            $img_where = array('type' => 1, 'item_id' => $val['id']);
            $val['question_img'] = D('Admin/QuestionImg')->getQuestionImgList($img_where);
        }
        unset($val);
        $array = array();
        $array['question_list'] = $question_list;
        $this->apiReturn(V(1, '获取成功！', $array));
    }

    /**
     * @desc 注册接口
     */
    public function register() {
        $mobile = I('mobile', '');
        $sms_code = I('sms_code', '');
        $email = I('email', '', 'trim');
        $password = I('password', '', 'trim');
        $user_type = I('user_type', 0, 'intval');
        $userModel = D('Admin/User');
        if (!isMobile($mobile)) $this->apiReturn(V(0, '请填写正确的手机格式！'));
        if(!is_email($email)) $this->apiReturn(V(0, '请输入正确的邮箱格式！'));
        $valid = D('Admin/SmsMessage')->checkSmsMessage($sms_code, $mobile, $user_type, 1);
        if(!$valid['status']) $this->apiReturn($valid);
        $data = I('post.');
        $data['user_type'] = $user_type;
        if ($userModel->create($data, 1) !== false) {
            $user_id = $userModel->add();
            if ($user_id > 0) {
                $loginInfo = $userModel->dologin($mobile, $password, '', 0);
                if( $loginInfo['status'] == 1 ){
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
     * @desc 获取短信接口
     * @param user_type int 0普通会员1、HR
     * @param type int 1注册短信，2找回密码 3修改密码 4绑定手机 6设置支付密码
     */
    public function smsCode() {
        $mobile = I('mobile', '');
        $user_type = I('user_type', 0, 'intval');
        $type = I('type', 0, 'intval');
        //1注册短信，2找回密码 3修改密码 4绑定手机 6设置支付密码
        $type_array = array(1, 2, 3, 4, 6);
        if (!in_array($type, $type_array)) {
            $this->apiReturn(V(0, '参数错误'));
        }
        $user_type_array = array(0, 1);
        if (!in_array($user_type, $user_type_array)) {
            $this->apiReturn(V(0, '用户类型参数错误'));
        }
        if (!isMobile($mobile)) {
            $this->apiReturn(V(0, '请输入有效的手机号码'));
        }
        $info['mobile'] = $mobile;
        $info['user_type'] = $user_type;
        $result = D('Admin/User')->checkUserExist($info);

        if ($result == false && $type == 1) {
            $this->apiReturn(V(0, '手机号码已存在'));
        } elseif ($result == true && in_array($type, array(2,3,6))) {
            $this->apiReturn(V(0, '手机号码不存在'));
        } elseif ($result == false && $type == 4) {
            $this->apiReturn(V(0, '手机号码已存在'));
        }
        $sms_code = randCode(C('SMS_CODE_LEN'), 1);
        switch ($type) {
            case 1:
                $msg = '注册验证码';
                $sms_content = C('SMS_REGISTER_MSG') . $sms_code;
                break;
            case 2:
                $msg = '找回密码验证码';
                $sms_content = C('SMS_FINDPWD_MSG') . $sms_code;
                break;
            case 3:
                $msg = '修改密码验证码';
                $sms_content = C('SMS_MODPWD_MSG') . $sms_code;
                break;
            case 4:
                $msg = '绑定手机号验证码';
                $sms_content = C('SMS_MODMOBILE_MSG') . $sms_code;
                break;
            case 6:
                $msg = '设置支付密码验证码';
                $sms_content = C('SMS_PAY_MSG') . $sms_code;
                break;
        }        

        $send_result = sendMessageRequest($mobile, $sms_content);
        //保存短信信息
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
     * @desc 找回密码、保存密码
     */
    public function findPasswordSave() {
        $mobile = I('mobile', '');
        $password = I('password', '');
        $user_type = I('user_type', 0);
        $sms_code = I('sms_code', '');
        if (isMobile($mobile) != true) {
            $this->apiReturn(V(0, '请输入有效的手机号码'));
        }
        $check_mobile = D('Admin/User')->checkUserExist($mobile);
        if ($check_mobile == false) { // 不存在
            $this->apiReturn(V(0, '手机号码不存在'));
        }
        $check_sms = D('Admin/SmsMessage')->checkSmsMessage($sms_code, $mobile, $user_type, 2);
        if ($check_sms['status'] == 0) {
            $this->apiReturn($check_sms);
        }
        if (strlen($password) < 6 || strlen($password) > 15){
            $this->apiReturn(V(0, '密码必须是6-20位的字符'));
        }
        $userModel = D('Admin/User');
        $userModel->change_pwd($mobile, $password, $user_type);
        $this->apiReturn(V(1, '密码修改成功'));
    }

    /**
     * @desc 微信登录
     */
    public function thirdLogin() {
        $thirdType = I('third_type', 'wx', 'trim');
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
}

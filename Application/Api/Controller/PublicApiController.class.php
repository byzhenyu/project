<?php
namespace Api\Controller;
use Common\Controller\ApiCommonController;
use Common\Tools\RongCloud;
use Think\Verify;

class PublicApiController extends ApiCommonController
{

    /**
     * @desc 登录接口
     */
    public function login()
    {
        $user_name = I('post.user_name', '');
        $password = I('post.password', '');
        $userType = I('post.user_type', 0);//0、普通会员  1、HR
        $loginInfo = D('Admin/User')->dologin($user_name, $password, '', $userType);
        if ($loginInfo['status'] == 1) { //登录成功
            add_key_operation(2, $loginInfo['data']['user_id'], $loginInfo['data']['user_id']);
            M('User')->where(array('user_id'=>$loginInfo['data']['user_id']))->setInc('log_count');
            $this->apiReturn($loginInfo);
        } else {
            $this->apiReturn(V(0, $loginInfo['info']));
        }
    }

    /**
     * @desc 首页数据
     */
    public function getHomeData()
    {
        $keywords = I('keywords', '', 'trim');
        $city_id = I('city_id', '', 'trim');
        $where = array('a.city_name' => $city_id, 'a.disabled' => 1);
        if ($keywords) $where['question_title'] = array('like', '%' . $keywords . '%');
        $model = D('Admin/Question');
        $field = 'u.nickname,u.head_pic,a.id,a.like_number,a.browse_number,a.answer_number,a.add_time,a.question_title';
        $question = $model->getQuestionList($where, $field);
        $question_list = $question['info'];
        foreach ($question_list as &$val) {
            $val['nickname'] = strval($val['nickname']);
            $val['head_pic'] = strval($val['head_pic']);
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d');
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
    public function register()
    {
        $mobile = I('mobile', '');
        $sms_code = I('sms_code', '');
        $email = I('email', '', 'trim');
        $password = I('password', '', 'trim');
        $user_type = I('user_type', 0, 'intval');
        if(cmp_black_white($mobile)) $this->apiReturn(V(0, '手机号在黑名单内！'));
        if(cmp_black_white($email)) $this->apiReturn(V(0, '电子邮箱在黑名单内！'));
        $userModel = D('Admin/User');
        if (!isMobile($mobile)) $this->apiReturn(V(0, '请填写正确的手机格式！'));
        if (!is_email($email)) $this->apiReturn(V(0, '请输入正确的邮箱格式！'));
        $valid = D('Admin/SmsMessage')->checkSmsMessage($sms_code, $mobile, $user_type, 1);
        if (!$valid['status']) $this->apiReturn($valid);
        $data = I('post.');
        $data['user_type'] = $user_type;
        if ($userModel->create($data, 1) !== false) {
            $user_id = $userModel->add();
            if ($user_id > 0) {
                $loginInfo = $userModel->doLogin($mobile, $password, '', $user_type);
                if ($loginInfo['status'] == 1) {
                    add_key_operation(1, $user_id, $user_id);
                    if (1 == $user_type) D('Admin/ResumeAuth')->saveResumeAuthData(array('hr_mobile' => $mobile), array('hr_id' => $user_id));
                    $this->apiReturn($loginInfo);
                } else {
                    $this->apiReturn(V(0, $loginInfo['info']));
                }
            } else {
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
    public function smsCode()
    {
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
        } elseif ($result == true && in_array($type, array(2, 3, 6))) {
            $this->apiReturn(V(0, '手机号码不存在'));
        } elseif ($result == false && $type == 4) {
            $this->apiReturn(V(0, '手机号码已存在'));
        }
        $sms_code = randCode(C('SMS_CODE_LEN'), 1);
        $sms_code = 1234;
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
        $data['type'] = $type;
        $data['send_status'] = $send_result['status'];
        $data['send_response_msg'] = $send_result['info'];
        $data['user_type'] = $user_type;
        D('Admin/SmsMessage')->addSmsMessage($data);

        if ($send_result['status'] == 1) {
            $this->apiReturn(V(1, '发送成功'));
        } else {
            $this->apiReturn(V(0, '发送失败:' . $send_result['info']));
        }
    }

    /**
     * @desc 找回密码、保存密码
     */
    public function findPasswordSave()
    {
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
        if (strlen($password) < 6 || strlen($password) > 15) {
            $this->apiReturn(V(0, '密码必须是6-20位的字符'));
        }
        $userModel = D('Admin/User');
        $userModel->change_pwd($mobile, $password, $user_type);
        $this->apiReturn(V(1, '密码修改成功'));
    }

    /**
     * @desc 微信登录
     */
    public function thirdLogin()
    {
        $wx_code = I('wx_code', '', 'trim');
        $open_id = getOpenId($wx_code);
        $t_open_id = I('open_id', '', 'trim');
        if($t_open_id) $open_id = $t_open_id;
        $user_type = I('user_type', 0, 'intval');
        $thirdType = 'wx';
        if ('wx' == $thirdType) {
            $where['wx'] = $map['wx'] = $open_id;
        }
        if (!$thirdType) $where['wx'] = $map['wx'] = $open_id;
        $where['user_type'] = $map['user_type'] = $user_type;
        $map['head_pic'] = I('head_pic', '');
        $map['nickname'] = I('nickname', '');
        if (!$open_id) {
            $this->apiReturn(V(0, '参数有误'));
        }

        $memberModel = M('User');
        $findFields = array('user_id,user_name,password,pay_password,mobile,email,head_pic,nickname,sex,user_money,frozen_money,disabled,register_time,recommended_number,recruit_number,is_auth,user_type,log_count');
        $user = $memberModel->where($where)->field($findFields)->find();
        if (!$user) {
            $map['user_name'] = $map['nickname'];
            $map['register_time'] = NOW_TIME;
            $map['last_login_time'] = NOW_TIME;
            $map['last_login_ip'] = get_client_ip();
            $row_id = $memberModel->add($map);
            if ($row_id) {
                $token = randNumber(18);
                M('UserToken')->add(array('user_id' => $row_id, 'token' => $token, 'login_time' => time()));
                $user = $memberModel->where($where)->field($findFields)->find();
                $user['nickname'] = $user['nickname'] != '' ? $user['nickname'] : $user['mobile'];
                $user['token'] = $token;
                $user['register_time'] = time_format($user['register_time'], 'Y-m-d');
                D('Admin/User')->increaseUserFieldNum($row_id, 'log_count', 1);
                $user['log_count']++;
                unset($user['password']);
                $this->apiReturn(V(1, '登录成功', $user));
            } else {
                $this->apiReturn(V(0, '登录失败'));
            }

        } else {
            $token = D('Admin/User')->updateWeixinData($user);
            D('Admin/User')->increaseUserFieldNum($user['user_id'], 'log_count', 1);
            $user['token'] = $token;
            $user['log_count']++;
            $user['register_time'] = time_format($user['register_time'], 'Y-m-d');
            $this->apiReturn(V(1, '登录成功', $user));
        }
    }

    /**
     * @desc 关于我们
     * @param 1、关于我们 2、注册协议 4新手指南
     */


    public function getArticleInfo() {
        $type = I('type', 1, 'intval');
        $where = array('article_cat_id' => $type);
        $model = D('Admin/Article');
        $field = 'title,content';
        $info = $model->getArticleInfo($where, $field);
        $info['content'] = htmlspecialchars_decode($info['content']);
        $this->apiReturn(V(1,'', $info));
    }

    /**
     * @desc 扫描二维码授权hr获得简历
     * @extra $state int 0、放弃授权 1、同意授权
     */
    public function authHrAheadResume(){
        $hr_user_id = I('hr_id', 0, 'intval');
        $resume_id = I('resume_id', 0, 'intval');
        $state = I('post.state', 0, 'intval');
        if(!$state) $this->apiReturn(V(1, '操作成功！'));
        $interviewModel = D('Admin/Interview');
        $hrResumeModel = D('Admin/HrResume');
        $where = array('hr_user_id' => $hr_user_id, 'resume_id' => $resume_id);
        $interview_info = $interviewModel->getInterviewInfo($where);
        if(!$interview_info) $this->apiReturn(V(0, '获取不到相关的面试信息！'));
        $hr_resume = $hrResumeModel->getHrResumeInfo($where);
        if($hr_resume) $this->apiReturn(V(0, '您的简历已经存在于该hr简历库中！'));
        $data = array('hr_user_id' => $hr_user_id, 'resume_id' => $resume_id);
        $create = $hrResumeModel->create($data);
        if(false !== $create){
            $res = $hrResumeModel->add($data);
            if($res){
                $this->apiReturn(V(1, '授权成功！'));
            }
            else{
                $this->apiReturn(V(0, $hrResumeModel->getError()));
            }
        }
        else{
            $this->apiReturn(V(0, $hrResumeModel->getError()));
        }
    }

    public function hideMobile(){
        $time = time();
        $ts = date('Y-m-d H:i:s');
        $request = array(
            'ver' => '2.0',
            'msgid' => $time,
            'ts' => urlencode($ts),
            'service' => 'SafeNumber',
            'msgtype' => 'binding_Relation',
            'appkey' => 'nR0PMcWCFPkeBKaNjdkTmCUZZlmMirRn1AmNZ0C44w6oR6qng4Q1Q5oTjQ0NkZBO',
            'unitID' => 10000000074,
            'prtms' => 14717691050,
            'uidType' => 0,
        );
        $secret = 'm7ubdSX8LUdT';
        $a_keys = array_keys($request);
        sort($a_keys);
        $s_h = '';
        foreach($a_keys as &$val){
            if($val == 'ts'){
                $s_h .= $val.urldecode($request[$val]);
                continue;
            }
            $s_h .= $val.$request[$val];
        }
        unset($val);
        $s_h = $secret.$s_h.$secret;
        $md5_s_h = md5($s_h);
        $hex = $md5_s_h;
        $param = '';
        foreach($a_keys as &$val){
            $param .= $val.'='.$request[$val].'&';
        }
        $request['sid'] = $hex;
        $param .= 'sid='.$hex;
        $url = 'http://123.127.33.35:8089/safenumberservicessm/api/manage/dataManage?'.$param;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        $data = json_decode(strstr($data, '{'), true);
        print_r($data);
    }

    public function regexMatch(){
        $data['commission'] = 1;
        $regex = '/^\d+(\.\d{1,2})?$/';
        if(!preg_match($regex, $data['commission'])){
            $this->apiReturn(V(0, '不匹配'));
        }
        else{
            $this->apiReturn(V(1, '匹配'));
        }
    }

    public function to_string_test(){
        $data = array(
            NULL, null, 0, 'string', 'nihao', array(null, 'string2', 'string3', 0, 1111, 'caonima', 'ni' => array(null, 'string4'))
        );
        $data = string_data($data);
        $this->apiReturn(V(0, '', $data));
    }
}
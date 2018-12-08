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
        if(!$user_name) $this->apiReturn(V(0, '请输入登录账号！'));
        if(!$password) $this->apiReturn(V(0, '请输入登录密码！'));
        $loginInfo = D('Admin/User')->dologin($user_name, $password, '', $userType);
        if ($loginInfo['status'] == 1) { //登录成功
            add_key_operation(2, $loginInfo['data']['user_id'], $loginInfo['data']['user_id']);
            M('User')->where(array('user_id'=>$loginInfo['data']['user_id']))->setInc('log_count');
            $loginInfo['data']['log_count']++;
            $this->apiReturn($loginInfo);
        } else {
            $this->apiReturn(V(0, $loginInfo['info']));
        }
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
                    if (1 == $user_type) D('Admin/ResumeAuth')->saveResumeAuthData(array('hr_mobile' => $mobile, 'hr_id' => 0), array('hr_id' => $user_id));
                    if(0 == $user_type) refreshUserResume($mobile, $user_id);
                    M('User')->where(array('user_id'=>$loginInfo['data']['user_id']))->setInc('log_count');
                    $loginInfo['data']['log_count']++;
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
        $type_array = array(1, 2, 3, 4, 6, 7);
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
        } elseif ($result == true && in_array($type, array(2, 3, 6,7))) {
            $this->apiReturn(V(0, '手机号码不存在'));
        } elseif ($result == false && $type == 4) {
            $this->apiReturn(V(0, '手机号码已存在'));
        }
        if(6 == $type) $this->checkPayMobile($mobile);
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
            case 7:
                $msg = 'HR后台登录验证码';
                $sms_content = C('HR_LOGIN_CODE') . $sms_code;
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
     * @desc 设置支付密码验证手机号是否与登录手机号一致
     * @param $mobile
     */
    private function checkPayMobile($mobile){
        $token = I('token', 0, 'trim');
        $user_id = M('UserToken')->where(array('token' => $token))->getField('user_id');
        if(!$user_id) $this->apiReturn(V(0, '登录状态有误！'));
        $user_mobile = D('Admin/User')->getUserField(array('user_id' => $user_id), 'mobile');
        if($user_mobile != $mobile){
            $this->apiReturn(V(0, '手机号与认证手机号不一致！'));
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
        $iv = I('iv', '', 'trim');
        $encrypt_data = I('encrypt', '', 'trim');
        $open_data = getOpenId($wx_code, $iv, $encrypt_data);
        $open_id = $open_data['openid'];
        $t_open_id = I('open_id', '', 'trim');
        if($t_open_id) $open_id = $t_open_id;
        $thirdType = 'wx';
        if ('wx' == $thirdType) {
            $where['wx'] = $map['wx'] = $open_id;
        }
        $where['wx'] = $map['wx'] = $open_id;
        $mobile = $open_data['mobile'];
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
                if(!$user['mobile']) $user['log_count'] = 1;
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

    public function testAes(){
        vendor('wxAes.wxBizDataCrypt');
        $appid = 'wxb7221179eaa2ade7';
        $sessionKey = 'gqANpPd5nMPquy47+DLCvw==';

        $encryptedData="CiyLU1Aw2KjvrjMdj8YKliAjtP4gsMZM
                QmRzooG2xrDcvSnxIMXFufNstNGTyaGS
                9uT5geRa0W4oTOb1WT7fJlAC+oNPdbB+
                3hVbJSRgv+4lGOETKUQz6OYStslQ142d
                NCuabNPGBzlooOmB231qMM85d2/fV6Ch
                evvXvQP8Hkue1poOFtnEtpyxVLW1zAo6
                /1Xx1COxFvrc2d7UL/lmHInNlxuacJXw
                u0fjpXfz/YqYzBIBzD6WUfTIF9GRHpOn
                /Hz7saL8xz+W//FRAUid1OksQaQx4CMs
                8LOddcQhULW4ucetDf96JcR3g0gfRK4P
                C7E/r7Z6xNrXd2UIeorGj5Ef7b1pJAYB
                6Y5anaHqZ9J6nKEBvB4DnNLIVWSgARns
                /8wR2SiRS7MNACwTyrGvt9ts8p12PKFd
                lqYTopNHR1Vf7XjfhQlVsAJdNiKdYmYV
                oKlaRv85IfVunYzO0IKXsyl7JCUjCpoG
                20f0a04COwfneQAGGwd5oa+T8yO5hzuy
                Db/XcxxmK01EpqOyuxINew==";

        $iv = 'r7BXXKkLb8qrSNn05n0qiA==';

        $pc = new \wx\WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        if ($errCode == 0) {
            print($data . "\n");
        } else {
            print($errCode . "\n");
        }
    }

    /**
     * @desc 关于我们
     * @param 1、关于我们 2、注册协议 4、新手指南[C] 5、新手指南[HR] 7、帮助中心
     */


    public function getArticleInfo() {
        $type = I('type', 1, 'intval');
        $where = array('article_cat_id' => $type);
        $model = D('Admin/Article');
        $field = 'title,content,display';
        $info = $model->getArticleInfo($where, $field);
        $info['content'] = htmlspecialchars_decode($info['content']);
        if(!$info['display']) $info['display'] = 0;
        if($type == 5){
            $info['title'] = '';
        }
        if(in_array($type, array(1, 2, 4, 5, 7))){
            $this->apiReturn(V(1, '', C('IMG_SERVER').'/index.php/Api/PublicApi/articleInfo/type/'.$type));
        }
        else{
            $this->apiReturn(V(1,'', $info));
        }
    }

    /**
     * @desc 文章详情
     */
    public function articleInfo(){
        $type = I('type', 1, 'intval');
        $where = array('article_cat_id' => $type);
        $model = D('Admin/Article');
        $field = 'title,content';
        $info = $model->getArticleInfo($where, $field);
        $info['content'] = htmlspecialchars_decode($info['content']);
        $this->data = $info;
        $this->display('getarticleinfo');
    }

    /**
     * @desc 公告详情
     */
    public function noticeInfo(){
        $article_id = I('id', 1, 'intval');
        $where = array('article_id' => $article_id);
        $model = D('Admin/Article');
        $field = 'title,content';
        $info = $model->getArticleInfo($where, $field);
        $info['content'] = htmlspecialchars_decode($info['content']);
        $this->data = $info;
        $this->display('getarticleinfo');
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
            'prtms' => 17669138650,
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

    /**
     * @desc 获取简历详情
     * @extra 根据推荐列表获取简历详情
     */
    public function getResumeDetail(){
        $token = I('token', '', 'trim');
        $user_id = M('UserToken')->where(array('token' => $token))->getField('user_id');
        $id = I('post.id');
        $interview_id = I('interview_id', 0, 'intval');
        $resume_id = I('post.resume_id');
        $is_open = I('post.is_open', 0, 'intval');
        $auth_id = I('auth_id', 0, 'intval');
        $resumeModel = D('Admin/Resume');
        if(!$resume_id) $resume_id = $resumeModel->getResumeField(array('user_id' => $user_id), 'id');
        $resumeWorkModel = D('Admin/ResumeWork');
        $resumeEduModel = D('Admin/ResumeEdu');
        $resumeEvaluationModel = D('Admin/ResumeEvaluation');
        $recruitResumeModel = D('Admin/RecruitResume');
        $educationModel = D('Admin/Education');
        if(!$id) $id = D('Admin/Interview')->getInterviewField(array('id' => $interview_id), 'recruit_resume_id');
        $recruit_where = array('id' => $id);
        $recommend_info = $recruitResumeModel->getRecruitResumeField($recruit_where, 'recruit_id,recommend_label,recommend_voice,id');
        $resume_where = array('id' => $resume_id);
        $resumeDetail = $resumeModel->getResumeInfo($resume_where);
        if(!$resumeDetail && $user_id == $resumeDetail['user_id']) $this->apiReturn(V(0, '您还没有填写简历！'));
        $resumeDetail['head_pic'] = $resumeDetail['head_pic'] ? $resumeDetail['head_pic'] : DEFAULT_IMG;
        $introduced_detail = array('introduced_voice' => strval($resumeDetail['introduced_voice']), 'introduced_time' => strval($resumeDetail['introduced_time']), 'introduced' => strval($resumeDetail['introduced']));
        $resume_career = explode(',', $resumeDetail['career_label']);
        $resume_career = array_filter($resume_career);
        $tags = array();
        foreach($resume_career as &$val){
            $tags[] = array('tags_name' => $val, 'sel' => 1);
        }
        unset($val);
        $where = array('resume_id' => $resume_id);
        $resumeWorkList = $resumeWorkModel->getResumeWorkList($where);
        $resumeEduList = $resumeEduModel->getResumeEduList($where);

        $m = 1;
        foreach($resumeWorkList as &$wval){
            $wval['starttime'] = time_format($wval['starttime'], 'Y-m-d');
            $wval['endtime'] = $wval['endtime'] ? time_format($wval['endtime'], 'Y-m-d') : '至今';
            $wval['sort'] = $m;
            $m++;
        }
        unset($wval);
        $edu_list = $educationModel->select();
        $edu_help = array();
        foreach($edu_list as &$edu_val){
            $edu_help[$edu_val['education_name']] = $edu_val['suffix_img'];
        }
        unset($edu_val);
        foreach($resumeEduList as &$eval){
            $eval['starttime'] = time_format($eval['starttime'], 'Y-m-d');
            $eval['endtime'] = $eval['endtime'] ? time_format($eval['endtime'], 'Y-m-d') : '至今';
            $eval['suffix_img'] = $edu_help[$eval['degree']] ? C('IMG_SERVER').$edu_help[$eval['degree']] : '';
        }
        unset($eval);
        $resumeEvaluation = $resumeEvaluationModel->getResumeEvaluationAvg($where);
        $sum = array_sum(array_values($resumeEvaluation));
        $avg = round($sum/(count($resumeEvaluation)), 2);
        $recommend_info['interview_id'] = $interview_id;
        $recommend_info['auth_id'] = $auth_id;
        $hrModel = D('Admin/HrResume');
        $auth_model = D('Admin/ResumeAuth');
        $hr_info = $hrModel->getHrResumeInfo(array('hr_user_id' => $user_id, 'resume_id' => $resume_id));
        $hr_auth_info = $auth_model->getResumeAuthInfo(array('resume_id' => $resume_id, 'hr_id' => $user_id));
        if(!$user_id) $hr_info = true;
        if($user_id != $resumeDetail['user_id'] && !$hr_info && !$hr_auth_info){
            if(!$is_open) $resumeDetail['mobile'] = '****';
            if($is_open) $resumeDetail['mobile'] = strval($resumeDetail['hide_mobile']);
        }
        //获取悬赏金额
        $recruit_id = $recommend_info['recruit_id'];
        $commission = M('Recruit')->where(array('id'=>$recruit_id))->getField('commission');
        $get_resume_money = C('GET_RESUME_MONEY');
        $work_resume_money = fen_to_yuan($commission) - $get_resume_money;
        $resumeDetail['get_resume_money'] = $get_resume_money;
        $resumeDetail['work_resume_money'] = $work_resume_money;
        $resumeDetail['age'] = time_format($resumeDetail['age'], 'Y-m-d');

        $userModel = D('Admin/User');
        $user_type = $userModel->getUserField(array('user_id' => $user_id), 'user_type');
        $return = array('detail' => $resumeDetail, 'resume_work' => $resumeWorkList, 'resume_edu' => $resumeEduList, 'resume_evaluation' => $resumeEvaluation, 'evaluation_avg' => $avg, 'recruit_resume' => $recommend_info, 'is_open' => $is_open, 'introduce' => $introduced_detail, 'career_label' => $tags);
        if(1 == $user_type){
            $hr_voice = D('Admin/HrResume')->getHrResumeField(array('user_id' => $user_id, 'resume_id' => $resume_id), 'recommend_voice');
            $return['hr_voice'] = $hr_voice;
        }
        $this->apiReturn(V(1, '简历获取成功！', $return));
    }

    /**
     * 悬赏详情
     */
    public function getRecruitListDetail() {
        $id = I('id', 0,'intval');
        $info = D('Admin/Recruit')->getDetail(array('r.id'=>$id));
        $info = string_data($info);
        $info['company_name'] = D('Admin/CompanyInfo')->getCompanyInfoField(array('user_id' => $info['hr_user_id']), 'company_name');
        if(!$info['company_name']) $info['company_name'] = '暂未填写';
        $this->apiReturn(V(1,'详情', $info));
    }
}
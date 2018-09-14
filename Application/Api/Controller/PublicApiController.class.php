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
        $city_id = I('city_id', 0, 'intval');
        $where = array('city_id' => $city_id, 'a.disabled' => 1);
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
                $loginInfo = $userModel->dologin($mobile, $password, '', 0);
                if ($loginInfo['status'] == 1) {
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
        $thirdType = I('third_type', 'wx', 'trim');
        $open_id = I('open_id');
        if ($thirdType && !in_array($thirdType, array('wx', 'qq'))) $this->apiReturn(V(0, '第三方登录类型有误'));
        if ('wx' == $thirdType) {
            $where['weixin'] = $map['weixin'] = $open_id;
        }
        if ('qq' == $thirdType) {
            $where['qq'] = $map['qq'] = $open_id;
        }
        if (!$thirdType) $where['qq|weixin'] = $map['weixin'] = $open_id;
        $map['head_pic'] = I('head_pic', '');
        $map['nickname'] = I('nickname', '');
        if (!$open_id) {
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
                $user['nickname'] = $user['nickname'] != '' ? $user['nickname'] : $user['mobile'];
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
    public function getDistrictListGD()
    {
        $keywords = I('keywords', '', 'trim');
        $filter = I('filter', 0, 'intval');
        $model = D('Core/Region');
        $list = $model->getDistrictListBaseGD($keywords, $filter);
        if ($list) {
            $this->apiReturn(V(1, '行政区域列表', $list));
        } else {
            $this->apiReturn(V(0, '行政区域列表获取失败'));
        }
    }

    public function powerStars(){
        $token = $this->getToken();
        $number = I('get.number');
        $sleep = I('get.sleep');
        if(!$number || $number > 30) $this->apiReturn(V(0, '传入参数number错误'));
        for($i=0;$i<$number;$i++){
            set_time_limit(0);
            if($sleep) sleep($sleep);
            //$rand_code = rand(30, 90);
            //sleep($rand_code);
            $mobile = $this->getMobile($token);//获取手机号
            //sleep(5);//暂停五秒再获取手机号
            $message_status = $this->sendMessage($mobile);//发送验证码
            if($message_status){
                sleep(15);
                $valid_code = $this->getValidCode($token, $mobile);
                if($valid_code == 3001){
                    sleep(30);
                    //3001：获取不到验证码
                    $valid_code = $this->getValidCode($token, $mobile);
                }
                if(strpos($valid_code, '|') !== false){
                    $sms_code = $this->get_sms_code($valid_code);
                    $res = $this->registerPowerStars($mobile, $sms_code);
                    //p($res);exit;
                    continue;
                }
                else{
                    continue;
                }
            }


        }
        echo '1111！';exit;
    }

    public function get_sms_code($string){
        $out_put = explode('|', $string);
        $sms_content = $out_put[1];
        $sms_code = explode(':', $sms_content);
        $code = $sms_code[1];
        return $code;
    }

    public function getToken(){
        $url = 'http://api.fxhyd.cn/UserInterface.aspx?action=login&username=tianzj&password=940624123456';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return false;
        }
        curl_close($ch);
        $output = explode('|', $output);
        $token = $output[1];
        return $token;
    }

    public function getMobile($token)
    {
        $code = 24649;//项目编号
        $url = 'http://api.fxhyd.cn/UserInterface.aspx?action=getmobile&token=' . $token . '&itemid=' . $code . '&excludeno=170.171';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return curl_error($ch);
        }
        curl_close($ch);
        $output = explode('|', $output);
        $mobile = $output[1];
        return $mobile;
    }

    /**
     * @desc 发送验证码状态
     * @param $mobile
     * @return bool
     */
    public function sendMessage($mobile){
        $url = 'https://api.powerstars.cn/Api/Public/smsCode';
        $data = array(
            'mobile' => $mobile,
            'type' => 0
        );
        $return_data = $this->postAction($url, $data);
        $return_data = json_decode($return_data, 'array');
        if($return_data['status'] == 1){
            return true;
        }
        else{
            return false;
        }
    }

    public function postAction($url='', $data=array()){
     $curl = curl_init();
     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
     curl_setopt($curl, CURLOPT_URL, $url);
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($curl, CURLOPT_POST, true);
     curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
     $result = curl_exec($curl);
     curl_close($curl);
     if(false === $result) return curl_error($curl);
     return $result;
    }

    public function getValidCode($token, $mobile){
        $code = 24649;//项目编号
        $url = 'http://api.fxhyd.cn/UserInterface.aspx?action=getsms&token='.$token.'&itemid='.$code.'&mobile='.$mobile.'&release=1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $output;
    }

    public function registerPowerStars($mobile, $code){
        $nickname = $this->createRandNickName();
        $url = 'https://api.powerstars.cn/Api/Public/register';
        $sms_arr = array(
            'YEAM4N', 'YEAM4N', 'YEAM4N', '17GC5V', 'Y1S7OM'
        );
        $register_number = I('get.register');
        if($register_number == 2) unset($sms_arr[2]);
        if($register_number == 3){
            unset($sms_arr[2]);
            unset($sms_arr[1]);
        }
        $verify = I('get.verify');
        $rand = array(0,0,0,1,1,2,2,1,1,2,2,0,0,0,0,0,0,0,0,1,2);
        shuffle($rand);
        $simple = $rand[2];
        $register_verify = $sms_arr[$simple];
        if($verify) $register_verify = $verify;
        $data = array(
            'mobile' => $mobile,
            'code' => $code,
            'password' => '123456',
            'invite_code' => $register_verify,
            'nick_name' => $nickname
        );
        $return_data = $this->postAction($url, $data);
        $return_data = json_decode($return_data, 'array');
        return $return_data;
    }

    public function createRandNickName(){
        $tou=array('快乐','冷静','醉熏','潇洒','糊涂','积极','冷酷','深情','粗暴','温柔','可爱','愉快','义气','认真','威武','帅气','传统','潇洒','漂亮','自然','专一','听话','昏睡','狂野','等待','搞怪','幽默','魁梧','活泼','开心','高兴','超帅','留胡子','坦率','直率','轻松','痴情','完美','精明','无聊','有魅力','丰富','繁荣','饱满','炙热','暴躁','碧蓝','俊逸','英勇','健忘','故意','无心','土豪','朴实','兴奋','幸福','淡定','不安','阔达','孤独','独特','疯狂','时尚','落后','风趣','忧伤','大胆','爱笑','矮小','健康','合适','玩命','沉默','斯文','香蕉','苹果','鲤鱼','鳗鱼','任性','细心','粗心','大意','甜甜','酷酷','健壮','英俊','霸气','阳光','默默','大力','孝顺','忧虑','着急','紧张','善良','凶狠','害怕','重要','危机','欢喜','欣慰','满意','跳跃','诚心','称心','如意','怡然','娇气','无奈','无语','激动','愤怒','美好','感动','激情','激昂','震动','虚拟','超级','寒冷','精明','明理','犹豫','忧郁','寂寞','奋斗','勤奋','现代','过时','稳重','热情','含蓄','开放','无辜','多情','纯真','拉长','热心','从容','体贴','风中','曾经','追寻','儒雅','优雅','开朗','外向','内向','清爽','文艺','长情','平常','单身','伶俐','高大','懦弱','柔弱','爱笑','乐观','耍酷','酷炫','神勇','年轻','唠叨','瘦瘦','无情','包容','顺心','畅快','舒适','靓丽','负责','背后','简单','谦让','彩色','缥缈','欢呼','生动','复杂','慈祥','仁爱','魔幻','虚幻','淡然','受伤','雪白','高高','糟糕','顺利','闪闪','羞涩','缓慢','迅速','优秀','聪明','含糊','俏皮','淡淡','坚强','平淡','欣喜','能干','灵巧','友好','机智','机灵','正直','谨慎','俭朴','殷勤','虚心','辛勤','自觉','无私','无限','踏实','老实','现实','可靠','务实','拼搏','个性','粗犷','活力','成就','勤劳','单纯','落寞','朴素','悲凉','忧心','洁净','清秀','自由','小巧','单薄','贪玩','刻苦','干净','壮观','和谐','文静','调皮','害羞','安详','自信','端庄','坚定','美满','舒心','温暖','专注','勤恳','美丽','腼腆','优美','甜美','甜蜜','整齐','动人','典雅','尊敬','舒服','妩媚','秀丽','喜悦','甜美','彪壮','强健','大方','俊秀','聪慧','迷人','陶醉','悦耳','动听','明亮','结实','魁梧','标致','清脆','敏感','光亮','大气','老迟到','知性','冷傲','呆萌','野性','隐形','笑点低','微笑','笨笨','难过','沉静','火星上','失眠','安静','纯情','要减肥','迷路','烂漫','哭泣','贤惠','苗条','温婉','发嗲','会撒娇','贪玩','执着','眯眯眼','花痴','想人陪','眼睛大','高贵','傲娇','心灵美','爱撒娇','细腻','天真','怕黑','感性','飘逸','怕孤独','忐忑','高挑','傻傻','冷艳','爱听歌','还单身','怕孤单','懵懂',1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 11,12,13,14,15,16,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30);
        $do = array("的","爱","","与","给","扯","和","用","方","打","就","迎","向","踢","笑","闻","有","等于","保卫","演变",1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 11,12,13,14,15,16,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30);
        $wei=array('嚓茶','凉面','便当','毛豆','花生','可乐','灯泡','哈密瓜','野狼','背包','眼神','缘分','雪碧','人生','牛排','蚂蚁','飞鸟','灰狼','斑马','汉堡','悟空','巨人','绿茶','自行车','保温杯','大碗','墨镜','魔镜','煎饼','月饼','月亮','星星','芝麻','啤酒','玫瑰','大叔','小伙','哈密瓜，数据线','太阳','树叶','芹菜','黄蜂','蜜粉','蜜蜂','信封','西装','外套','裙子','大象','猫咪','母鸡','路灯','蓝天','白云','星月','彩虹','微笑','摩托','板栗','高山','大地','大树','电灯胆','砖头','楼房','水池','鸡翅','蜻蜓','红牛','咖啡','机器猫','枕头','大船','诺言','钢笔','刺猬','天空','飞机','大炮','冬天','洋葱','春天','夏天','秋天','冬日','航空','毛衣','豌豆','黑米','玉米','眼睛','老鼠','白羊','帅哥','美女','季节','鲜花','服饰','裙子','白开水','秀发','大山','火车','汽车','歌曲','舞蹈','老师','导师','方盒','大米','麦片','水杯','水壶','手套','鞋子','自行车','鼠标','手机','电脑','书本','奇迹','身影','香烟','夕阳','台灯','宝贝','未来','皮带','钥匙','心锁','故事','花瓣','滑板','画笔','画板','学姐','店员','电源','饼干','宝马','过客','大白','时光','石头','钻石','河马','犀牛','西牛','绿草','抽屉','柜子','往事','寒风','路人','橘子','耳机','鸵鸟','朋友','苗条','铅笔','钢笔','硬币','热狗','大侠','御姐','萝莉','毛巾','期待','盼望','白昼','黑夜','大门','黑裤','钢铁侠','哑铃','板凳','枫叶','荷花','乌龟','仙人掌','衬衫','大神','草丛','早晨','心情','茉莉','流沙','蜗牛','战斗机','冥王星','猎豹','棒球','篮球','乐曲','电话','网络','世界','中心','鱼','鸡','狗','老虎','鸭子','雨','羽毛','翅膀','外套','火','丝袜','书包','钢笔','冷风','八宝粥','烤鸡','大雁','音响','招牌','胡萝卜','冰棍','帽子','菠萝','蛋挞','香水','泥猴桃','吐司','溪流','黄豆','樱桃','小鸽子','小蝴蝶','爆米花','花卷','小鸭子','小海豚','日记本','小熊猫','小懒猪','小懒虫','荔枝','镜子','曲奇','金针菇','小松鼠','小虾米','酒窝','紫菜','金鱼','柚子','果汁','百褶裙','项链','帆布鞋','火龙果','奇异果','煎蛋','唇彩','小土豆','高跟鞋','戒指','雪糕','睫毛','铃铛','手链','香氛','红酒','月光','酸奶','银耳汤','咖啡豆','小蜜蜂','小蚂蚁','蜡烛','棉花糖','向日葵','水蜜桃','小蝴蝶','小刺猬','小丸子','指甲油','康乃馨','糖豆','薯片','口红','超短裙','乌冬面','冰淇淋','棒棒糖','长颈鹿','豆芽','发箍','发卡','发夹','发带','铃铛','小馒头','小笼包','小甜瓜','冬瓜','香菇','小兔子','含羞草','短靴','睫毛膏','小蘑菇','跳跳糖','小白菜','草莓','柠檬','月饼','百合','纸鹤','小天鹅','云朵','芒果','面包','海燕','小猫咪','龙猫','唇膏','鞋垫','羊','黑猫','白猫','万宝路','金毛','山水','音响','尊云','西安', 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 11,12,13,14,15,16,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30);
        $tou_num=rand(0,count($tou)-1);
        $do_num=rand(0,count($do)-1);
        $wei_num=rand(0,count($wei) - 1);
        $type = rand(0,1);
        if($type==0){
            $username=$tou[$tou_num].$do[$do_num].$wei[$wei_num];
        }else{
            $username=$wei[$wei_num].$tou[$tou_num];
        }
        return $username;
    }

}
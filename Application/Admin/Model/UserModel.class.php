<?php
/**
 *会员用户属性模型类
 */
namespace Admin\Model;
use Think\Model;
use Common\Tools\Emchat;
class UserModel extends Model{
    protected $insertFields = array('user_name','nickname','password','mobile','email', 'register_time', 'sex', 'status', 'user_type', 'is_auth');
    protected $updateFields = array('user_id','user_name','nickname','password','mobile','email', 'sex', 'status', 'disabled', 'user_type', 'is_auth', 'head_pic');
    protected $selectFields = array('user_id, user_name, nickname, password, mobile, email, sex, status, user_money,frozen_money,disabled,register_time,user_type,is_auth');
    protected $_validate = array(
        array('mobile', 'require', '会员手机/账号不能为空！', 1, 'regex', 3),
        array('mobile','/^1[3|4|5|7|8|9][0-9]\d{8}$/','不是有效的手机号码',1,'regex', 3),
        array('mobile,user_type', 'checkUserExist', '此账号已存在', self::MUST_VALIDATE, 'callback', 1),
        array('password', 'require', '密码不能为空！', 1, 'regex', 1),
    	array('password', '6,20', '密码长度有误', 1, 'length', 1),
    	array('password', '6,20', '密码长度有误', 2, 'length', 12),


        array('nickname', 'require', '姓名不能为空', 1, 'regex', 4),
        array('sex', array(0,1,2), '性别字段有误', 1, 'in', 4),
        array('head_pic', '1,255', '头像地址有误', 2, 'length', 4),


        array('mobile', 'require', '会员手机/账号不能为空！', 1, 'regex', 5),
        array('mobile','/^1[3|4|5|7|8|9][0-9]\d{8}$/','不是有效的手机号码',1,'regex', 5),
        array('password', 'require', '密码不能为空！', 1, 'regex', 5),
        array('password', '6,20', '密码长度有误', 1, 'length', 5),

    );

    // 判断用户是否存在
    public function checkUserExist($info){
        $where['mobile'] = $info['mobile'];
        $where['status'] = 1;
        $where['user_type'] =$info['user_type'];
        $count = $this->where($where)->count();
        if ($count > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 获取会员列表或获取指定查询的会员信息
     * @param  String $mobile [查询时所使用的手机号]
     * @return array
     */
    public function getUsersList($where, $field = null, $sort = 'register_time desc'){
        if(is_null($field)){
            $field = $this->selectFields;
        }
        $where['status'] = array('eq', 1);

        $count = $this->where($where)->count();
        $usersData = get_page($count, 15);
        $userslist = $this->field($field)->where($where)->limit($usersData['limit'])->order($sort)->select();
        foreach($userslist as &$val){
            if(($val['weixin'] || $val['qq']) && !$val['mobile']) $val['mobile'] = '三方登录未绑定';
        }
        return array(
            'userslist'=>$userslist,
            'page'=>$usersData['page']
        );
    }

    /**
     * @desc 用户端登录
     * @param $user_name string 登录账号
     * @param $pwd string 登录密码
     * @param string $field 返回字段
     * @param $user_type int 用户类型 0、普通会员 1、HR
     * @return array
     */
    public function doLogin($user_name, $pwd, $field = '', $user_type){
        if(!$field) $field = 'user_id,user_name,password,pay_password,mobile,email,head_pic,nickname,sex,user_money,frozen_money,disabled,register_time,recommended_number,recruit_number,is_auth,user_type';
        $where = array('user_name' => $user_name, 'status' => 1, 'user_type' => $user_type);
        $info = $this->where($where)->field($field)->find();
        if($info){
            if(!$info['disabled']) return V(0, '用户已经被禁用');
            if(pwdHash($pwd, $info['password'], true) != true) return V(0, '密码输入不正确');
            $unArr = array('disabled', 'password');
            $info['pay_password'] = $info['pay_password'] ? 1 : 0;
            $info['register_time'] = time_format($info['register_time']);
            foreach($unArr as &$val) unset($info[$val]); unset($val);
            $info['head_pic'] = strval($info['head_pic']);
            $info['nickname'] = strval($info['nickname']);
            $info['token'] = $this->updateUserToken($info['user_id']);
            return V(1, '用户登录成功', $info);
        }
        else{
            return V(0, '账号不存在！');
        }
    }

    /**
     * @desc 修改用户可提现金额
     * @param $user_id int 用户id
     * @param $type int 操作类型1、完成任务
     * @param $item_id int 对应的类型id
     * @return bool
     */
    public function changeUserWithdrawAbleAmount($user_id, $type, $item_id){
        if(!$user_id || !$type || !$item_id) return false;
        $res = false;
        if($type == 1){
            $where['user_id'] = $user_id;
            $task_info = D('Admin/Task')->getTaskInfo(array('id' => $item_id));
            $user_info = $this->getUserInfo($where, 'user_money, withdrawable_amount');
            $data['user_money'] = $user_info['user_money'] + $task_info['reward'];
            $data['withdrawable_amount'] = $user_info['withdrawable_amount'] + $task_info['reward'];
            $res = $this->where($where)->save($data);
        }
        return $res;
    }

    /**
     * @desc 用户登录/注册token变动信息
     * @param $userId
     * @return string
     */
    public function updateUserToken($userId){
        $model = M('UserToken');
        $where = array('user_id' => $userId);
        $info = $model->where($where)->find();
        if($info) return $this->changeToken($userId);
        $userInfo = $this->getUserInfo($where);
        $token = randNumber(18);
        $data = array(
            'user_id' => $userId,
            'user_name' => $userInfo['user_name'],
            'token' => $token,
            'login_time' => NOW_TIME,
        );
        $model->add($data);
        return $token;
    }

    /**
     * 找回密码时修改密码
     * @param $mobile
     * @param $password
     * @param $user_type
     * @return bool
     */
    public function change_pwd($mobile, $password, $user_type){
        $where['mobile'] = $mobile;
        $where['user_type'] = $user_type;
        $data['password'] = $password;
        $this->where($where)->data($data)->save();
        return true;
    }

    /**
     * 修改用户启用禁用状态
     * @param $user_id int 用户id
     * @return array
     */
    public function changeDisabled($user_id){

        $userInfo = $this->where(array('user_id'=>$user_id))->field('disabled, user_id')->find();
        $dataInfo = $userInfo['disabled'] == 1 ? 0 : 1;
        $update_info = $this->where(array('user_id'=>$user_id))->setField('disabled', $dataInfo);
        if($update_info !== false){
            //改变用户token
            $this->changeToken($userInfo['user_id']);
            return V(1, '修改成功！');
        }else{
            return V(0, '修改失败！');
        }
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['password'] = pwdHash($data['password']);
        $data['register_time'] = NOW_TIME;
        if(!$data['user_name']) $data['user_name'] = $data['mobile'];
    }

    //会员添加后生成token
    protected function _after_insert($data, $option){
        $user_id = $this->getLastInsID();
        $where['user_id'] = $user_id;
        $info = $this->where($where)->find();
        $token = randNumber(18); // 18位纯数字
        $user_name = $info['user_name'];
        $data['user_id'] = $user_id;
        $data['user_name'] = $user_name;
        $data['token'] = $token;
        M('user_token')->add($data);
    }


    //修改操作前的钩子操作
    protected function _before_update(&$data, $option){
        // 判断密码为空就不修改这个字段
        if(empty($data['password']))
            unset($data['password']);
        else 
            $data['password'] = pwdHash($data['password']);
        $data['last_login_ip'] = get_client_ip();
        $data['last_login_time'] = NOW_TIME;
    }

    /**
     * @desc 获取用户的指定字段
     * @param $where
     * @param $field
     * @return mixed
     */
    public function getUserField($where, $field){
        $info = $this->where($where)->getField($field);
        return $info;
    }

    //修改操作前的钩子操作
    protected function _after_update(&$data, $option){
        $user_id = I('user_id', 0);
        if ($data['disabled'] == 0) {
            $this->changeToken($user_id);
        }
    }

    /**
     * 查询用户信息
     * @param $where
     * @param null $fields
     * @return mixed
     */
    public function getUserInfo($where, $fields = null){
        if(is_null($fields)){
            $fields = $this->selectFields;
        }
        $userInfo = $this->field($fields)->where($where)->find();
        return $userInfo;
    }

    /**
     * @desc 修改用户信息
     * @param $where
     * @param array $saveData
     * @return bool
     */
    public function saveUserData($where, $saveData = array()){
        if(!is_array($saveData)) return false;
        $result = $this->where($where)->data($saveData)->save();
        return $result;
    }

    /**
     * 查询用户信息
     * @param $user_id
     *@return mixed
     */
    public function getUserName($user_id){
        $userInfo = $this->where('user_id ='.$user_id)->getField('user_name');
        return $userInfo;
    }
    //禁用账号改变会员token下线
    public function changeToken($user_id) {
        $token = randNumber(18); // 18位纯数字
        $where['user_id'] = $user_id;
        $data['token'] = $token;
        M('user_token')->where($where)->data($data)->save();
        return $token;
    }

    /**
     * 发布悬赏资金变动
     * @param array $where
     * @param string $money 变动金额（分）
     */
    public function recruitUserMoney($money) {
        $userModel = M('User');
        $balance = $userModel->where(array('user_id'=>UID))->field('user_id,user_money,frozen_money')->find();
        $user_money = $balance['user_money'] - $money;
        if ($user_money < 0) {
            return V(0, '可用余额不足');
        }
        M()->startTrans();
        $data['user_money'] = $user_money;
        $data['frozen_money'] = $balance['frozen_money'] + $money;
        if($userModel->where(array('user_id'=>UID))->save($data) ===false) {
            M()->rollback();
            return V(0, '资金变动失败');
        }
        $res = account_log(UID, $money, 4, '发布悬赏冻结', '');
        if ($res ===false) {
            M()->rollback();
            return V(0 ,'资金变动记录保存失败');
        }
        M()->commit();
        return V(1, '操作成功');
    }

    /**
     * 查看简历和入职资金变动
     * @param $recruit_resume_id 悬赏简历表信息id
     * @param $type 1 查看简历 2 入职
     */
    public function changeUserMoney($recruit_resume_id,$type = 1) {
        //悬赏简历表信息
        $info = M('RecruitResume')->where(array('id'=>$recruit_resume_id))->find();
        //获取分配比例
        if ($type == 1) {
            $field = 'get_resume_token';
        } else {
            $field = 'entry_token';
        }
        $recruitModel = M('Recruit');
        $userModel = M('User');
        //悬赏金额
        $trans = M();
        $moneyInfo = $recruitModel->where(array('id'=>$info['recruit_id']))->getField($field);
        $resumeRes = $recruitModel->where(array('id'=>$info['recruit_id']))->setDec('last_token', $moneyInfo);
        if ($resumeRes ===false) {
            $trans->rollback();
            return V(0, '悬赏信息扣除令牌失败');
        }
        $useRes = $userModel->where(array('user_id'=>$info['recruit_hr_uid']))->setDec('frozen_money', $moneyInfo);
        if ($useRes ===false) {
            $trans->rollback();
            return V(0, '用户扣除冻结令牌失败');
        }
        //插入待结算表
        $logRes = $this->addTokenLog($info['hr_user_id'], $moneyInfo, $type, $info['recruit_id']);
        if ($logRes ===false) {
            $trans->rollback();
            return V(0, '插入待结算表失败');
        }
        $trans->commit();
        return V(1 , '操作成功');
    }

    /**
     *  插入tokenLog表
     */
    public function addTokenLog($user_id,$token_num,$type,$recruit_id) {
        $tokenLogModel = D('Admin/TokenLog');
        $data['user_id'] = $user_id;
        $data['token_num'] = $token_num;
        $data['type'] = $type;
        $data['recruit_id'] = $recruit_id;
        if($tokenLogModel->create($data) !==false) {
            $id = $tokenLogModel->add();
            if ($id ===false) {
                return V(0 ,'添加失败');
            } else {
                return V(1 ,'添加成功');
            }
        } else {
            return V(0, $tokenLogModel->getError());
        }

    }

}
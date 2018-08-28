<?php
/**
 *会员用户属性模型类
 */
namespace Admin\Model;
use Think\Model;
use Common\Tools\Emchat;
class UserModel extends Model{
    protected $insertFields = array('user_name','nickname','password','mobile','email', 'register_time', 'sex', 'status', 'rank_id', 'user_type');
    protected $updateFields = array('user_id','user_name','nickname','password','mobile','email', 'sex', 'status', 'rank_id', 'disabled', 'user_type');
    protected $selectFields = array('user_id, user_name, nickname, password, mobile, email, sex, status, points,user_money,frozen_money,disabled,register_time,user_type,invitation_code,invitation_uid','weixin','qq');
    protected $_validate = array(
        array('mobile', 'require', '会员手机/账号不能为空！', 1, 'regex', 3),
        array('mobile','/^1[3|4|5|7|8][0-9]\d{8}$/','不是有效的手机号码',1,'regex', 3),
        //array('mobile','','会员手机/账号已经存在！',0,'unique',1),
        array('mobile,user_type', 'checkUserExist', '此账号已存在', self::MUST_VALIDATE, 'callback', 1),
        array('password', 'require', '密码不能为空！', 1, 'regex', 1),
    	array('password', '6,20', '密码长度有误', 1, 'length', 1),
    	array('password', '6,20', '密码长度有误', 2, 'length', 12),

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
     * 获取有推广的会员列表
     */
    public function getuserCashbackByPage($where, $field = false, $order = 'user_id desc'){
        if(!$field) {
            $field = 'user_id, user_name, mobile, user_money, register_time, invitation_code';
        }
        $number = $this->where($where)->count();
        $page = get_page($number, 15);
        $list = $this->where($where)->field($field)->limit($page['limit'])->order($order)->select();

        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * 修改用户启用禁用状态
     * @param $shop_id
     * @param $is_admin
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
     * 查询用户信息
     * @param $user_id
     *@return mixed
     * create by wangwujiang 2018/3/22
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

    public function getUserAlipayList($where, $field = false){
        if(!$field) $field = 'alipay_name,alipay_num,default,id';
        $number = M('UserAlipay')->where($where)->count();
        $page = get_web_page($number);
        $list = M('UserAlipay')->where($where)->limit($page['limit'])->field($field)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }


}
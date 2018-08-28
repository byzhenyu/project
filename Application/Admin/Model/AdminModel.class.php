<?php
/**
 * 权限管理之管理员模型类
 */
namespace Admin\Model;
use Think\Model;
use Common\Tools\Emchat;
class AdminModel extends Model{       
    //进行表单的合法性检测
    protected $insertFields = array('admin_name','password','cpassword','email','disabled');
    protected $updateFields = array('admin_id','admin_name','password','cpassword','email','disabled');

    //登录时表单验证的规则 
    public $_login_validate = array(
        array('admin_name', 'require', '用户名称不能为空!'),
        array('password', 'require', '登陆密码不能为空！'),
//        array('chkcode', 'require', '验证码不能为空！', 1),
//    	array('chkcode', '4', '验证码长度必须为4位', 1, 'length'),
//        array('chkcode', 'chk_chkcode', '验证码不正确！', 1, 'function'),
    );

    //添加修改管理员时用
    protected $_validate = array(
       array('admin_name', 'require', '账号名不能为空！', 1, 'regex', 3),
       array('admin_name', '1,30', '账号的值最长不能超过 30 个字符！', 1, 'length', 3),
       // 下面的规则只有添加时生效，修改时不生效，第六个参数代表什么时候验证：1：添加时验证 2：修改时 3：所有情况都验证
       array('password', 'require', '密码不能为空！', 1, 'regex', 1),
       array('cpassword', 'password', '两次密码输入不一致！', 1, 'confirm', 3),
       array('disabled', 'number', '必须是一个整数！', 2, 'regex', 3),
       array('admin_name', '', '账号已经存在！', 1, 'unique', 3),
    );



    //执行用户登陆验证操作
    public function doLogin(){
        // 获取表单中的用户名密码
        $admin_name = $this->admin_name;
        $password = $this->password;
        $verify = I('verify');
        if(!check_verify($verify)){
            $this->error = '验证码输入错误';
            return false;
        }
        $admin = $this->where(array('admin_name' =>$admin_name))->find();
        if(is_array($admin) && !empty($admin)) {
            // 判断是否启用(超级管理员不能禁用）
            if($admin['is_admin'] == 1 || !$admin['disabled'] == 0){
                if(pwdHash($password,$admin['password'],true)){
                    // 把ID和用户名存到session中
                    session('admin_id', $admin['admin_id']);
                    session('admin_info', $admin);
                    session('admin_name', $admin['admin_name']);
                    return true;
                }else{
                    $this->error = '密码不正确！';
                    return false;
                }
            }else{
                $this->error = '管理帐号被禁用！';
                return false;
            }
        }else{
            $this->error = '管理帐号不存在！';
            return false;
        }
    }
    
    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){ 
        $data['password'] = pwdHash($data['password']);
        $data['addtime'] = NOW_TIME;
        $data['last_login_ip'] = get_client_ip();
        $data['login_count'] = 1;
    }

    //添加操作后的钩子操作
    protected function _after_insert($data, $option){
        $roleId = I('post.role_id');
        if($roleId){
            $arModel = M('AdminRole');
                foreach($roleId as $v){
                    $arModel->add(array('admin_id' => $data['admin_id'],'role_id' => $v));
                }
        }
    }
    
    //修改操作前的钩子操作
    protected function _before_update(&$data, $option){
        //如果是超级管理员必须是启用的
        if(($option['where']['admin_id'] == 1) && $data['status'])
            $data['status'] = 0;         // 直接设置为启用状态
        $modify_self = I('post.modify_self','0','intval');

        //通过后台导航修改个人密码时不作下面的操作,而当超级管理员修改-->管理帐号列表里的数据时，需执行下面的方法
        if($modify_self != 1){
            $roleId = I('post.role_ids');
            $adminModel = M('AdminRole');
            $where = array('admin_id'=>array('eq', $option['where']['admin_id']));
            $hasRole = $adminModel->where($where)->count();
            //ln_role表有该管理员的角色数据,则进行全部删除操作
            if($hasRole){
                $adminModel->where($where)->delete();
            }
            //编辑前如果有角色的ID数据时,进行插入操作
            if($roleId){
                foreach ($roleId as $v) {
                    $adminModel->add(array(
                        'admin_id' => $option['where']['admin_id'],
                        'role_id' => $v
                    ));
                }
            }
        }

        // 判断密码为空就不修改这个字段
        if(empty($data['password']))
            unset($data['password']);
        else 
            $data['password'] = pwdHash($data['password']);
    }
    
    //删除操作前的钩子操作
    protected function _before_delete($option){
        if($option['where']['admin_id'] == 1){
                $this->error = '超级管理员不能被删除！';
                return false;
        }
        // 在删除admin表中管理员的信息之前先删除admin_role表中这个管理员对应的数据
        $arModel = M('AdminRole');
        $arModel->where(array('admin_id'=>array('eq', $option['where']['admin_id'])))->delete();
    }

    //根据管理帐号的admin_id取出该管理帐号信息
    public function getAdminInfo($admin_id){
        $field = 'admin_id,admin_name,email,disabled';
        $adminInfo = $this->where(array('admin_id'=>$admin_id))->field($field)->find($admin_id);
        if(is_array($adminInfo) && !empty($adminInfo)){
            return $adminInfo;
        }else{
            return NULL;
        }
    }
	
}
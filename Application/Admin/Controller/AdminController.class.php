<?php
/**
 * 权限管理之管理员信息操作类 
 */
namespace Admin\Controller;
use Think\Controller;
class AdminController extends CommonController {
    //管理员列表显示
    public function listAdmin(){
        $keyword = I('keyword', '');
        $where = null;
        if ($keyword != '') {
            $where['admin_name|email'] = array('like', '%'. $keyword .'%');
        }
        $adminlist = M('Admin')->where($where)->order('admin_id desc')->select();

        $this->assign('adminlist',$adminlist);
        $this->display();
    }

    //修改管理帐号
    public function editAdmin(){
        $admin_id = I('admin_id', 0, 'intval');

        $adminModel = D('Admin/Admin');
        if(IS_POST){
            if ($admin_id > 0){
                if($adminModel->create(I('post.'), 2)){
                    if ($adminModel->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($adminModel->create(I('post.'), 1)){
                    if ($adminModel->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $adminModel->getError()));
        }

        $adminInfo = $adminModel->getAdminInfo($admin_id);
        //取出所有的角色
        $roleData = M('Role')->select();
        // 取出当前管理员所在的角色的ID
        $arModel = M('AdminRole');
        $rids = $arModel->field('GROUP_CONCAT(role_id) role_id')->where(array('admin_id'=>array('eq', $admin_id)))->find();
        $this->assign('roleData', $roleData);
        $this->assign('rids', $rids['role_id']);
        $this->assign('adminInfo', $adminInfo);
        $this->display();
    }
        
    //删除管理帐号    
    public function del(){
        $admin_id = I('id', 0);
        if ($admin_id != 0) {
            $ids = explode(',', $admin_id);
            $count = count($ids);
            for ($i=0; $i<$count; $i++){
                if ($ids[$i] == 1) {
                    $this->ajaxReturn(V(0, '超级管理员不可以删除, 已终止!'));
                    die;
                }
            }
        }
        $this->_del('admin', 'admin_id');
    }
  
    //AJAX修改用户状态
    public function ajaxUpdateStatus(){

    }
}
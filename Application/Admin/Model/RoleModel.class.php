<?php
/**
 *权限管理之角色模型类
 */
namespace Admin\Model;
use Think\Model;
class RoleModel extends Model {
    protected $insertFields = array('role_name');
    protected $updateFields = array('id','role_name');
    protected $_validate = array(
            array('role_name', 'require', '角色名称不能为空！', 1, 'regex', 3),
            array('role_name', '1,30', '角色名称的值最长不能超过 30 个字符!', 1, 'length', 3),
    );

   /**
    * 获取角色信息
    * @param Int $pageSize 每页显示的数量(分页)
    * @return Array
    */
    public function getRoleInfo(){
        $where = array();
        if($role_name = I('get.role_name'))
                $where['role_name'] = array('like', "%$role_name%");

        $data['role'] = $this
            ->field('a.*,GROUP_CONCAT(distinct c.pri_name) pri_name,GROUP_CONCAT(d.admin_id) admin_id')
            ->alias('a')
            ->join('LEFT JOIN ln_role_privilege b ON a.id=b.role_id LEFT JOIN ln_privilege c ON b.pri_id=c.id')
            ->join('LEFT JOIN ln_admin_role d on d.role_id = a.id')
            ->where($where)
            ->group('a.id')
            ->select();

        foreach($data['role'] as $k => $v){
            if (!empty($v['admin_id'])) {
                if($v['admin_id']){
                    $where = array('admin_id'=>array('in',$v['admin_id']));
                }else{
                    $where = array();
                }
                $names = M('Admin')
                    ->field('GROUP_CONCAT(admin_name) admin_name')
                    ->where($where)
                    ->select();
                if(!$names)  continue;
                $data['role'][$k]['admin_name'] = $names[0]['admin_name'];
            }
        }
        return $data;
    }
     //添加角色后执行的钩子操作
    protected function _after_insert($data, $option){
        $priId = I('post.pri_id');
        if($priId){
            $rpModel = M('RolePrivilege');
            $priIds=explode(',',$priId);
            foreach ($priIds as $k => $v){
                $rpModel->add(array(
                    'pri_id' => $v,
                    'role_id' => $data['id'],
                ));
            }
        }
    }
        
    //修改角色前执行的钩子操作
    protected function _before_update(&$data, $option){
        // 先清除原来的权限
        $rpModel = M('RolePrivilege');
        $rpModel->where(array('role_id'=>array('eq', $option['where']['id'])))->delete();
        // 接收表单重新添加一遍
        $priId = I('post.pri_id', 0);
        if($priId){
            $priIds=explode(',',$priId);
            foreach ($priIds as $k => $v){
            	$rpModel->add(array(
            			'pri_id' => $v,
            			'role_id' => $option['where']['id']
            	));
            }
        }
    }
    
    //删除角色前执行的钩子操作
    protected function _before_delete($option){
        //验证是否有管理帐号属于该角色
        $arModel = M('AdminRole');
        $has = $arModel->where(array('role_id'=>array('eq', $option['where']['id'])))->count();
        if($has > 0){
            $this->error = '有管理员属于这个角色，无法删除！';
            return false;
        }
        //删除该角色所拥有的全部权限
        $rpModel = M('RolePrivilege');
        $rpModel->where(array('role_id'=>array('eq', $option['where']['id'])))->delete();
    }
}
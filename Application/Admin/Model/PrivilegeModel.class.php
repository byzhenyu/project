<?php
/**
 * 权限管理之权限管理类
 */
namespace Admin\Model;
use Think\Model;
class PrivilegeModel extends Model {
    protected $insertFields = array('pri_name','module_name','controller_name','action_name','params','parent_id','sort','icon');
    protected $updateFields = array('id','pri_name','module_name','controller_name','action_name','params','parent_id','sort','icon');
    protected $_validate = array(
            array('pri_name', 'require', '权限名称不能为空！', 1, 'regex', 3),
            array('pri_name', '1,30', '权限名称的值最长不能超过 30 个字符！', 1, 'length', 3),
            array('module_name', 'require', '模块名称不能为空！', 1, 'regex', 3),
            array('module_name', '1,20', '模块名称的值最长不能超过 20 个字符！', 1, 'length', 3),
    		array('sort', 'number', '排序必须是一个整数！', 2, 'regex', 3),
//            array('controller_name', 'require', '控制器名称不能为空！', 1, 'regex', 3),
//            array('controller_name', '1,20', '控制器名称的值最长不能超过 20 个字符！', 1, 'length', 3),
//            array('action_name', 'require', '方法名称不能为空！', 1, 'regex', 3),
//            array('action_name', '1,20', '方法名称的值最长不能超过 20 个字符！', 1, 'length', 3),
            array('parent_id', 'number', '上级权限的ID，0：代表顶级权限必须是一个整数！', 2, 'regex', 3),
    );

    //提取全部权限并作排序
    public function getTree(){
        $data = $this->order('sort asc')->select();
        return $this->_reSort($data);
    }

    /**
     * 对数据进行重组
     * @staticvar Array $ret
     * @param Array $data 需要处理的数组 
     * @param Int $parent_id 默认的父级ID
     * @param Int $level  用于显示等级的数字 
     * @param Boolean $isClear 清除static声明变量的数据
     * @return type
     */
    private function _reSort($data, $parent_id=0, $level=0, $isClear=true){
        static $ret = array();
        if($isClear)
            $ret = array();
        foreach ($data as $k => $v){
            if($v['parent_id'] == $parent_id){
                    $v['level'] = $level;
                    $ret[] = $v;
                    $this->_reSort($data, $v['id'], $level+1, false);
            }
        }
        return $ret;
    }

    /**
     * 查询权限的子数据,用于在删除数据时,获取该类下的子类ID集合
     * @param int $id
     * @return Array
     */
    public function getChildren($id){
            $data = $this->select();
            return $this->_children($data, $id);
    }
    
    /**
     * 获取某个权限的子类所有权限
     * @param Array $data
     * @param Int $parent_id
     * @param Boolean $isClear
     * @return Array
     */
    private function _children($data, $parent_id=0, $isClear=true){
        static $ret = array();
        if($isClear)
            $ret = array();
        foreach ($data as $k => $v){
            if($v['parent_id'] == $parent_id){
                $ret[] = $v['id'];
                $this->_children($data, $v['id'], false);
            }
        }
        return $ret;
    }
    
    //删除权限前的钩子操作
    public function _before_delete($option){
        $children = $this->getChildren($option['where']['id']);
        // 如有子分类,则全部删除
        if($children){
            $children = implode(',', $children);
            $where['id'] = array('in',$children);
            $this->where($where)->delete();
        }
    }

    /*
     * 查询用户所操作的地址(模块/控制器/方法)是否拥有操作权限
     * @param $where Array 查询条件 
     */
    public function hasPrivilege($where){
        $privilegeModel = M('Privilege');
        if($where['admin_id'] == 1){
            unset($where['admin_id']);
            $has = $privilegeModel->where($where)->count();
//             echo $privilegeModel->_sql();die;
        }else{
            $has = $privilegeModel
                    ->where($where)
                    ->join('ln_role_privilege a on a.pri_id = ln_privilege.id')
                    ->join('ln_admin_role b on b.role_id = a.role_id')
                    ->where($where)
                    ->count("a.role_id");
        }
        return $has;
    }
    /**
     * 获取管理帐号的权限菜单列表
     * @param Int $admin_id
     * @return Array
     */
    public function getMenu($admin_id){
        $privilegeModel = M('Privilege');
        if($admin_id == 1){
            $menuInfos = $privilegeModel->order('sort asc')->select(); 
			 
        }else 
            $menuInfos = $privilegeModel
                        ->join('ln_role_privilege a on a.pri_id = ln_privilege.id')
                        ->join('ln_admin_role b on b.role_id = a.role_id')
                         ->where(array('admin_id'=>$admin_id))
                         ->select();
        $menu = array();  
        foreach ($menuInfos as $k => $v){
            //查询顶级分类
            if($v['parent_id'] == 0){
                //循环取出顶级分类下的二级子分类
                foreach ($menuInfos as $k1 => $v1){
                    if($v1['parent_id'] == $v['id']){
                    	if($v1['params']){
                    		$v1['url'] = U($v1['module_name'].'/'.$v1['controller_name'].'/'.$v1['action_name'].$v1['params']);
                    	}else{
                    		$v1['url'] = U($v1['module_name'].'/'.$v1['controller_name'].'/'.$v1['action_name']);
                    	}
                       
                        $v['children'][] = $v1;
                    }
                }
                $menu[] = $v;
            }
        }
        return $menu;
    }
    protected function  _before_insert(&$data,$options){
        $data['icon']=I('icon','','htmlspecialchars');
    }

    protected function _before_update(&$data, $options) {
        $ori_pri_id = I('post.parent_id',0,'intval');
        $children = $this->getChildren($ori_pri_id);
        $parent_id = I('post.parent_id',0,'intval');
        if (in_array($parent_id, $children)){
            $this->error = '所选择的上级分类不能是分类的下级分类!';
            return false;
        }
        $data['icon']=I('icon','','htmlspecialchars');

    }
    
    //提取商品ID以应的家谱树
    public function getParentCat($id){
    	$priList = $this->field('id,pri_name,parent_id')->select();
    	return $this->getFamilyTree($priList,$id);
    }
    
    
    public function getFamilyTree($data,$id){
    	static $tree = array();
    	foreach($data as $v) {
    		if($v['id'] == $id) {
    			if($v['parent_id'] > 0) {
    				$this->getFamilyTree($data,$v['parent_id']);
    			}
    			$tree[] = $v;
    		}
    	}
    	return $tree;
    }
    
    
}
<?php
/**
 * 行业信息
 */
namespace Admin\Model;
use Think\Model;
class IndustryModel extends Model {
    protected $insertFields = array('industry_name','parent_id');
    protected $updateFields = array('industry_name','parent_id');
    protected $_validate = array(
            array('industry_name', 'require', '行业名称不能为空！', 1, 'regex', 3),
            array('industry_name', '1,30', '行业名称的值最长不能超过 30 个字符！', 1, 'length', 3),
            array('parent_id', 'number', '上级权限的ID，0：代表顶级权限必须是一个整数！', 2, 'regex', 3),
    );

    /**
     * @desc 获取行业信息列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return mixed
     */
    public function getIndustryList($where, $field = false, $order = 'sort asc'){
        if(!$field) $field = '';
        $list = $this->where($where)->field($field)->order($order)->select();
        return $list;
    }

    /**
     * @desc 获取行业信息
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getIndustryInfo($where, $field = false){
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

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
     * @return mixed
     */
    private function _reSort($data, $parent_id=0, $level=0, $isClear=true){
        static $ret = array();
        if($isClear)
            $ret = array();
        foreach ($data as $k => $v){
            if($v['parent_id'] == $parent_id){
                    $v['level'] = 1;
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
     * @return mixed
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

    protected function  _before_insert(&$data,$options){
        $parentId = I('post.parent_id');
        if($parentId > 0){
            $info = $this->find($parentId);
            if($info['parent_id'] > 0){
                $this->error = '二级行业信息下不能再添加行业信息！';
                return false;
            }
        }
    }

    protected function _before_update(&$data, $options) {
        $parentId = I('post.parent_id');
        if($parentId > 0){
            $info = $this->find($parentId);
            if($info['parent_id'] > 0){
                $this->error = '二级行业信息下不能再添加行业信息！';
                return false;
            }
        }
    }
    
    //提取商品ID以应的家谱树
    public function getParentCat($id){
    	$priList = $this->field('id,industry_name,parent_id')->select();
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
<?php
/**
 * 职位模型类
 */
namespace Admin\Model;

use Think\Model;

class PositionModel extends Model {
    protected $insertFields = array('position_name', 'sort', 'industry_id', 'parent_id');
    protected $updateFields = array('position_name', 'sort', 'industry_id', 'id', 'parent_id');
    protected $_validate = array(
        array('position_name', 'require', '职位名称不能为空！', 1, 'regex', 3),
        array('sort', 'require', '排序不能为空！', 1, 'regex', 3),
        array('position_name', 'checkPositionLength', '职位名称不能超过30字！', 2, 'callback', 3),
        array('industry_id', 'require', '请选择行业！', 1, 'regex', 3)
    );

    /**
     * 验证职位名称长度
     */
    protected function checkPositionLength($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 30) {
            return false;
        }
        return true;
    }
    /**
     * 获取职位列表
     * @param $where array 条件
     * @param $field string|bool 字段
     * @param $isPage bool 是否返回分页数据
     * @param $order string 排序顺序
     * @return mixed
     */
    public function getPositionList($where, $field = false, $order = 'sort', $isPage = true){
        if(!$field) $field = '*';
        if(!$isPage) return $this->where($where)->field($field)->order($order)->select();
        $count = $this->where($where)->count();
        $page = get_web_page($count);
        $list = $this->where($where)->limit($page['limit'])->field($field)->order($order)->select();

        return array('info' => $list, 'page' => $page['page']);
    }

    /**
     * @desc 获取职位信息
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getPositionInfo($where, $field = false){
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    //提取职位列表并做排序
    public function getTree($where){
        $data = $this->order('sort asc')->where($where)->select();
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

    /**
     * @desc 行业下的职位列表
     * @param $where
     * @return array
     */
    public function getIndustryPositionList($where){
        $where['parent_id'] = 0;
        $list = $this->where($where)->select();
        $return = array();
        foreach($list as &$val){
            $return[] = array('id' => $val['id'], 'position_name' => $val['position_name']);
            $children = $this->where(array('industry_id' => $where['industry_id'], 'parent_id' => $val['id']))->field('id,position_name')->select();
            if(count($children) > 0){
                foreach($children as &$c){
                    $c['position_name'] = '&nbsp;&nbsp;&nbsp;&nbsp;├-'.$c['position_name'];
                    array_push($return, $c);
                }
                unset($c);
            }
        }
        unset($val);
        return $return;
    }

    protected function _before_insert(&$data, $option){
        $res = $this->getPositionInfo(array('industry_id' => $data['industry_id'], 'position_name' => $data['position_name'], 'parent_id' => $data['parent_id']));
        if($res){
            $this->error = '该职位信息已经存在！';
            return false;
        }
    }

    protected function _before_update(&$data, $option){
        $res = $this->getPositionInfo(array('industry_id' => $data['industry_id'], 'position_name' => $data['position_name'], 'parent_id' => $data['parent_id'], 'id' => array('neq', $data['id'])));
        if($res){
            $this->error = '该职位信息已经存在！';
            return false;
        }
    }

}
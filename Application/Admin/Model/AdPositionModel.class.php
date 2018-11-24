<?php
/**
 * 广告位模型类
 */
namespace Admin\Model;
use Think\Model;
class AdPositionModel extends Model {
    protected $insertFields = array('name','width','height','description','sort');
    protected $updateFields = array('position_id','name','width','height','description','sort');
    protected $selectFields = array('position_id','name','width','height','description','sort');

    protected $_validate = array(
            array('name', 'require', '广告位置名称不能为空！', 1, 'regex', 3),
            array('name', '1,30', '广告位置名称的长度最长不能超过 30 个字符！', 1, 'length', 3),
            //array('width', '1,1200', '广告位置宽度不能超过1200像素', 1, 'between', 3),
    );

    //提取全部文章分类数据并作排序
    public function getTree($where=array()){
            $data = $this->select();
            return $this->_reSort($data);
    }

    /**
     * 对文章分类数据进行重组
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
                $this->_reSort($data, $v['article_cat_id'], $level+1, false);
            }
        }
        return $ret;
    }
    
    /**
     * 获取某个分类的子类
     * @param Int $id
     * @return Array
     */
    public function getChildren($id){
        $data = $this->select();
        return $this->_children($data, $id);
    }
    
    /**
     * 递归提取某个分类的子类数组数据
     * @staticvar array $ret
     * @param Array $data
     * @param Int $cid
     * @param Boolean $isClear
     * @return Array
     */
    private function _children($data, $cid=0, $isClear=true){
        static $ret = array();
        if($isClear)
            $ret = array();
        foreach ($data as $k => $v){
            if($v['position_id'] == $cid){
                $ret[] = $v['position_id'];
                $this->_children($data, $v['position_id'], false);
            }
        }
        return $ret;
    }

    //获取广告位置列表
    public function getAdPositionlist($where, $fields = null, $order = 'position_id desc'){
        if(is_null($fields)){
            $fields = $this->selectFields;
        }

        $count = $this->where($where)->count();
        $page = get_page($count);

        $info = $this->field($fields)->where($where)->limit($page['limit'])->order($order)->select();

        return array(
            'info' => $info,
            'page' => $page['page']
        );
    }
}
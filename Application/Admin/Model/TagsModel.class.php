<?php
/**
 * 标签模型
 */
namespace Admin\Model;

use Think\Model;

class TagsModel extends Model {
    protected $insertFields = array('tags_name', 'tags_type', 'tags_sort');
    protected $updateFields = array('tags_name', 'tags_type', 'tags_sort', 'id');
    protected $_validate = array(
        array('tags_name', 'require', '标签名称不能为空！', 1, 'regex', 3),
        array('tags_type', 'require', '标签类型不能为空！', 1, 'regex', 3),
    );

    /**
     * @desc 获取标签列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return mixed
     */
    public function getTagsList($where, $field = false, $order = 'tags_sort asc'){
        if(!$field) $field = 'tags_name,id';
        $list = $this->where($where)->field($field)->order($order)->select();
        return $list;
    }

    /**
     * @desc 标签详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getTagsInfo($where, $field = false){
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }
}
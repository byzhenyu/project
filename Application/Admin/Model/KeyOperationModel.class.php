<?php
/**
 * 操作日志模型类
 */
namespace Admin\Model;
use Think\Model;
class KeyOperationModel extends Model{
    protected $insertFields = array('id', 'operate_time', 'operate_type', 'relation_id', 'user_id');
    protected $updateFields = array('id', 'operate_time', 'operate_type', 'relation_id', 'user_id');
    protected $_validate = array(
        array('operate_type', 'require', '操作类型不能为空！', 1, 'regex', 3),
    );

    /**
     * @desc 用户操作日志列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getKeyOperationList($where, $field = false, $order = 'k.operate_time desc'){
        if(!$field) $field = 'k.*,u.user_name,u.mobile,u.nickname';
        $number = $this->alias('k')->join('__USER__ as u on k.user_id = u.user_id')->where($where)->count();
        $page = get_web_page($number);
        $list = $this->alias('k')->join('__USER__ as u on k.user_id = u.user_id')->where($where)->limit($page['limit'])->order($order)->field($field)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    protected function _before_insert(&$data, $option){
        $data['operate_time'] = NOW_TIME;
    }
    protected function _before_update(&$data, $option){
        //不支持修改操作
        return false;
    }
}
<?php
/**
 * 问题|回答点赞/浏览模型
 */
namespace Admin\Model;

use Think\Model;

class QuestionPointsModel extends Model {
    protected $insertFields = array('item_id', 'user_id', 'operate_type', 'type');
    protected $updateFields = array('item_id', 'user_id', 'id', 'operate_type', 'type');
    protected $_validate = array(
        array('item_id', 'require', '关联不能为空！', 1, 'regex', 3),
        array('user_id', 'require', '用户id不能为空！', 1, 'regex', 3),
    );

    /**
     * @desc 是否点赞/浏览
     * @param $where
     * @return mixed
     */
    public function getQuestionPointsInfo($where){
        $res = $this->where($where)->find();
        return $res;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }
}
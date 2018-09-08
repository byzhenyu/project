<?php
/**
 * 问题/回答图片模型
 */
namespace Admin\Model;

use Think\Model;

class QuestionImgModel extends Model {
    protected $insertFields = array();
    protected $updateFields = array();
    protected $_validate = array(
    );

    /**
     * @desc 获取问题/回答图片列表
     * @param $where
     * @return mixed
     */
    public function getQuestionImgList($where){
        $res = $this->where($where)->select();
        return $res;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}
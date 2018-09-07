<?php
/**
 * 问题图片模型
 */
namespace Admin\Model;

use Think\Model;

class QuestionImgModel extends Model {
    protected $insertFields = array();
    protected $updateFields = array();
    protected $_validate = array(
    );


    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}
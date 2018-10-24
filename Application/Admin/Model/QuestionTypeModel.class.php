<?php
/**
 * 问题类型
 */
namespace Admin\Model;

use Think\Model;

class QuestionTypeModel extends Model {
    protected $insertFields = array('type_name', 'sort');
    protected $updateFields = array('type_name', 'sort', 'id');
    protected $_validate = array(
        array('type_name', 'require', '问题类型不能为空！', 1, 'regex', 3),
        array('type_name', 'checkTypeLength', '问题类型不能超过18字！', 2, 'callback', 3),
    );

    /**
     * 验证过滤词长度
     */
    protected function checkTypeLength($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 18) {
            return false;
        }
        return true;
    }
    /**
     * 获取问题类型名称
     * @param
     * @return mixed
     */
    public function getQuestionTypeList($where, $is_limit = false, $field = false){
        if($is_limit) return $this->where($where)->field($field)->order('sort desc')->select();
        $count = $this->where($where)->count();
        $page = get_web_page($count);
        $list = $this->where($where)->limit($page['limit'])->order('sort desc')->select();

        return array('info' => $list, 'page' => $page['page']);
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $type_name = I('type_name');
        if($this->where(array('type_name' => $type_name))->find()){
            $this->error = '问题类型名称已经存在！';
            return false;
        }
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
        $post = I('post.');
        if($this->where(array('type_name' => $post['type_name'], 'id' => array('neq', $post['id'])))->find()){
            $this->error = '问题类型名称已经存在！';
            return false;
        }
    }

}
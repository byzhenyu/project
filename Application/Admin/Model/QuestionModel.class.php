<?php
/**
 * 问题模型
 */
namespace Admin\Model;

use Think\Model;

class QuestionModel extends Model {
    protected $insertFields = array('question_title', 'question_type', 'question_content', 'add_time', 'user_id');
    protected $updateFields = array('question_title', 'question_type', 'question_content', 'user_id', 'browse_number', 'like_number', 'answer_number', 'disabled');
    protected $_validate = array(
        array('question_title', 'require', '问题标题不能为空！', 1, 'regex', 3),
        array('question_title', '10,40', '标题长度10-40字之间', 1, 'length', 3),
        array('question_type', 'require', '问题类型不能为空！', 1, 'regex', 3),
        array('question_content', 'require', '问题内容不能为空！', 1, 'regex', 3),
        array('question_content', '1,200', '问题内容不能超过200字', 1, 'length', 3)
    );

    /**
     * 获取问题列表
     * @param
     * @return mixed
     */
    public function getQuestionList($where, $field = false, $order = 'add_time desc'){
        if(!$field) $field = 'a.*,u.nickname';
        $count = $this->alias('a')->where($where)->join('__USER__ as u on u.user_id = a.user_id')->count();
        $page = get_web_page($count);
        $list = $this->alias('a')->where($where)->field($field)->limit($page['limit'])->join('__USER__ as u on u.user_id = a.user_id')->order($order)->select();

        return array('info' => $list, 'page' => $page['page']);
    }


    /**
     * 修改问题启用禁用状态
     * @return array
     */
    public function changeDisabled($question_id){
        $userInfo = $this->where(array('id' => $question_id))->field('disabled, id')->find();
        $dataInfo = $userInfo['disabled'] == 1 ? 0 : 1;
        $update_info = $this->where(array('id'=>$question_id))->setField('disabled', $dataInfo);
        if($update_info !== false){
            return V(1, '修改成功！');
        }else{
            return V(0, '修改失败！');
        }
    }

    /**
     * @desc 获取问题详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getQuestionDetail($where, $field = false){
        $info = $this->where($where)->field($field)->find();
        return $info;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}
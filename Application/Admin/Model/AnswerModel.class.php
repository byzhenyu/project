<?php
/**
 * 问题答案模型
 */
namespace Admin\Model;

use Think\Model;

class AnswerModel extends Model {
    protected $insertFields = array('question_id', 'user_id', 'answer_content', 'add_time', 'is_anonymous');
    protected $updateFields = array('');
    protected $_validate = array(
        array('question_id', 'require', '回答问题不能为空！', 1, 'regex', 3),
        array('answer_content', 'require', '答案内容不能为空！', 1, 'regex', 3),
    );

    /**
     * 获取答案列表
     * @param
     * @return mixed
     */
    public function getAnswerList($where, $field = false, $order = 'add_time desc'){
        if(!$field) $field = 'a.*,u.nickname';
        $count = $this->alias('a')->join('__USER__ as u on u.user_id = a.user_id')->where($where)->count();
        $page = get_web_page($count);
        $list = $this->alias('a')->where($where)->field($field)->limit($page['limit'])->join('__USER__ as u on u.user_id = a.user_id')->order($order)->select();

        return array('info' => $list, 'page' => $page['page']);
    }

    /**
     * @desc 获取答案详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getAnswerDetail($where, $field = false){
        $info = $this->where($where)->field($field)->find();
        if(!$info) return false;
        return $info;
    }

    /**
     * 修改答案启用禁用状态
     * @return array
     */
    public function changeDisabled($question_id){

        $userInfo = $this->where(array('id'=>$question_id))->field('disabled, id')->find();
        $dataInfo = $userInfo['disabled'] == 1 ? 0 : 1;
        $update_info = $this->where(array('id'=>$question_id))->setField('disabled', $dataInfo);
        if($update_info !== false){
            return V(1, '修改成功！');
        }else{
            return V(0, '修改失败！');
        }
    }



    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
        $question_id = $data['question_id'];
        $questionInfo = D('Question')->getQuestionDetail(array('id' => $question_id));
        if($questionInfo){
            $this->error = '问题不存在！';
            return false;
        }
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}
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
     * @desc 获取答案列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getAnswerList($where, $field = false, $order = 'add_time desc'){
        $where['a.disabled'] = 1;
        $where['q.disabled'] = 1;
        if(!$field) $field = 'a.*,u.nickname,u.head_pic';
        $count = $this->alias('a')->join('__USER__ as u on u.user_id = a.user_id')->join('__QUESTION__ as q on q.id = a.question_id')->where($where)->count();
        $page = get_web_page($count);
        $list = $this->alias('a')->where($where)->field($field)->limit($page['limit'])->join('__USER__ as u on u.user_id = a.user_id')->join('__QUESTION__ as q on q.id = a.question_id')->order($order)->select();
        foreach($list as &$val){
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d');
            $val['head_pic'] = $val['head_pic'] ? strval($val['head_pic']) : DEFAULT_IMG;
            if(!$val['is_anonymous']){
                $val['nickname'] = '匿名用户';
                $val['head_pic'] = DEFAULT_IMG;
            }
            $imgWhere = array('type' => 2, 'item_id' => $val['id']);
            $val['answer_img'] = D('Admin/QuestionImg')->getQuestionImgList($imgWhere);
        }
        unset($val);
        return array('info' => $list, 'page' => $page['page']);
    }

    /**
     * @desc 回答数量
     * @param $where
     * @return mixed
     */
    public function getAnswerNum($where){
        $res = $this->where($where)->count();
        return $res;
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
    public function changeDisabled($id){

        $userInfo = $this->where(array('id'=>$id))->field('disabled, id')->find();
        $dataInfo = $userInfo['disabled'] == 1 ? 0 : 1;
        $update_info = $this->where(array('id'=>$id))->setField('disabled', $dataInfo);
        if($update_info !== false){
            return V(1, '修改成功！');
        }else{
            return V(0, '修改失败！');
        }
    }

    /**
     * @desc 设置为最佳答案
     * @param $where
     * @return array
     */
    public function settingOptimum($where){
        $answer_id = $where['id'];
        $answerInfo = $this->getAnswerDetail($where);
        if(!$answerInfo) return V(0, '未找到对应的答案信息！');
        if($answerInfo['user_id'] == UID) return V(0, '不能采纳自己的回答为最佳答案！');
        unset($where['id']);
        $where['is_optimum'] = 1;
        $answerInfo = $this->getAnswerDetail($where);
        if($answerInfo) return V(0, '该问题已经有最佳答案！');
        unset($where);
        $where['id'] = $answer_id;
        $save = $this->where($where)->save(array('is_optimum' => 1));
        if(false !== $save){
            return V(1, '设置成功！');
        }
        else{
            return V(0, '设置失败！');
        }
    }

    /**
     * @desc 回答点赞/
     * @param $where
     * @param string $field
     * @return bool
     */
    public function setAnswerInc($where, $field = 'like_number'){
        $res = $this->where($where)->setInc($field);
        return $res;
    }



    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
        $answer_cmp = cmp_contraband($data['answer_content']);
        if($answer_cmp){
            $this->error = '回答内容中有违禁词！';
            return false;
        }
        $question_id = $data['question_id'];
        $questionInfo = D('Admin/Question')->getQuestionDetail(array('id' => $question_id));
        if(!$questionInfo){
            $this->error = '问题不存在！';
            return false;
        }
        /*if(!check_is_auth($data['user_id'])){
            $string = auth_string();
            $error = '请先通过实名认证！';
            if(false !== $string) $error = $string;
            $this->error = $error;
            return false;
        }*/
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}
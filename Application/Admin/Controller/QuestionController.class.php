<?php
/**
 * 问题类
 */
namespace Admin\Controller;
use Think\Controller;
class QuestionController extends CommonController {


    //显示问题列表
    public function listQuestion(){
        $keyword = I('keyword', '', 'trim');

        $model = D('Admin/Question');
        $where = array();
        //关键字查询
        if($keyword){
            $where['question_title'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getQuestionList($where);


        $this->keyword = $keyword;
        $this->assign('list', $list['info']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    /**
     * @desc 获取问题答案列表
     */
    public function listAnswer(){
        $id = I('id', 0, 'intval');
        $where = array('question_id' => $id);
        $keywords = I('keyword', '', 'trim');
        if($keywords) $where['answer_content'] = array('like', '%'.$keywords.'%');
        $answer = D('Admin/Answer');
        $list = $answer->getAnswerList($where);
        $this->keyword = $keywords;
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    /**
     * @desc 问题详情
     */
    public function seeQuestionDetail(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id);
        $questionWhere = array('question_id' => $id, 'is_optimum' => 1);
        $model = D('Admin/Question');
        $answerModel = D('Admin/Answer');
        $info = $model->getQuestionDetail($where);
        $info['nickname'] = D('Admin/User')->getUserField(array('user_id' => $info['user_id']), 'nickname');
        $answer = $answerModel->getAnswerDetail($questionWhere);
        if(!$answer){
            $optimum = 1;
        }
        else{
            $answer['nickname'] = D('Admin/User')->getUserField(array('user_id' => $answer['user_id']), 'nickname');
        }
        $this->answer = $answer;
        $this->optimum = $optimum;
        $this->info = $info;
        $this->display();
    }

    /**
     *用户管理启用，禁用方法
     */
    public function changeQuestionDisabled(){
        $question_id = I('question_id', 0, 'intval');
        $updateInfo = D('Question')->changeDisabled($question_id);
        $this->ajaxReturn($updateInfo);
    }

    /**
     *用户管理启用，禁用方法
     */
    public function changeAnswerDisabled(){
        $answer_id = I('answer_id', 0, 'intval');
        $updateInfo = D('Answer')->changeDisabled($answer_id);
        $this->ajaxReturn($updateInfo);
    }

    // 放入回收站
    public function del(){
        $this->_del('StaffBank');  //调用父类的方法
    }
}
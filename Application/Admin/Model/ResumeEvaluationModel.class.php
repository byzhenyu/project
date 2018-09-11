<?php
/**
 * 简历评价模型
 */
namespace Admin\Model;

use Think\Model;

class ResumeEvaluationModel extends Model {
    protected $insertFields = array('resume_id', 'user_id', 'skill_score', 'major_score', 'chat_score', 'appearance_score', 'innovate_score', 'analysis_score', 'add_time');
    protected $updateFields = array('resume_id', 'user_id', 'skill_score', 'major_score', 'chat_score', 'appearance_score', 'innovate_score', 'analysis_score', 'add_time', 'id');
    protected $_validate = array(
        array('resume_id', 'require', '简历id不能为空！', 1, 'regex', 3),
        array('skill_score', 'require', '技能得分不能为空！', 1, 'regex', 3),
        array('major_score', 'require', '专业得分不能为空！', 1, 'regex', 3),
        array('chat_score', 'require', '沟通得分不能为空！', 1, 'regex', 3),
        array('appearance_score', 'require', '仪容得分不能为空！', 1, 'regex', 3),
        array('innovate_score', 'require', '创新得分不能为空！', 1, 'regex', 3),
        array('analysis_score', 'require', '分析得分不能为空！', 1, 'regex', 3),
    );

    protected function _before_insert(&$data, $option){
        $valid_where = array('user_id' => $data['user_id'], 'resume_id' => $data['resume_id']);
        $res = $this->where($valid_where)->find();
        if($res){
            $this->error = '您已经评价过该简历！';
            return false;
        }
        $data['add_time'] = NOW_TIME;
    }
    protected function _before_update(&$data, $option){
    }

    /**
     * @desc 获取简历评价均值
     * @param $where
     */
    public function getResumeEvaluationAvg($where){
        $evaluation = $this->where($where)->field('skill_score,major_score,chat_score,appearance_score,innovate_score,analysis_score')->select();
        $fieldArray = array('skill_score', 'major_score', 'chat_score', 'appearance_score', 'innovate_score', 'analysis_score');
        $skill_score = $major_score = $chat_score = $appearance_score = $innovate_score = $analysis_score = 0;
        $skill_score_number = $major_score_number = $char_score_number = $appearance_score_number = $innovate_score_number = $analysis_score_number = 0;
        foreach($evaluation as &$val){
            foreach($fieldArray as &$value){
                $$value += $val[$$value];
                $temp_number_field = $value.'_number';
                $$temp_number_field ++;
            }
        }
        unset($val);
        $ret_arr = array();
        foreach($fieldArray as &$v){
            $t_field = $v.'_number';
            $t_number = $$t_field;
            $t_score = $$v;
            $avg_score = round($t_score / $t_number);
            $ret_arr[] = array($v => $avg_score);
        }
        unset($v);
        return $ret_arr;
    }

}
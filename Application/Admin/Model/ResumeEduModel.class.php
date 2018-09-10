<?php
/**
 * 教育经历模型
 */
namespace Admin\Model;

use Think\Model;

class ResumeEduModel extends Model {
    protected $insertFields = array('resume_id', 'school_name', 'degree', 'starttime', 'endtime', 'major', 'describe');
    protected $updateFields = array('resume_id', 'school_name', 'degree', 'starttime', 'endtime', 'major', 'describe', 'id');
    protected $_validate = array(
        array('school_name', 'require', '学校名称不能为空！', 1, 'regex', 3),
        array('degree', 'require', '学历不能为空！', 1, 'regex', 3),
        array('major', 'require', '专业不能为空！', 1, 'regex', 3),
        array('starttime', 'require', '请选择开始时间！', 1, 'regex', 3),
        array('endtime', 'require', '请选择结束时间!', 1, 'length', 3),
        array('describe', 'require', '请输入教育描述!', 1, 'length', 3)
    );

    /**
     * @desc 获取教育经历详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getResumeEduInfo($where, $field = false){
        if($field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    /**
     * @desc 删除教育经历
     * @param $where
     * @return mixed
     */
    public function deleteResumeEdu($where){
        $res = $this->where($where)->delete();
        return $res;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['starttime'] = strtotime($data['starttime']);
        $data['endtime'] = strtotime($data['endtime']);
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}
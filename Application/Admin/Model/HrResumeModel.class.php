<?php
/**
 * hr简历库模型
 */
namespace Admin\Model;

use Think\Model;

class HrResumeModel extends Model {
    protected $insertFields = array('hr_user_id', 'resume_id', 'recommend_label', 'add_time');
    protected $updateFields = array();
    protected $_validate = array(
        array('resume_id', 'require', '简历不能为空！', 1, 'regex', 3),
        array('recommend_label', 'require', '标签不能为空！', 1, 'regex', 3),
    );

    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
    }
}
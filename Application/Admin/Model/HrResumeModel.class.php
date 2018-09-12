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
    );

    /**
     * @desc 获取人才列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getHrResumeList($where, $field = false, $order = ''){
        if(!$field) $field = 'h.id,h.resume_id,r.true_name,r.head_pic,h.add_time,r.age,r.sex';
        $number = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->where($where)->count();
        $page = get_web_page($number);
        $list = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->field($field)->order($order)->limit($page['limit'])->where($where)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * @desc 获取hr简历库详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getHrResumeInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
    }
}
<?php
/**
 * 悬赏缓存模型类
 */
namespace Admin\Model;

use Think\Model;

class RecruitCacheModel extends Model {

    protected $_validate = array(
        array('hr_user_id', 'require', '发布人不能为空', 1, 'regex', 3),
        array('position_id', 'require', '请选择悬赏职位', 1, 'regex', 3),
        array('recruit_num', 'number', '请填写招聘人数', 1, 'regex', 3),
        array('nature', 'require', '岗位性质不能为空', 1, 'regex', 3),
        array('sex', array(0,1,2), '性别字段有误', 1, 'in', 3),
        array('degree', 'require', '请选择学历要求', 1, 'regex', 3),
        array('language_ability', '1,255', '请填写语言要求', 1, 'length', 3),
        array('experience', 'require', '请选择工作经验', 1, 'regex', 3),
        array('job_area', 'require', '请选择工作地区', 1, 'regex', 3),
        array('base_pay', 'require', '请填写基本工资', 1, 'regex', 3),
        array('merit_pay', 'require', '请填写绩效工资', 1, 'regex', 3),
        array('welfare', 'require', '请选择福利', 1, 'regex', 3),
    );

    public function getRecruitCacheInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }
    protected function _before_insert(&$data, $option) {
        $res = $this->getRecruitCacheInfo(array('hr_user_id' => $data['hr_user_id']));
        if($res){
            $this->where(array('id' => $res['id']))->delete();
        }
    }
}
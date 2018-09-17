<?php
/**
 * 学历模型
 */
namespace Admin\Model;

use Think\Model;

class EducationModel extends Model {
    protected $insertFields = array('education_name', 'sort');
    protected $updateFields = array('education_name', 'sort', 'id');
    protected $_validate = array(
        array('education_name', 'require', '学历名称不能为空！', 1, 'regex', 3),
        array('education_name', '1,50', '学历名称保持在1-50字！', 1, 'length', 3)
    );

    /**
     * @desc 获取学历信息列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return mixed
     */
    public function getEducationList($where, $field = false, $order = 'sort'){
        if(!$field) $field = '*';
        $list = $this->where($where)->field($field)->order($order)->select();
        return $list;
    }

    /**
     * @desc 获取学历信息详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getEducationInfo($where, $field = false){
        if(!$field) $field = '';
        $info = $this->where($where)->field($field)->find();
        return $info;
    }

    protected function _before_insert(&$data, $option){
        $education_name = $data['education_name'];
        $res = $this->getEducationInfo(array('education_name' => $education_name));
        if($res) {
            $this->error = '学历名称已经存在！';
            return false;
        }
    }
    protected function _before_update(&$data, $option){
        $education_name = $data['education_name'];
        $res = $this->getEducationInfo(array('education_name' => $education_name, 'id' => array('neq', $data['id'])));
        if($res){
            $this->error = '学历名称已经存在！';
            return false;
        }
    }

}
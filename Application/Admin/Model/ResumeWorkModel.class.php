<?php
/**
 * 工作经历模型
 */
namespace Admin\Model;

use Think\Model;

class ResumeWorkModel extends Model {
    protected $insertFields = array('resume_id', 'company_name', 'position', 'starttime', 'endtime', 'describe', 'work_mobile', 'work_hr_name');
    protected $updateFields = array('resume_id', 'company_name', 'position', 'starttime', 'endtime', 'describe', 'id');
    protected $_validate = array(
        array('company_name', 'require', '公司名称不能为空！', 1, 'regex', 3),
        array('position', 'require', '职位不能为空！', 1, 'regex', 3),
        array('starttime', 'require', '请选择开始时间！', 1, 'regex', 3),
        //array('endtime', 'require', '请选择结束时间!', 1, 'regex', 3),
        array('describe', 'require', '请输入经历描述!', 1, 'regex', 3)
    );

    /**
     * @desc 获取简历工作经历列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @param $limit
     * @return mixed
     */
    public function getResumeWorkList($where, $field = false, $order = 'endtime desc', $limit = 5){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->order($order)->limit($limit)->select();
        return $res;
    }

    /**
     * @desc 获取简历工作经历详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getResumeWorkInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    /**
     * @desc 删除工作经历
     * @param $where
     * @return mixed
     */
    public function deleteResumeWork($where){
        $res = $this->where($where)->delete();
        return $res;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $c_data = array('company_name' => $data['company_name']);
        D('Admin/Company')->add($c_data);
        $res = $this->getResumeWorkInfo(array('resume_id' => $data['resume_id'], 'company_name' => $data['company_name']));
        if($res){
            $this->error = '该公司已经有工作经历！';
            return false;
        }
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

    /**
     * @desc 工作经历修改。
     * @param $where
     * @param $data
     * @return bool
     */
    public function saveResumeWorkData($where, $data){
        if(!is_array($where) || !is_array($data)) return false;
        $res = $this->where($where)->save($data);
        return $res;
    }

}
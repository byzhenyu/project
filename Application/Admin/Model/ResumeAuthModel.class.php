<?php
/**
 * 简历认证表
 */
namespace Admin\Model;

use Think\Model;

class ResumeAuthModel extends Model {
    protected $insertFields = array('user_id', 'hr_id', 'hr_name', 'hr_mobile', 'resume_id', 'add_time');
    protected $updateFields = array('user_id', 'hr_id', 'hr_name', 'hr_mobile', 'resume_id', 'add_time', 'auth_result', 'auth_time');
    protected $_validate = array(
    );


    /**
     * @desc 获取简历认证列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getResumeAuthList($where, $field = false, $order = 'a.add_time desc'){
        if(!$field) $field = 'a.add_time,a.auth_result,r.head_pic,r.true_name,a.resume_id,a.id,r.age,r.sex,r.update_time';
        $number = $this->alias('a')->join('__RESUME__ as r on a.resume_id = r.id')->join('__USER__ as u on hr_id = u.user_id', 'LEFT')->where($where)->count();
        $page = get_web_page($number);
        $res = $this->alias('a')->join('__RESUME__ as r on a.resume_id = r.id')->join('__USER__ as u on hr_id = u.user_id', 'LEFT')->where($where)->field($field)->limit($page['limit'])->order($order)->select();
        foreach($res as &$val){
            $val['add_time'] = time_format($val['add_time']);
            $val['update_time'] = time_format($val['update_time']);
            $val['auth_result_string'] = show_resume_auth_result($val['auth_result']);
            if ($val['head_pic'] == '') {
                $val['head_pic'] = 'https://shanjian.oss-cn-hangzhou.aliyuncs.com/nopic.png';
            }
        }
        return array(
            'info' => $res,
            'page' => $page['page']
        );
    }

    /**
     * @desc 工作经历新增同步执行
     * @param $data
     * @return bool|mixed
     */
    public function changeResumeAuth($data){
        $authRes = $this->add($data);
        return $authRes;
    }

    /**
     * @desc 保存简历认证信息
     * @param $where
     * @param $data
     * @return bool
     */
    public function saveResumeAuthData($where, $data){
        if(!is_array($where) || !is_array($data)) return false;
        $res = $this->where($where)->save($data);
        return $res;
    }

    /**
     * @desc 简历认证详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getResumeAuthInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
        if(isMobile($data['mobile'])) return false;
        $hr_info = D('Admin/User')->getUserInfo(array('mobile' => $data['hr_mobile'], 'user_type' => 1));
        if($hr_info) $data['hr_id'] = $hr_info['user_id'];
        $res = $this->getResumeAuthInfo(array('user_id' => $data['user_od'], 'hr_id' => $data['hr_id']));
        if($res){
            return false;
        }
    }
    protected function _before_update(&$data, $option){
    }

    /**
     * @desc 简历认证数量
     * @param $where
     * @return mixed
     */
    public function getResumeAuthCount($where){
        $res = $this->where($where)->count();
        return $res;
    }

}
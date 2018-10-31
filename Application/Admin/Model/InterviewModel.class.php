<?php
/**
 * 面试管理模型
 */
namespace Admin\Model;

use Think\Model;

class InterviewModel extends Model {
    protected $insertFields = array('hr_user_id', 'resume_id', 'state', 'update_time', 'recruit_resume_id');
    protected $updateFields = array('hr_user_id', 'resume_id', 'state', 'update_time', 'id', 'recruit_resume_id');
    protected $_validate = array(
        array('resume_id', 'require', '简历不能为空！', 1, 'regex', 3),
        array('recruit_resume_id', 'require', '悬赏推荐id不能为空！', 1, 'regex', 3)
    );

    /**
     * @desc 获取面试管理表
     * @param $where array 检索条件
     * @param bool $field 需要字段
     * @param string $order 排序顺序
     * @return array
     */
    public function getInterviewList($where, $field = false, $order = ''){
        if(!$field) $field = 'i.resume_id,i.state,i.update_time,r.age,r.sex,r.true_name,r.head_pic,r.update_time as resume_time,i.id as interview_id,i.recruit_resume_id';
        $number = $this->alias('i')->join('__RESUME__ as r on i.resume_id = r.id')->where($where)->count();
        $page = get_web_page($number);
        $list = $this->alias('i')->join('__RESUME__ as r on i.resume_id = r.id')->field($field)->where($where)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * @desc 获取面试详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getInterviewInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    /**
     * @desc 改变面试状态
     * @param $where
     * @param $data
     * @return bool
     */
    public function saveInterviewData($where, $data){
        if(!is_array($where) || !is_array($data)) return false;
        $res = $this->where($where)->save($data);
        return $res;
    }

    protected function _before_insert(&$data, $option){
        $recruit_hr_uid = D('Admin/RecruitResume')->getRecruitResumeField(array('id' => $data['recruit_resume_id']), 'recruit_hr_uid');
        $recruit_hr_uid = $recruit_hr_uid['recruit_hr_uid'];
        if($recruit_hr_uid != $data['hr_user_id']){
            $this->error = '获取不到对应悬赏信息！';
            return false;
        }
        $interview_where = array('hr_user_id' => $data['hr_user_id'], 'resume_id' => $data['resume_id']);
        $res = $this->getInterviewInfo($interview_where);
        if($res){
            $this->error = '该悬赏已经发送过面试信息！';
            return false;
        }
        $data['update_time'] = NOW_TIME;
    }

    protected function _before_update(&$data, $option){
        $data['update_time'] = NOW_TIME;
    }

    /**
     * 获取面试状态
     * @param $where
     * @return mixed
     */
    public function getInterviewStatus($where) {
        $res = $this->where($where)->getField('state');

        if (!is_null($res)) {
            return $res;
        } else {
            return '3';
        }

    }

    /**
     * @desc 面试统计
     * @param $where
     * @return mixed
     */
    public function getInterviewCount($where){
        $number = $this->alias('i')->join('__HR_RESUME__ as r on i.resume_id = r.resume_id', 'LEFT')->where($where)->count();
        return $number;
    }

    /**
     * @desc 面试列表[]
     * @param $where
     * @param bool $field
     * @param string $order
     * @return mixed
     */
    public function interviewList($where, $field = false, $order = 'i.update_time desc'){
        $list = $this->alias('i')->join('__RECRUIT_RESUME__ as r on i.recruit_resume_id = r.id')->where($where)->field($field)->order($order)->select();
        return $list;
    }

    /**
     * @desc 悬赏下共入职人数
     * @param $where
     * @return mixed
     */
    public function interviewRecruitCount($where){
        $count = $this->alias('i')->join('__RECRUIT_RESUME__ as r on i.recruit_resume_id = r.id')->where($where)->count();
        return $count;
    }

    public function getInterviewField($where, $field){
        $res = $this->where($where)->getField($field);
        return $res;
    }
}
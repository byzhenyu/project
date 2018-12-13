<?php
/**
 * hr简历库模型
 */
namespace Admin\Model;

use Think\Model;

class HrResumeModel extends Model {
    protected $insertFields = array('hr_user_id', 'resume_id', 'recommend_label', 'add_time', 'recommend_voice');
    protected $updateFields = array('hr_user_id', 'resume_id', 'recommend_label', 'add_time', 'recommend_voice');
    protected $_validate = array(
        array('resume_id', 'require', '简历不能为空！', 1, 'regex', 3),
    );

    /**
     * @desc 获取人才列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @param $onlyCount bool 是否仅统计数量
     * @return array
     */
    public function getHrResumeList($where, $field = false, $order = 'h.add_time desc', $onlyCount = false){
        if(!$field) $field = 'h.id,h.resume_id,r.true_name,r.head_pic,h.add_time,r.age,r.sex';
        $number = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->join('__USER__ as u on h.hr_user_id = u.user_id', 'LEFT')->where($where)->count();
        if($onlyCount) return $number;
        $page = get_web_page($number);
        $list = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->join('__USER__ as u on h.hr_user_id = u.user_id', 'LEFT')->field($field)->order($order)->limit($page['limit'])->where($where)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * @desc web端HR简历库 [未审核简历展示问题]
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getHrResumeListWeb($where, $field = false, $order = 'r.update_time desc'){
        if(!$field) $field = 'h.id,h.resume_id,r.true_name,r.head_pic,h.add_time,r.age,r.sex';
        $number = M('Resume')->alias('r')->join('__HR_RESUME__ as h on r.id = h.resume_id', 'LEFT')->field($field)->order($order)->where($where)->count();
        $page = get_web_page($number);
        $list = M('Resume')->alias('r')->join('__HR_RESUME__ as h on r.id = h.resume_id', 'LEFT')->field($field)->order($order)->where($where)->limit($page['limit'])->select();
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
        $res = $this->getHrResumeInfo(array('resume_id' => $data['resume_id'], 'hr_user_id' => $data['hr_user_id']));
        if($res){
            $this->error = 'HR简历库中已有该简历！';
            return false;
        }
    }

    /**
     * @desc HR简历拥有数量排名情况
     * @param $user_id
     * @return mixed
     */
    public function getHrResumeRankingInfo($user_id){
        $model = M();
        $sql = 'select rownumber from (select @rownumber:=@rownumber+1 as rownumber,hr_user_id from (select @rownumber:=0) as r,(select hr_user_id,count(1) as number from ln_hr_resume group by hr_user_id order by number desc) as t) as sel where sel.hr_user_id = ' . $user_id;
        $query = $model->query($sql);
        $number = $query[0]['rownumber'];
        return $number;
    }

    /**
     * @desc 简历拥有数量统计
     * @param $where
     * @return mixed
     */
    public function getHrResumeCount($where){
        $res = $this->where($where)->count();
        return $res;
    }

    /**
     * @desc 获取简历库数量
     * @param $where
     * @return mixed
     */
    public function getHrResumeSel($where){
        $list = $this->where($where)->group('hr_user_id')->field('hr_user_id,count(1) as number')->select();
        return $list;
    }

    /**
     * @desc 获取HR人才列表字段信息
     * @param $where
     * @param $field
     * @return mixed
     */
    public function getHrResumeField($where, $field){
        $res = $this->where($where)->getField($field);
        return $res;
    }

    /**
     * @desc 检测HR下简历手机号是否存在
     * @param $mobile
     * @param bool $resume_id
     * @param bool $hr_id
     * @return bool
     */
    public function checkHrResumeMobile($mobile, $resume_id = false, $hr_id = false){
        $where = array('r.mobile' => $mobile, 'r.user_id' => $hr_id);
        if(!$resume_id){
            $res = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->where($where)->find();
        }
        else{
            $where['r.id'] = array('neq', $resume_id);
            $res = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->where($where)->find();
        }
        if($res) return true;
        return false;
    }

    /**
     * @desc HR拥有简历组成的条件
     * @param $hr_id
     * @param $limit string
     * @return array
     */
    public function getHrTags($hr_id, $limit = 'r.'){
        if(!$hr_id) return array();
        $edu_list = D('Admin/Education')->getEducationList(array('id' => array('gt', 0)));
        $edu_arr = array();
        foreach($edu_list as &$val) $edu_arr[$val['education_name']] = $val['id']; unset($val);
        $result = $this->alias('a')->join('__RESUME__ as r on a.resume_id = r.id')->where(array('a.hr_user_id' => $hr_id))->field('r.position_id,r.job_area,r.first_degree')->select();
        $where = array();
        foreach($result as &$val){
            $degree_where = ')';
            $degree_id = $edu_arr[$val['first_degree']];
            if($degree_id > 0) $degree_where = ' and '.$limit.'`degree` <= '.$degree_id.' )';
            $where[] = ' ('.$limit.'`position_id` = '.$val['position_id'].' and '.$limit.'`job_area` like \''.$val['job_area'].'%\''.$degree_where;
        }
        unset($val);
        $where_string = implode(' or ', $where);
        return $where_string;
    }
}
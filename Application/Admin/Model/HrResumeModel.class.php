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
    public function getHrResumeList($where, $field = false, $order = 'h.add_time desc'){
        if(!$field) $field = 'h.id,h.resume_id,r.true_name,r.head_pic,h.add_time,r.age,r.sex';
        $number = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->join('__USER__ as u on h.hr_user_id = u.user_id')->where($where)->count();
        $page = get_web_page($number);
        $list = $this->alias('h')->join('__RESUME__ as r on h.resume_id = r.id')->join('__USER__ as u on h.hr_user_id = u.user_id')->field($field)->order($order)->limit($page['limit'])->where($where)->select();
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
}
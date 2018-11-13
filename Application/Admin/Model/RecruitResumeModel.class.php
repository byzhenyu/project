<?php
/**
 * 悬赏推荐简历模型类
 */
namespace Admin\Model;

use Think\Model;

class RecruitResumeModel extends Model {
//    protected $insertFields = array('hr_user_id', 'position_id','position_name','recruit_num','','');
//    protected $updateFields = array('position_name', 'sort');
    protected $selectFields = array('*');
    protected $_validate = array(
        array('recruit_id', 'require', '悬赏信息id不能为空', 1, 'regex', 3),
        array('recruit_hr_uid', 'require', '发布悬赏的hr有误', 1, 'regex', 3),
        array('resume_id', 'require', '简历id有误', 1, 'regex', 3),
        array('hr_user_id', 'number', '推荐人用户信息有误', 1, 'regex', 3),
        //array('recommend_label', 'require', '请选择推荐标签', 1, 'regex', 3),
        //array('recommend_voice', 'require', '推荐语有误', 1, 'regex', 3),

    );
    /**
     * 获取推荐人列表
     */
    public function getHrListByPage($where,$field='', $order='resume.id desc') {
        if (!$field) {
            $field = array('resume.id as recruit_resume_id','resume.recruit_id','resume.add_time','user.user_id,user.nickname,user.user_name,user.head_pic');
        }
        $count = $this->alias('resume')->where($where)->count('distinct resume.hr_user_id');
        $page = get_web_page($count);
        $list = $this->alias('resume')
                    ->join('__USER__ user on resume.hr_user_id = user.user_id')
                    ->where($where)
                    ->field($field)
                    ->limit($page['limit'])
                    ->group('hr_user_id')
                    ->order($order)
                    ->select();
        foreach ($list as $k=>$v) {
            $list[$k]['add_time'] = time_format($v['add_time']);
        }
        return array(
            'info'=>$list,
            'page'=>$page['page']
        );
    }
    /**
     * 获取推荐简历列表
     * @param $where array 条件
     * @param $field string 字段
     * @param $isPage bool 是否返回分页数据
     * @param $order string 排序顺序
     * @return mixed
     */
    public function getResumeListByPage($where, $field = '', $order = 'r.is_open desc'){
        if(!$field) {
            $field = array('r.id, r.resume_id, resume.head_pic,resume.true_name,resume.sex, resume.age,resume.update_time,r.is_open');
        }
        $count = $this->alias('r')->where($where)->count();
        $page = get_web_page($count);
        $list = $this->alias('r')
            ->join('__RESUME__ resume on r.resume_id = resume.id')
            ->where($where)
            ->limit($page['limit'])
            ->field($field)
            ->order($order)
            ->select();
        foreach ($list as $k=>$v) {
            $list[$k]['update_time'] = time_format($v['update_time'],'Y-m-d');
            $list[$k]['sex'] = getSexInfo($v['sex']);
            if($list[$k]['is_open'] == 1){
                $list[$k]['age'] = time_to_age($list[$k]['age']).' [已下载]';
            }
        }
        return array('info' => $list, 'page' => $page['page']);
    }

    /**
     * @desc 获取推荐简历推荐信息
     * @param $where
     * @param $field
     * @return mixed
     */
    public function getRecruitResumeField($where, $field){
        if (!$field) {
            $field = $this->selectFields;
        }
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    /**
     * @desc 获取简历推荐详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getRecruitResumeInfo($where, $field = false){
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['hr_user_id'] = UID;
        $data['add_time'] = NOW_TIME;
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

    /**
     * 返回平均值
     * @param $where
     */
    public function getAverageValue($where) {
        $info = $this->where($where)->avg('commission');
        return fen_to_yuan($info);
    }

    /**
     * 详情
     */
    public function getDetail($where) {

        $info = $this->where($where)->find();
        $wordArr = C('WORK_NATURE');
        $exp = C('WORK_EXP');
        $sexArr = array('0'=>'不限','1'=>'男','2'=>'女');
        $degreeArr = M('Education')->where()->getField('id,education_name', true);
        $tags = M('Tags')->where(array('tags_type'=>3))->getField('id, position_name',true);
        $info['sex'] = $exp[$info['sex']];
        $info['nature'] = $wordArr[$info['nature']];
        $info['degree'] = $degreeArr[$info['degree']];
        $info['commission'] = fen_to_yuan($info['commission']);
        $info['add_time'] = time_format($info['add_time']);
        return $info;
    }
    /**
     * 获取推荐数
     */
    public function getRecruitResumeNum($where) {
        return $this->where($where)->count();
    }

    /**
     * @desc 简历推荐统计
     * @param $where
     * @return mixed
     */
    public function recruitResumeStatistic($where){
        $recruit_resume_count = $this->where($where)->field('id')->select();
        return $recruit_resume_count;
    }
}
<?php
/**
 * 悬赏模型类
 */
namespace Admin\Model;

use Think\Model;

class RecruitModel extends Model {
//    protected $insertFields = array('hr_user_id', 'position_id','position_name','recruit_num','','');
//    protected $updateFields = array('position_name', 'sort');
    protected $selectFields = array('*');
    protected $_validate = array(
        array('hr_user_id', 'require', '发布人不能为空', 1, 'regex', 3),
        array('position_id', 'require', '请选择悬赏职位', 1, 'regex', 3),
        array('commission,recruit_num', 'checkCommission', '悬赏佣金不足', 1, 'callback', 3),
        array('recruit_num', 'number', '请填写招聘人数', 1, 'regex', 3),
        array('nature', 'require', '发布人不能为空', 1, 'regex', 3),
        array('sex', array(0,1,2), '性别字段有误', 1, 'in', 3),
        array('degree', 'require', '请选择学历要求', 1, 'regex', 3),
        array('language_ability', '1,255', '请填写语言要求', 1, 'length', 3),
        array('experience', 'require', '请选择工作经验', 1, 'regex', 3),
        array('job_area', 'require', '请选择工作地区', 1, 'regex', 3),
        array('base_pay', 'require', '请填写基本工资', 1, 'regex', 3),
        array('merit_pay', 'require', '请填写绩效工资', 1, 'regex', 3),
        array('welfare', 'require', '请选择福利', 1, 'regex', 3),

    );

    /**
     * 验证佣金数量
     */
    protected function checkCommission($data) {
        $resumeMoney = yuan_to_fen(C('GET_RESUME_MONEY'));
        $entryMoney = yuan_to_fen(C('GET_ENTRY_MONEY'));
        $maxResume = C('MAX_RESUME');
        $resumeNum = $data['recruit_num'];
        if ($resumeNum > $maxResume) {
            $resumeNum = $maxResume;
        }
        $total = ($resumeMoney + $entryMoney)*$resumeNum;
        if ($data['commission'] < $total) {
            return false;
        }
        return true;
    }
    /**
     * 获取悬赏列表
     * @param $where array 条件
     * @param $field string 字段
     * @param $isPage bool 是否返回分页数据
     * @param $order string 排序顺序
     * @return mixed
     */
    public function getRecruitList($where, $field = false, $order = 'id desc', $isPage = true){
        if(!$field) $field = '*';
        if(!$isPage) return $this->where($where)->field($field)->order($order)->select();
        $count = $this->where($where)->count();

        $page = get_web_page($count);
        $list = $this->where($where)->limit($page['limit'])->field($field)->order($order)->select();
        foreach ($list as $k=>$v) {
            $list[$k]['add_time'] = time_format($v['add_time']);
            $list[$k]['commission'] = fen_to_yuan($v['commission']);
            $list[$k]['last_token'] = fen_to_yuan($v['last_token']);
            $list[$k]['get_resume_token'] = fen_to_yuan($v['get_resume_token']);
            $list[$k]['entry_token'] = fen_to_yuan($v['entry_token']);
            $list[$k]['sex'] = getSexInfo($v['sex']);
        }
        return array('info' => $list, 'page' => $page['page']);
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option) {

        $data['add_time'] = NOW_TIME;

    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option) {

    }

    /**
     * 返回平均值
     * @param $where
     */
    public function getAverageValue($where) {
        $commission = $this->where($where)->Sum('commission');
        $recruit_num = $this->where($where)->Sum('recruit_num');
        $info = $commission / $recruit_num;
        $data['average'] = fen_to_yuan($info); //平均花费

        return $data;
    }

    /**
     *  获取字段
     */
    public function getRecruitInfo($where,$field='') {
        if (!$field) {
            $field = $this->selectFields;
        }
        $info = $this->where($where)->field($field)->find();
        return $info;
    }
    /**
     * 详情
     */
    public function getDetail($where) {
        $field = 'u.nickname,u.head_pic, r.*';
        $info = $this->alias('r')
            ->join('__USER__ u on r.hr_user_id = u.user_id')
            ->where($where)
            ->field($field)
            ->find();
        $sexArr = array('0'=>'不限','1'=>'男','2'=>'女');

        $degreeArr = M('Education')->getField('id,education_name', true);
        $tags = M('Tags')->where(array('tags_type'=>3))->field('id, tags_name')->select();
        $welfare = explode(',',$info['welfare']);

        foreach ($tags as $k=>$v) {
            if (in_array($v['id'],$welfare)) {
                $tags[$k]['is_select'] = 1;
            } else {
                $tags[$k]['is_select'] = 0;
            }
        }
        $experience = C('WORK_EXP');
        $info['degree'] = $degreeArr[$info['degree']];
        $info['sex'] = $sexArr[$info['sex']];
        $info['experience'] = $experience[$info['experience']];
        $info['commission'] = fen_to_yuan($info['commission']);
        $info['add_time'] = time_format($info['add_time']);
        $info['welfare'] = $tags;
        return $info;
    }
    /**
     * 获取我的推荐
     */
    public function getMyRecruitByPage() {
        $RecruitResumeModel = M('RecruitResume');
        $recruit_ids = $RecruitResumeModel->where(array('hr_user_id'=>UID))->field('recruit_id')->distinct(true)->getField('recruit_id',true);

        $where['id'] = array('in', $recruit_ids);

        $fields = array('id,hr_user_id,position_id,position_name,commission');
        $count = $this->where($where)->count();
        $page = get_web_page($count);

        $recruitInfo = $this->where($where)
            ->field($fields)
            ->limit($page['limit'])
            ->order('id desc')
            ->select();

        foreach ($recruitInfo as $k=>$v) {
            $map['recruit_id'] = array('eq', $v['id']);
            $recruitInfo[$k]['total'] = $RecruitResumeModel->where($map)->count();

            $map['hr_user_id'] = array('eq', UID);
            $recruitInfo[$k]['my'] = $RecruitResumeModel->where($map)->count();
            $recruitInfo[$k]['commission'] = fen_to_yuan($v['commission']);
        }
        return array(
            'info'=>$recruitInfo,
            'page'=>$page['page']
        );

    }

}
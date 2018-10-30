<?php
/**
 * 悬赏模型类
 */
namespace Admin\Model;

use Think\Model;

class RecruitModel extends Model {
//    protected $insertFields = array('hr_user_id', 'position_id','position_name','recruit_num','','');
    protected $updateFields = array('age', 'nature', 'sex', 'degree', 'language_ability', 'experience', 'job_area', 'base_pay', 'merit_pay', 'welfare', 'description', 'id', 'status', 'is_pos');
    protected $selectFields = array('*');
    protected $_validate = array(
        array('position_id', 'require', '请选择悬赏职位', 1, 'regex', 3),
        //array('commission,recruit_num', 'checkCommission', '悬赏佣金不足', 1, 'callback', 3),
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

        array('nature', 'require', '岗位性质不能为空', 1, 'regex', 4),
        array('sex', array(0,1,2), '性别字段有误', 1, 'in', 4),
        array('degree', 'require', '请选择学历要求', 1, 'regex', 4),
        array('language_ability', '1,255', '请填写语言要求', 1, 'length', 4),
        array('experience', 'require', '请选择工作经验', 1, 'regex', 4),
        array('job_area', 'require', '请选择工作地区', 1, 'regex', 4),
        array('base_pay', 'require', '请填写基本工资', 1, 'regex', 4),
        array('merit_pay', 'require', '请填写绩效工资', 1, 'regex', 4),
        array('welfare', 'require', '请选择福利', 1, 'regex', 4),

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
        $recruitResumeModel = D('Admin/RecruitResume');
        $page = get_web_page($count);
        $list = $this->where($where)->limit($page['limit'])->field($field)->order($order)->select();
        foreach ($list as $k=>$v) {
            $list[$k]['num'] = $recruitResumeModel->getRecruitResumeNum(array('recruit_id'=>$v['id']));
            $list[$k]['add_time'] = time_format($v['add_time']);
            if(isset($list[$k]['commission'])) $list[$k]['commission'] = fen_to_yuan($v['commission']);
            if(isset($list[$k]['last_token'])) $list[$k]['last_token'] = fen_to_yuan($v['last_token']);
            if(isset($list[$k]['get_resume_token'])) $list[$k]['get_resume_token'] = fen_to_yuan($v['get_resume_token']);
            if(isset($list[$k]['entry_token'])) $list[$k]['entry_token'] = fen_to_yuan($v['entry_token']);
            if(isset($list[$k]['sex'])) $list[$k]['sex'] = getSexInfo($v['sex']);
        }
        return array('info' => $list, 'page' => $page['page']);
    }

    protected function _before_insert(&$data, $option) {
        $data['add_time'] = NOW_TIME;
        $data['hr_user_id'] = UID;
    }
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
        $field = 'u.nickname,u.head_pic, r.id,r.hr_user_id,r.position_id,r.position_name,r.recruit_num,r.age,r.nature,r.sex,r.degree,r.language_ability,r.experience,r.job_area,r.base_pay,r.merit_pay,r.welfare,r.description,r.commission,r.add_time,r.get_resume_token,r.entry_token,r.share';
        $info = $this->alias('r')
            ->join('__USER__ u on r.hr_user_id = u.user_id')
            ->where($where)
            ->field($field)
            ->find();
        $sexArr = array('0'=>'不限','1'=>'男','2'=>'女');

        $degreeArr = M('Education')->where(array('id' => $info['degree']))->getField('education_name');

        $experience = C('WORK_EXP');
        $info['degree'] = $degreeArr.'或'.$degreeArr.'以上';
        $info['sex'] = $sexArr[$info['sex']];
        $info['experience'] = $experience[$info['experience']];
        $info['commission'] = fen_to_yuan($info['commission']);
        $info['add_time'] = time_format($info['add_time']);
        $info['get_resume_token'] = fen_to_yuan($info['get_resume_token']);
        $info['entry_token'] = fen_to_yuan($info['entry_token']);

        return $info;
    }
    /**
     * 获取我的推荐
     */
    public function getMyRecruitByPage() {
        $RecruitResumeModel = M('RecruitResume');
        $recruit_ids = $RecruitResumeModel->where(array('hr_user_id'=>UID))->field('recruit_id')->distinct(true)->getField('recruit_id',true);
        if(empty($recruit_ids)) {
            return array(
                'info'=>'',
                'page'=>''
            );
        };
        $where['r.id'] = array('in', $recruit_ids);

        $fields = array('r.id,r.hr_user_id,r.position_id,r.position_name,r.commission,r.add_time,u.head_pic,u.nickname,u.user_name');
        $count = $this->alias('r')->where($where)->count();

        $page = get_web_page($count);

        $recruitInfo = $this->alias('r')->join('__USER__ u on r.hr_user_id = u.user_id')->where($where)
            ->field($fields)
            ->limit($page['limit'])
            ->order('id desc')
            ->select();

        foreach ($recruitInfo as $k=>$v) {
            $map['recruit_id'] = array('eq', $v['id']);
            $recruitInfo[$k]['total'] = $RecruitResumeModel->where($map)->count();
            $recruitInfo[$k]['add_time'] = time_format($v['add_time'],'Y-m-d');
            if (empty($recruitInfo[$k]['nickname'])) {
                $recruitInfo[$k]['nickname'] = $v['user_name'];
            }
            $map['hr_user_id'] = array('eq', UID);
            $recruitInfo[$k]['my'] = $RecruitResumeModel->where($map)->count();
            $recruitInfo[$k]['commission'] = fen_to_yuan($v['commission']);
        }
        return array(
            'info'=>$recruitInfo,
            'page'=>$page['page']
        );

    }

    /**
     * @desc 悬赏数量
     * @param $where
     * @return mixed
     */
    public function getRecruitCount($where){
        $res = $this->where($where)->count();
        return $res;
    }

    /**
     * @desc 待结算
     * @param $where
     * @param $field
     * @return mixed
     */
    public function getRecruitPendingSel($where, $field){
        $list = $this->where($where)->field($field)->group('recruit_num')->select();
        return $list;
    }

    /**
     * @desc 获取简历联系方式/简历入职
     * @param $recruit_resume_id int 悬赏推荐表主键id
     * @param $operate_type int 1、获取简历联系方式 2、入职获取简历
     * @return bool
     */
    public function recruitPayOff($recruit_resume_id, $operate_type = 1){
        $accountLogModel = D('Admin/AccountLog');
        $userModel = D('Admin/User');
        $recruitResumeModel = D('Admin/RecruitResume');
        $type_arr = array(1 => 2, 2 => 3);
        $type_string = array(1 => '获取简历令牌', 2 => '入职简历令牌');
        $type_token = array(1 => 'get_resume_token', 2 => 'entry_token');
        $recruit_resume_info = $recruitResumeModel->getRecruitResumeInfo(array('id' => $recruit_resume_id));
        if(!$recruit_resume_info) return false;
        $recruit_id = $recruit_resume_info['recruit_id'];
        M()->startTrans();
        //减少悬赏发布人冻结资金
        $recruit_where = array('id' => $recruit_id);
        $recruit_info = $this->getRecruitInfo($recruit_where);
        $release_res = $userModel->decreaseUserFieldNum($recruit_info['hr_user_id'], 'frozen_money', $recruit_info[$type_token[$operate_type]]);
        //增加用户资金/暂冻结金额资金
        $token_log_res = $userModel->increaseUserFieldNum($recruit_resume_info['hr_user_id'], 'user_money', $recruit_info[$type_token[$operate_type]]);
        $token_log_res2 = $userModel->increaseUserFieldNum($recruit_resume_info['hr_user_id'], 'frozen_money', $recruit_info[$type_token[$operate_type]]);
        $token_account_data = array(
            'user_id' => $recruit_resume_info['hr_user_id'],
            'user_money' => $recruit_info[$type_token[$operate_type]],
            'change_desc' => $type_string[$operate_type],
            'change_type' => $type_arr[$operate_type],
            'order_sn' => $recruit_resume_id,
            'change_time' => NOW_TIME
        );
        //增加资金记录
        $token_account_res = $accountLogModel->add($token_account_data);
        //增加平台资金记录
        account_log(0, $recruit_info[$type_token[$operate_type]], $type_arr[$operate_type], $type_string[$operate_type], $recruit_resume_id);
        if(2 == $operate_type){
            $interviewModel = D('Admin/Interview');
            $interview_num = $interviewModel->interviewRecruitCount(array('r.recruit_id' => $recruit_id, 'i.state' => 1));
            $is_post = 1;
            if($recruit_info['recruit_num'] - 1 == $interview_num){
                $is_post = 2;
            }
            $this->where(array('id' => $recruit_id))->setField('is_post', $is_post);
        }
        if(false !== $release_res && false !== $token_log_res && false !== $token_log_res2 && false !== $token_account_res){
            M()->commit();
            return true;
        }
        else{
            M()->rollback();
            return false;
        }
    }
}
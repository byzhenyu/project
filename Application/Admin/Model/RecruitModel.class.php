<?php
/**
 * 悬赏模型类
 */
namespace Admin\Model;

use Think\Model;

class RecruitModel extends Model {
//    protected $insertFields = array('hr_user_id', 'position_id','position_name','recruit_num','','');
    protected $updateFields = array('age', 'nature', 'sex', 'degree', 'language_ability', 'experience', 'job_area', 'base_pay', 'merit_pay', 'welfare', 'description', 'id', 'status', 'is_post');
    protected $selectFields = array('*');
    protected $_validate = array(
        array('position_id', 'require', '请选择悬赏职位', 1, 'regex', 3),
        array('recruit_num', 'number', '请填写招聘人数', 1, 'regex', 3),
        array('recruit_num', array(1, 100), '招聘人数不能超过100', 1, 'between', 3),
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
     * 获取悬赏列表
     * @param $where array 条件
     * @param $field bool 字段
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

    /**
     * @desc 闪荐二期HR悬赏列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getHrRecruitList($where, $field = false, $order = 'r.id desc'){
        if(!$field) $field = '*';
        $count = $this->alias('r')->join('__COMPANY_INFO__ as c on r.hr_user_id = c.user_id', 'LEFT')->where($where)->count();
        if($count < 1){
            unset($where['_string']);
            $count = $this->alias('r')->join('__COMPANY_INFO__ as c on r.hr_user_id = c.user_id', 'LEFT')->where($where)->count();
        }
        $page = get_web_page($count);
        $list = $this->alias('r')->join('__COMPANY_INFO__ as c on r.hr_user_id = c.user_id', 'LEFT')->where($where)->limit($page['limit'])->field($field)->order($order)->select();
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
     * @param $where
     * @param string $field
     * @return mixed
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
        $info['degree'] = $degreeArr != '不限' ? $degreeArr.'或'.$degreeArr.'以上' : '不限';
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
     * @param $where
     * @param $data
     * @return bool
     */
    public function saveRecruitData($where, $data){
        $res = $this->where($where)->save($data);
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
     * @param $is_pay_back bool 是否属于补缴下载简历 true :是 false: 不是
     * @return bool
     */
    public function recruitPayOff($recruit_resume_id, $operate_type = 1, $is_pay_back = false){
        $accountLogModel = D('Admin/AccountLog');
        $userModel = D('Admin/User');
        $recruitResumeModel = D('Admin/RecruitResume');
        $type_arr = array(1 => 2, 2 => 3);
        $type_string = array(1 => '获取简历令牌', 2 => '入职简历令牌');
        $type_token = array(1 => 'get_resume_token', 2 => 'entry_token');
        $recruit_resume_info = $recruitResumeModel->getRecruitResumeInfo(array('id' => $recruit_resume_id));
        if(!$recruit_resume_info) return false;
        $recruit_resume_user_where = array('user_id' => $recruit_resume_info['hr_user_id']);
        $recruit_resume_user = $userModel->getUserInfo($recruit_resume_user_where, 'frozen_money,recommended_number');//悬赏推荐用户信息
        $recruit_user_where = array('user_id' => $recruit_resume_info['recruit_hr_uid']);
        $recruit_user = $userModel->getUserInfo($recruit_user_where, 'frozen_money,recruit_number,invoice_amount');//悬赏发布用户信息
        $recruit_id = $recruit_resume_info['recruit_id'];
        $radio = C('RATIO');
        M()->startTrans();
        //减少悬赏发布人冻结资金
        $recruit_where = array('id' => $recruit_id);
        $recruit_info = $this->getRecruitInfo($recruit_where);
        $recruit_save = array('frozen_money' => $recruit_user['frozen_money'] - $recruit_info[$type_token[$operate_type]]);
        //$release_res = $userModel->decreaseUserFieldNum($recruit_info['hr_user_id'], 'frozen_money', $recruit_info[$type_token[$operate_type]]);
        //增加用户资金/暂冻结金额资金
        $user_account_money = $recruit_info[$type_token[$operate_type]] * ((100 - $radio) / 100);
        $plat_account_money = $recruit_info[$type_token[$operate_type]] - $user_account_money;
        $recruit_resume_save = array('frozen_money' => $recruit_resume_user['frozen_money'] + $user_account_money);
        //$token_log_res2 = $userModel->increaseUserFieldNum($recruit_resume_info['hr_user_id'], 'frozen_money', $user_account_money);
        $token_account_data = array(
            'user_id' => $recruit_resume_info['hr_user_id'],
            'user_money' => $user_account_money,
            'change_desc' => $type_string[$operate_type],
            'change_type' => $type_arr[$operate_type],
            'order_sn' => $recruit_resume_id,
            'change_time' => NOW_TIME
        );
        //增加资金记录
        $token_account_res = $accountLogModel->add($token_account_data);
        //增加平台资金记录
        account_log(0, $plat_account_money, $type_arr[$operate_type], $type_string[$operate_type], $recruit_resume_id);
        if(2 == $operate_type){
            $interviewModel = D('Admin/Interview');
            $interview_num = $interviewModel->interviewRecruitCount(array('r.recruit_id' => $recruit_id, 'i.state' => 1));
            $is_post = 1;
            if($recruit_info['recruit_num'] == $interview_num){
                $is_post = 2;
            }
            $this->where(array('id' => $recruit_id))->setField('is_post', $is_post);
            $recruit_save['recruit_number'] = $recruit_user['recruit_number']++;//增加悬赏发布悬赏总人数字段
            $recruit_resume_save['recommended_number'] = $recruit_resume_user['recommended_number']++;//悬赏推荐方
            //$userModel->increaseUserFieldNum($recruit_resume_info['hr_user_id'], 'recommended_number', 1);
            //$userModel->increaseUserFieldNum($recruit_resume_info['recruit_hr_uid'], 'recruit_number', 1);
        }
        //增加用户可开发票金额
        $recruit_save['invoice_amount'] = $recruit_user['invoice_amount'] + $recruit_info[$type_token[$operate_type]];
        $release_res = $userModel->saveUserData($recruit_user_where, $recruit_save);
        $token_log_res2 = $userModel->saveUserData($recruit_resume_user_where, $recruit_resume_save);
        //修改悬赏剩余佣金
        $recruit_info_save = array();
        if(!$is_pay_back && $operate_type == 1){
            $recruit_info_save['last_token'] = $recruit_info['last_token'] - $recruit_info[$type_token[$operate_type]];
        }
        if($operate_type == 2) $recruit_info_save['last_token'] = $recruit_info['last_token'] - $recruit_info[$type_token[$operate_type]];
        if(count($recruit_info_save) > 0) $this->where($recruit_where)->save($recruit_info_save);
        if(false !== $release_res && false !== $token_log_res2 && false !== $token_account_res){
            M()->commit();
            return true;
        }
        else{
            M()->rollback();
            return false;
        }
    }
}
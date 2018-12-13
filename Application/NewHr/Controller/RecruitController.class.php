<?php
/**
 * 悬赏管理控制器
 */
namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class RecruitController extends HrCommonController {

    /**
     * @desc 新增/编辑悬赏
     */
    public function editRecruit(){
        $hr_id = HR_ID;
        $model = D('Hr/Recruit');
        $user_model = D('Hr/User');
        $cacheModel = D('Admin/RecruitCache');
        $recruit_id = I('recruit_id', 0, 'intval');
        $recruit_info = $model->getRecruitInfo(array('id' => $recruit_id));
        if(IS_POST){
            $data = I('post.', '');
            if($data['recruit_num'] > 100) $this->ajaxReturn(V(0, '悬赏人数不能超过100'));
            $data['hr_user_id'] = $hr_id;
            $data['job_area'] = $data['province'].','.$data['city'].','.$data['county'];
            if(!check_is_auth($hr_id)) {
                $string = auth_string();
                if(false !== $string) $this->ajaxReturn(V(0, $string));
                $this->ajaxReturn(V(0, '请先完成实名认证'));
            }
            $companyInfoModel = D('Admin/CompanyInfo');
            $checkCompanyInfo = $companyInfoModel->checkCompanyInfo($hr_id);
            if ($checkCompanyInfo == 0) {
                $this->ajaxReturn(V(0, '请先完善公司资料'));
            }
            //$recruit_resume_model = D('Admin/RecruitResume');
            $position_name = M('Position')->where(array('id' => $data['position_id']))->getField('position_name');
            if(!$position_name) $this->ajaxReturn(V(0, '获取不到职位名称！'));
            $getResumeMoney = C('GET_RESUME_MONEY');
            $data['position_name'] = $position_name;
            if($data['commission'] <= $getResumeMoney) $this->ajaxReturn(V(0, '悬赏金额需要大于'.$getResumeMoney.'令牌！'));
            $regex = '/^\d+(\.\d{1,2})?$/';
            if(!preg_match($regex, $data['commission'])){
                $this->ajaxReturn(V(0, '悬赏令牌小数点最多两位！'));
            }
            if(cmp_contraband($data['description'])){
                $this->ajaxReturn(V(0, '悬赏工作描述中有违禁词！'));
            }
            $data['commission'] = yuan_to_fen($data['commission']);
            $data['last_token'] = $data['commission'] * $data['recruit_num'];
            $data['get_resume_token'] = yuan_to_fen($getResumeMoney);
            $data['entry_token'] = $data['commission'] - $data['get_resume_token'];//入职获取令牌
            $user_money = $user_model->getUserField(array('user_id' => $hr_id),'user_money');//用户余额
            if($recruit_id > 0){
                $data['id'] = $recruit_id;
                /*$recruitResumeNumber = $recruit_resume_model->getRecruitResumeNum(array('recruit_id' => $recruit_id));
                if($recruitResumeNumber > 0 && $data['commission'] < $recruit_info['commission']) $this->ajaxReturn(V(0, '该悬赏已有推荐，悬赏金额不可小于原悬赏金额！'));
                $frozen_money = ($data['commission'] - $recruit_info['commission']) * $data['recruit_num'];//悬赏数量不可修改
                if($frozen_money > $user_money) $this->ajaxReturn(V(0, '用户可支配金额不足以支付悬赏佣金！'));
                $user_model->recruitUserMoney($frozen_money, $hr_id, $frozen_money);*/
                unset($data['recruit_num']);
                unset($data['commission']);
                unset($data['last_token']);
                $create = $model->create($data, 4);
                if(false !== $create){
                    $res = $model->save($data);
                    if(false !== $res){
                        $this->ajaxReturn(V(1, '保存成功！', $recruit_id));
                    }
                }
                $this->ajaxReturn(V(0, $model->getError()));
            }
            else{
                if (($data['commission'] * $data['recruit_num']) > $user_money) {
                    //余额不足，跳转充值页，保存之前的数据
                    $cacheModel->add($data);
                    $this->ajaxReturn(V(2, '悄悄的告诉你，你的令牌不足喽。马上充值令牌，快速发布悬赏。'));
                }
                $trans = M();
                if ($model->create($data, 1) ===false) {
                    $trans->rollback();
                    $this->ajaxReturn(V(0, $model->getError()));
                }
                $newId = $model->add();
                if ($newId ===false) {
                    $trans->rollback();
                    $this->ajaxReturn(V(0, '发布失败'));
                }
                //修改金额
                $res = $user_model->recruitUserMoney($data['commission'] * $data['recruit_num'], $hr_id);
                if ($res['status'] ==0) {
                    $trans->rollback();
                    $this->ajaxReturn($res);
                }
                add_key_operation(5, $newId);
                $task_id = 4;
                add_task_log($hr_id, $task_id);
                refreshRecruitCache($hr_id);
                $trans->commit();
                $this->ajaxReturn(V(1,'发布成功', $newId));
            }
        }
        $cache_info = $cacheModel->getRecruitCacheInfo(array('hr_user_id' => HR_ID));
        if(!$recruit_info) $recruit_info = $cache_info;
        $recruit_info['position_parent'] = 0;
        if(!$recruit_info['position_id']) $recruit_info['position_id'] = 0;
        if($recruit_info['position_id']) $recruit_info['position_parent'] = D('Admin/Position')->getPositionField(array('id' => $recruit_info['position_id']), 'parent_id');
        $industry = D('Admin/Industry')->getIndustryList();
        $recruit_data = array();
        $recruit_data['nature'] = returnArrData(C('WORK_NATURE')); //性质
        $recruit_data['sex'] = returnArrData(array(0 => '不限',1 => '男',2 => '女'));
        $recruit_data['degree'] = D('Admin/Education')->getEducationList(array(),'id,education_name');//学历
        $recruit_data['experience'] = returnArrData(C('WORK_EXP')); //经验
        $recruit_data['lang'] = returnArrData(C('SHAN_LANGUAGE'));
        $recruit_data['tags'] = D('Admin/Tags')->getTagsList(array('tags_type' => 3)); //福利标签
        $recruit_data['age'] = returnArrData(C('SHAN_AGE'));
        $recruit_info['welfare_data'] = explode(',', $recruit_info['welfare']);
        $recruit_info['lan_data'] = explode(',', $recruit_info['language_ability']);
        foreach($recruit_data['tags'] as &$val){
            $val['sel'] = 0;
            if(in_array($val['tags_name'], $recruit_info['welfare_data'])) $val['sel'] = 1;
        }
        unset($val);
        foreach($recruit_data['lang'] as &$val){
            $val['sel'] = 0;
            if(in_array($val['value'], $recruit_info['lan_data'])) $val['sel'] = 1;
        }
        $recruit_info['commission'] = fen_to_yuan($recruit_info['commission']);
        $recruit_info['job_area_t'] = explode(',', $recruit_info['job_area']);
        $recruit_info['job_area1'] = $recruit_info['job_area_t'][0];
        $recruit_info['job_area2'] = $recruit_info['job_area_t'][1];
        $recruit_info['job_area3'] = $recruit_info['job_area_t'][2];
        $this->recruit_data = $recruit_data;
        $this->industry = $industry;
        $this->info = $recruit_info;
        $this->display();
    }

    public function frozenShelfRecruit(){
        $model = D('Hr/Recruit');
        $recruit_id = I('recruit_id', 0, 'intval');
        $field = I('field', 'is_shelf', 'trim');
        if(!in_array($field, array('is_frozen', 'is_shelf'))) $this->ajaxReturn(V(0, '操作类型有误！'));
        $result = $model->changeRecruitFrozenShelf(array('id' => $recruit_id), $field);
        if(false !== $result){
            $this->ajaxReturn(V(1, '修改成功！'));
        }
        else{
            $this->ajaxReturn(V(0, '修改失败！'));
        }
    }

    /**
     * @desc 悬赏详情
     */
    public function seeRecruitDetail(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id);
        $model = D('Admin/Recruit');
        $userModel = D('Admin/User');
        $info = $model->getRecruitInfo($where);
        $user_where = array('user_id' => $info['hr_user_id']);
        $user_info = $userModel->getUserInfo($user_where, 'nickname,user_name');
        $info['release_name'] = !empty($user_info['nickname']) ? $user_info['nickname'] : $user_info['user_name'];
        $info['experience'] = C('WORK_EXP')[$info['experience']];
        $this->info = $info;
        $this->display();
    }

    public function recruitList(){
        $keywords = I('keyword', '', 'trim');
        $where = array('hr_user_id' => HR_ID);
        $model = D('Hr/Recruit');
        if($keywords) $where['position_name|job_area'] = array('like', '%'.$keywords.'%');
        $list = $model->getRecruitList($where);
        $user_model = D('Admin/User');
        $user_info = $user_model->getUserInfo(array('hr_user_id' => HR_ID), 'nickname,user_name');
        foreach($list['info'] as &$val){
            $val['release_name'] = !empty($user_info['nickname']) ? $user_info['nickname'] : $user_info['user_name'];
        }
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->keyword = $keywords;
        $this->display();
    }

    /**
     * @desc 悬赏推荐列表
     */
    public function listRecruitResume(){
        $recruit_id = I('id', 0, 'intval');
        $keywords = I('keyword', '', 'trim');
        $model = D('Admin/RecruitResume');
        $where = array('r.recruit_id' => $recruit_id);
        if($keywords) $where['r.recommend_label'] = array('like', '%'.$keywords.'%');
        $list = $model->getResumeListByPage($where);
        $this->list = $list['info'];
        $this->keyword = $keywords;
        $this->page = $list['page'];
        $this->display();
    }

    /**
     * @desc 悬赏佣金情况
     */
    public function seeRecruitAccountLog(){
        $recruit_id = I('recruit_id', 0, 'intval');
        $recruit_resume_model = D('Admin/RecruitResume');
        $recruit_resume = $recruit_resume_model->recruitResumeStatistic(array('recruit_id' => $recruit_id));
        if(count($recruit_resume) > 0){
            $order_sn = array();
            $keywords = I('keyword', '', 'trim');
            foreach($recruit_resume as &$val) $order_sn[] = $val['id']; unset($val);
            //$where = array('change_type' => array('in', array(2,3,7)), 'order_sn' => array('in', $order_sn));
            if($keywords) $where['u.mobile|u.nickname'] = array('like', '%'.$keywords.'%');
            $data = D('Admin/AccountLog')->getRecruitAccountList($where);
            foreach($data['info'] as &$val){
                $val['user_money'] = fen_to_yuan($val['user_money']);
                $val['diss_string'] = '冻结中';
                if($val['diss'] == 1) $val['diss_string'] = '已结算';
                $val['change_time'] = time_format($val['change_time'], 'Y-m-d');
            }
            unset($val);
        }
        $this->info = $data['info'];
        $this->page = $data['page'];
        $this->display();
    }
}
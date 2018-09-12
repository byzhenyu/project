<?php
namespace Api\Controller;
use Common\Controller\ApiUserCommonController;
use Think\Verify;

class RecruitApiController extends ApiUserCommonController{
    //发布悬赏页面
    public function publishPage() {
        //获取职位需要单独接口
        $data['ratio'] = returnArrData(C('RATIO')); //比例
        $data['nature'] = returnArrData(C('WORK_NATURE')); //性质
        $data['sex'] = returnArrData(array('0'=>'不限','1'=>'男','2'=>'女'));
        $data['degree'] = D('Admin/Education')->getEducationList(array(),'id,education_name');//学历
        $data['experience'] = returnArrData(C('WORK_EXP')); //经验
        //地区单独接口
        $data['tags'] = D('Admin/Tags')->getTagsList(array('tags_type'=>3)); //福利标签
        $this->apiReturn(V(1 , '悬赏页面信息',$data));
    }

    //获取平均悬赏金额
    public function getCommissionValue() {
        $position_id = I('position_id',0,'intval');
        $value = D('Admin/Recruit')->getAverageValue(array('position_id'=>$position_id));

        $this->apiReturn(V(1,'令牌平均值',$value));
    }
    //发布接口
    public function publish() {
        $data = I('post.', '');
        $id = I('id', 0,'intval');
        $model = D('Admin/Recruit');
        $position_name = M('Position')->where(array('id'=>$data['position_id']))->getField('position_name');
        $data['position_name'] = $position_name;

        if ($model->create($data) ===false) {
            $this->apiReturn(V(0, $model->getError()));
        }
        if ($id > 0) {
            $res = $model->save();
            if ($res ===false) {
                $this->apiReturn(V(0,'修改失败'));
            } else {
                $this->apiReturn(V(1, '修改成功'));
            }
        } else {
            $newId = $model->add();
            if ($newId ===false) {
                $this->apiReturn(V(0, '发布失败'));
            } else {
                $this->apiReturn(V(1,'发布成功'));
            }
        }

    }

    /**
     * 悬赏列表
     *  type 0全部 1我的
     *  age string (16-20)
     */
    public function getRecruitList() {
        $type = I('type', 0, 'intval');
        $area = I('area', ''); //diqu
        $position_name = I('position_name','');//求职方向
        if ($type == 1) {
            $where['hr_user_id'] = array('eq', UID);
        } else {

        }

        $data = D('Admin/Recruit')->getRecruitList($where);
        $this->apiReturn(V(1, '悬赏列表', $data['info']));
    }
    /**
     * 根据简历匹配悬赏
     * $resume_id 简历id
     */
    public function getRecruitListByResume() {
        $resume_id = I('id', 0, 'intval');
        $resumeInfo = D('Admin/Resume')->getResumeInfo(array('id'=>$resume_id),'id,user_id,job_intension,job_area');

        $areaInfo = explode(',', $resumeInfo['job_area']);
        $intension = explode(',', $resumeInfo['job_intension']);

        foreach ($areaInfo as $v) {
            $where[]['job_area'] = array('like', '%'.$v.'%');
        }

        foreach ($intension as $v) {
            $where[]['position_name'] = array('like', '%'.$v.'%');
        }
        $where['_logic'] = 'or';
        $map['_complex'] = $where;

        $data = D('Admin/Recruit')->getRecruitList($map);
        $this->apiReturn(V(1, '悬赏列表', $data['info']));
    }

    /**
     * 悬赏详情
     */
    public function getRecruitListDetail() {
        $id = I('id', 0,'intval');
        $info = D('Admin/Recruit')->getDetail(array('id'=>$id));
        $this->apiReturn(V(1,'详情', $info));
    }

    /**
     * 查看悬赏下的推荐人列表
     * id 悬赏信息id
     */
    public function getReferrerHrList() {
        $id = I('recruit_id', 0, 'intval');
        $where['recruit_id'] = array('eq', $id);
        $data = D('Admin/RecruitResume')->getHrListByPage($where);
        $this->apiReturn(V(1, '推荐人列表',$data['info']));
    }

    /**
     *  推荐人列表下面的简历
     *  hr_id推荐人id
     *  recruit_id悬赏id
     */
    public function getReferrerResumeList() {
        $recruit_id = I('recruit_id', 0, 'intval');
        $hr_id = I('hr_user_id', 0, 'intval');
        $where['recruit_id'] = $recruit_id;
        $where['hr_user_id'] = $hr_id;
        $data = D('Admin/RecruitResume')->getResumeListByPage($where);
        $this->apiReturn(V(1, '推荐人列表',$data['info']));
    }

    /**
     *  获取联系方式
     *  recruit_id 悬赏id
     *  resume_id 简历id
     */
    public function getResumePhoneNumber() {
        $recruit_id = I('recruit_id', 0, 'intval');
        $resume_id = I('resume_id', 0, 'intval');
        //获取分配比例
        $RecruitModel = D('Admin/Recruit');
        $RecruitInfo =$RecruitModel->getRecruitInfo(array('id'=>$recruit_id),'id,get_resume_token');

        //推荐简历信息
        $hrInfo = D('Admin/RecruitResume')->getRecruitResumeField(array('id'=>$resume_id),'id,recruit_id,recruit_hr_uid,resume_id,hr_user_id');
        $trans = M();
        $trans->startTrans();
        $res =  $RecruitModel->where(array('id'=>$recruit_id))->setDec('last_token',$RecruitInfo['get_resume_token']);
        if ($res ===false) {
           $trans->rollback();
           $this->apiReturn(V(0, '扣除令牌失败'));
        }
        $data['type'] = 1;
        $data['token_num'] = $RecruitInfo['get_resume_token'];
        $data['user_id'] = $hrInfo['hr_user_id'];
        $data['recruit_id'] = $RecruitInfo['id'];
        $tokenLogModel = D('Admin/TokenLog');
        if($tokenLogModel->create($data) ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, $tokenLogModel->getError()));
        };
        $id = $tokenLogModel->add();
        if ($id ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, '插入记录失败'));
        }
        $trans->commit();
        $this->apiReturn(V(1, '查看成功'));
    }


    /**
     *  每日任务
     */
    public function getTaskList() {
        $info = D('Admin/Task')->getTaskList();

        $this->apiReturn(V(1, '每日任务', $info));
    }

    /**
     *  擅长领域(求职方向)
     *  tags_id 标签id（多个用,隔开）
     */
    public function saveLikeTags() {
        $tagsId = I('tags_id', '');
        $res = M('User')->where(array('user_id'=>UID))->setField('like_tags',$tagsId);
        if ($res ===false) {
            $this->apiReturn(V(0, '保存失败'));
        } else {
            $this->apiReturn(V(1, '保存成功'));
        }
    }

    /**
     *  收益
     */
    public function getHrAccountLog() {
        $where['user_id'] = UID;
        $data = D('Admin/AccountLog')->getAccountLogByPage($where);
        $this->apiReturn(V(1, '收益明细', $data['info']));
    }
    /**
     * 我的推荐(hr)
     */
    public function getMyRecommend() {
        $recruitModel = D('Admin/Recruit');
        $data = $recruitModel->getMyRecruitByPage();
        $this->apiReturn(V(1, '我的推荐列表',$data['info']));
    }
}
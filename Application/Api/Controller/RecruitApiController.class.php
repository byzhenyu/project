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

        $model = D('Admin/Recruit');
        $position_name = M('Position')->where(array('id'=>$data['position_id']))->getField('position_name');
        $data['position_name'] = $position_name;
        //判断余额
        $data['last_token'] = $data['commission'] = yuan_to_fen($data['commission']);
        $data['get_resume_token'] = yuan_to_fen(C('GET_RESUME_MONEY'));
        $data['entry_token'] = yuan_to_fen(C('GET_ENTRY_MONEY'));
        $user_money = D('Admin/User')->getUserField(array('user_id'=>UID),'user_money');

        if ($data['commission'] > $user_money) {
            $this->apiReturn(V(0, '可用余额不足'));
        }

        $trans = M();
        $data['hr_user_id'] = UID;

        if ($model->create($data) ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, $model->getError()));
        }
        $newId = $model->add();
        if ($newId ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, '发布失败'));
        }

        //修改金额
        $res = D('Admin/User')->recruitUserMoney($data['commission']);
        if ($res['status'] ==0) {
            $trans->rollback();
            $this->apiReturn($res);
        }
        $trans->commit();
        $this->apiReturn(V(1,'发布成功'));



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
     * recruit_resume_id 悬赏简历id
     *
     */
    public function getResumePhoneNumber() {
        $recruit_id = I('recruit_id', 0, 'intval');
        $recruit_resume_id = I('recruit_resume_id', 0, 'intval');
        //判断次数
        $where['recruit_id'] = $recruit_id;
        $result = D('Admin/TokenLog')->getResumeCountRes($where);
        if ($result['status'] ==0) {
            $this->apiReturn($result);
        }
        $info = D('Admin/User')->changeUserMoney($recruit_resume_id, 1);
        if ($info['status'] ==0) {
            $this->apiReturn($info);
        } else {
            $this->apiReturn(V(1, '获取联系方式成功'));
        }

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
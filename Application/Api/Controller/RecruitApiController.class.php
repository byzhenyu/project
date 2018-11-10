<?php
namespace Api\Controller;
use Common\Controller\ApiUserCommonController;
use Think\Verify;

class RecruitApiController extends ApiUserCommonController{
    //发布悬赏页面
    public function publishPage() {
        //获取职位需要单独接口
        $data['resume'] = C('GET_RESUME_MONEY');
        $data['entry'] = C('GET_ENTRY_MONEY');
        $data['ratio'] = C('RATIO')/100; //比例
        $data['nature'] = returnArrData(C('WORK_NATURE')); //性质
        $data['sex'] = returnArrData(array('0'=>'不限','1'=>'男','2'=>'女'));
        $data['degree'] = D('Admin/Education')->getEducationList(array(),'id,education_name');//学历
        $data['experience'] = returnArrData(C('WORK_EXP')); //经验
        //地区单独接口
        $data['tags'] = D('Admin/Tags')->getTagsList(array('tags_type'=>3)); //福利标签
        foreach($data['tags'] as &$val){
            $val['sel'] = 0;
        }
        unset($val);
        $this->apiReturn(V(1 , '悬赏页面信息',$data));
    }

    //获取平均悬赏金额
    public function getCommissionValue() {
        $position_id = I('position_id',0,'intval');
        $recruit_amount = I('recruit_amount', 0, 'intval');
        $getAmount = C('GET_RESUME_MONEY');
        $radio = C('RATIO')/100; //比例
        $value = D('Admin/Recruit')->getAverageValue(array('position_id'=>$position_id));
        $entry = $recruit_amount - $getAmount;
        if($entry < 0) $entry = 0;
        $data = array('get' => $getAmount, 'avg' => $value['average'], 'entry' => $entry, 'plat' => $recruit_amount*$radio);
        $this->apiReturn(V(1,'令牌平均值', $data));
    }

    //发布接口
    public function publish() {
        if(!check_is_auth(UID)) {
            $string = auth_string();
            if(false !== $string) $this->apiReturn(V(0, $string));
            $this->apiReturn(V(0, '请先完成实名认证'));
        }
        $companyInfoModel = D('Admin/CompanyInfo');
        $checkCompanyInfo = $companyInfoModel->checkCompanyInfo(UID);
        if ($checkCompanyInfo == 0) {
            $this->apiReturn(V(0, '请先完善个人资料'));
        }
        $data = I('post.', '');
        $model = D('Admin/Recruit');
        $position_name = M('Position')->where(array('id'=>$data['position_id']))->getField('position_name');
        if(!$position_name) $this->apiReturn(V(0, '获取不到职位名称！'));
        $getResumeMoney = C('GET_RESUME_MONEY');
        $data['position_name'] = $position_name;
        if($data['commission'] <= $getResumeMoney) $this->apiReturn(V(0, '悬赏金额需要大于'.$getResumeMoney.'令牌！'));
        $regex = '/^\d+(\.\d{1,2})?$/';
        if(!preg_match($regex, $data['commission'])){
            $this->apiReturn(V(0, '悬赏令牌小数点最多两位！'));
        }
        //判断余额
        $data['last_token'] = $data['commission'] = yuan_to_fen($data['commission']);
        $data['get_resume_token'] = yuan_to_fen($getResumeMoney);
        $data['entry_token'] = $data['commission'] - $data['get_resume_token'];//入职获取令牌
        $user_money = D('Admin/User')->getUserField(array('user_id'=>UID),'user_money');

        if (($data['commission'] * $data['recruit_num']) > $user_money) {
            $this->apiReturn(V(0, '悄悄的告诉你，你的令牌不足喽。马上充值令牌，快速发布悬赏。'));
        }
        if (cmp_contraband($data['description'])) {
            $this->apiReturn(V(0, '悬赏工作描述中有违禁词！'));
        }
        $trans = M();
        if ($model->create($data, 1) ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, $model->getError()));
        }
        $newId = $model->add();
        if ($newId ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, '发布失败'));
        }

        //修改金额
        $res = D('Admin/User')->recruitUserMoney($data['commission'] * $data['recruit_num']);
        if ($res['status'] ==0) {
            $trans->rollback();
            $this->apiReturn($res);
        }
        add_key_operation(5, $newId);
        $task_id = 4;
        add_task_log(UID, $task_id);
        refreshRecruitCache();
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
        $user_id = UID;
        if ($type == 1) {
            $where['hr_user_id'] = array('eq', $user_id);
        }
        else{
            $tags = user_tags($user_id);
            $map = '';
            if(count($tags) > 0){
                $where1 = array();
                foreach($tags as &$val){
                    $val['job_area'] = rtrim($val['job_area'], ',');
                    if(false !== strpos($val['job_position'], '|')){
                        $pos = 'in ('.str_replace('|', ',', $val['job_position']).')';
                    }
                    else{
                        $pos = '= '.$val['job_position'];
                    }
                    $where1[] = ' (`job_area` like \''.$val['job_area'].'%\' and `position_id` '.$pos.') ';
                }
                unset($val);
                $map = implode(' or ', $where1);
            }
            if($map) $where['_string'] = $map;
            if(!$map) $where['_string'] = 'hr_user_id = 0';//无符合条件人选
            $where['hr_user_id'] = array('neq', $user_id);
        }
        $where['status'] = 1;
        $where['is_post'] = array('lt', 2);

        $list = D('Admin/Recruit')->getRecruitList($where,'id, position_name, recruit_num, commission, add_time,position_id');
        $position_model = D('Admin/Position');
        foreach($list['info'] as &$val){
            $t_parent_id = $position_model->getPositionField(array('id' => $val['position_id']), 'parent_id');
            $position_name = $position_model->getPositionField(array('id' => $t_parent_id), 'position_name');
            $val['position_name'] = $position_name .'-'. $val['position_name'];
        }
        unset($val);
        $this->apiReturn(V(1, '悬赏列表', $list['info']));
    }


    /**
     * 悬赏详情
     */
    public function getRecruitListDetail() {
        $id = I('id', 0,'intval');
        $info = D('Admin/Recruit')->getDetail(array('r.id'=>$id));
        $info = string_data($info);
        $info['company_name'] = D('Admin/CompanyInfo')->getCompanyInfoField(array('user_id' => $info['hr_user_id']), 'company_name');
        if(!$info['company_name']) $info['company_name'] = '暂未填写';
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
        $this->apiReturn(V(1, '推荐简历列表',$data['info']));
    }

    /**
     *  获取联系方式
     *  recruit_id 悬赏id
     * recruit_resume_id 悬赏推荐id
     *
     */
    public function getResumePhoneNumber() {
        $recruit_id = I('recruit_id', 0, 'intval');
        $recruit_resume_id = I('recruit_resume_id', 0, 'intval');
        $recruit_model = D('Admin/Recruit');
        $recruit_resume_model = D('Admin/RecruitResume');
        $resume_model = D('Admin/Resume');
        $user_model = D('Admin/User');
        $recruit_resume_open = $recruit_resume_model->getRecruitResumeField(array('id' => $recruit_resume_id), 'is_open');
        if($recruit_resume_open == 1) $this->apiReturn(V(0, '无需重复下载！'));
        $recruit_info = $recruit_model->getRecruitInfo(array('id' => $recruit_id));
        $recruit_num = $recruit_info['recruit_num'];
        $recruit_resume_count = $recruit_resume_model->getRecruitResumeNum(array('recruit_id' => $recruit_id, 'is_open' => 1));
        $get_resume_money = C('GET_RESUME_MONEY');
        $recruit_get_money = yuan_to_fen($get_resume_money);
        if($recruit_resume_count >= $recruit_num){
            $user_money = $user_model->getUserField(array('user_id' => UID), 'user_money');
            if($user_money < $recruit_get_money) $this->apiReturn(V(0, '您的余额不足，请前往充值页面充值。'));
            M()->startTrans();
            $user_money_res = $user_model->decreaseUserFieldNum(UID, 'user_money', $recruit_get_money);//冻结资金
            $account_res = account_log(UID, $recruit_get_money, 7, '补缴下载简历金额！', $recruit_resume_id);
            $user_frozen_res = $user_model->increaseUserFieldNum(UID, 'frozen_money', $recruit_get_money);
            if(false !== $user_money_res && false !== $account_res && false !== $user_frozen_res){
                M()->commit();
            }
            else{
                M()->rollback();
                $this->apiReturn(V(0, '冻结补缴金额发生错误！'));
            }
        }
        M()->startTrans();
        $recruit_res = $recruit_model->recruitPayOff($recruit_resume_id);
        $recruit_resume_res = M('RecruitResume')->where(array('id' => $recruit_resume_id))->setField('is_open', 1);
        if(false !== $recruit_res && false !== $recruit_resume_res){
            $recruitResume = $recruit_resume_model->getRecruitResumeField(array('id' => $recruit_resume_id), 'resume_id');
            $resume_info = $resume_model->getResumeInfo(array('id' => $recruitResume['resume_id']));
            if(!$resume_info['hide_mobile']){
                $hideMobile = hideMobile($resume_info['mobile']);
                if(false !== $hideMobile) M('Resume')->where(array('id' => $recruitResume['resume_id']))->setField('hide_mobile', $hideMobile);
            }
            M()->commit();
            $this->apiReturn(V(1, '恭喜你悬赏到了一个人才线索，我们将为你扣除'.$get_resume_money.'令牌用来打赏！'));
        }
        else{
            M()->rollback();
            $this->apiReturn(V(0, '简历联系方式获取错误！'));
        }

    }


    /**
     *  每日任务
     */
    public function getTaskList() {
        $info = D('Admin/Task')->getTaskList();
        $task_log = D('Admin/TaskLog');
        $task_arr = array(1 => '每日限制', 0 => '永久限制', 2 => '每周限制', 3 => '每月限制');
        foreach($info as &$val){
            $val['reward'] = fen_to_yuan($val['reward']);
            $val['can'] = intval($val['can']);
            $t_n = $task_log->validTaskNumber($val['id'], UID, true);
            $val['type_number'] = $task_arr[$val['type']].$val['type_number'].'份/已完成'.$t_n;
        }
        unset($val);
        $this->apiReturn(V(1, '每日任务', $info));
    }

    /**
     * 擅长领域页面
     * tags_type 4 擅长领域 2 求职方向
     *
     */
    public function  getLikeTags() {
        $type = I('tags_type', 4, 'intval');
        if (!in_array($type,[4,2])) {
            $this->apiReturn(V(0, '类型字段不合法'));
        }
        $all = M('Tags')->where(array('tags_type'=>$type))->order('tags_sort')->select();

        $tags = M('User')->where(array('user_id'=>UID))->getField('like_tags');

        $tagsArr = explode(',', $tags);
        foreach ($all as $k=>$v) {
            if (in_array($all[$k]['id'], $tagsArr)) {
                $all[$k]['is_select'] = 1;
            } else {
                $all[$k]['is_select'] = 0;
            }
        }
        $this->apiReturn(V(1, '页面信息', $all));
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
     *  type 0 全部 1 收益明细
     */
    public function getHrAccountLog() {
        $type = I('type', 0 , 'intval');
        $where['user_id'] = UID;
        if ($type == 1) $where['change_type'] = array('in', [2,3,6]);
        $data = D('Admin/AccountLog')->getAccountLogByPage($where,'log_id,user_id,user_money,change_time,change_desc,change_type,order_sn');
        $account_model = D('Admin/UserAccount');
        if ($type == 1) {
            $where['change_type'] = array('in', [2,3,6]);
            $info['list'] = $data['info'];
            $info['statistics'] = D('Admin/AccountLog')->getAccountMsg($where);
        } else {
            $info = $data['info'];
            foreach($info as &$val){
                if($val['change_type'] != 1) continue;
                $val['user_account_state'] = $account_model->getAccountField(array('id' => $val['order_sn']), 'state');
                $val['change_desc'] = '提现 - '.C('ACCOUNT_STATE')[$val['user_account_state']];
            }
            unset($val);
        }

        $this->apiReturn(V(1, '收益明细', $info));
    }
    /**
     * 我的推荐(hr)
     */
    public function getMyRecommend() {
        $recruitModel = D('Admin/Recruit');
        $data = $recruitModel->getMyRecruitByPage();
        $this->apiReturn(V(1, '我的推荐列表',$data['info']));
    }

    /**
     * 我的推荐 - 推荐简历
     */
    public function getMyRecommendResume() {
        $recruit_id = I('recruit_id', 0,'intval');
        $where['recruit_id'] = array('eq', $recruit_id);
        $where['hr_user_id'] = array('eq', UID);
        $data = D('Admin/RecruitResume')->getResumeListByPage($where);
        $interviewModel = D('Admin/Interview');
        foreach ($data['info'] as $k=>$v) {
            $data['info'][$k]['state'] = $interviewModel->getInterviewStatus(array('recruit_resume_id'=>$v['id']));
        }

        $this->apiReturn(V(1, '我的推荐推荐简历列表',$data['info']));
    }
    /**
     * 编辑资料(hr)
     */
    public function editUserInfo() {
        $id = I('id', 0, 'intval');
        $data = I('post.', '');

        $data['user_id'] = UID;
        $userData['user_id'] = UID;
        $userData['nickname'] = $data['nickname'];
        $userData['sex'] = $data['sex'];
        $userData['head_pic'] = $data['head_pic'];
        $userModel = D('Admin/User');
        $companyInfoModel = D('Admin/CompanyInfo');
        $trans = M();
        if ($userModel->create($userData,4) ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, $userModel->getError()));
        }
        $res = $userModel->save();
        if ($res ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, '个人信息保存失败'));
        }

        //公司信息
        if ($companyInfoModel->create($data) ===false) {

            $trans->rollback();
            $this->apiReturn(V(0, $companyInfoModel->getError()));
        }
        if ($id > 0) {
            $infoRes = $companyInfoModel->save();

        } else {
            $infoRes = $companyInfoModel->add();
        }

        if ($infoRes ===false) {
            $trans->rollback();
            $this->apiReturn(V(0, '公司信息保存失败'));
        }

        $trans->commit();
        $this->apiReturn(V(1, '保存成功'));

    }

    /**
     *  编辑页面
     */
    public function getUserInfo() {
        $array = array('id','user_id','company_name','company_size','company_nature','company_mobile','company_email','company_industry','company_address', 'head_pic', 'nickname', 'sex');
        $info = D('Admin/CompanyInfo')->getHrInfo(array('c.user_id'=>UID));
        $address = explode(' ' ,$info['company_address']);
        if(count($address) > 0){
            $info['company_address_p'] = $address[0];
            unset($address[0]);
            $info['company_address'] = str_replace($info['company_address_p'].' ', '', $info['company_address']);
        }
        else{
            $info['company_address_p'] = '';
        }
        if(!$info) {
            foreach($array as &$value) $info[$value] = '';
            $info['company_pic'] = array();
            $info['company_address_p'] = '';
        }
        $this->apiReturn(V(1 ,'编辑个人资料',$info));
    }

    /**
     * @desc 上传头像
     * @param photo file 图片文件
     */
    public function uploadHeadPicture(){
        if (!empty($_FILES['photo'])) {
            $img = app_upload_img('photo', '', 'User');
            if (0 === $img) {
                $this->apiReturn(V(0, '上传失败'));
            } else if (-1 === $img){
                $this->apiReturn(V(0, '上传失败'));
            } else {
                thumb($img, 180, 240);
                $this->apiReturn(V(1, '上传成功', $img));
            }
        }
        else{
            $this->apiReturn(V(0, '上传失败', $_FILES));
        }
    }
    /**
     * 上传公司环境
     */
    public function uploadMorePicture() {
        $img_url = [];
        if (!empty($_FILES['photo'])) {

            $photo = $_FILES['photo'];

            foreach ($photo['name'] as $key => $value) {
                $res= app_upload_more_img('photo', '', 'CompanyInfo', UID, $key);
                if ($res !== -1 && $res !== 0) {
                    $img_url[] = $res;
                }

            }

            if (empty($img_url)) {
                $this->apiReturn(V(0, '上传失败'));
            }
            $this->apiReturn(V(1, '上传成功', $img_url));
        }
        $this->apiReturn(V(0, '上传失败'));

    }

    /**
     * 上传图片通用
     */
    public function uploadPicture() {
        if (!empty($_FILES['photo'])) {
            $img = app_upload_img('photo', '', 'CompanyInfo');
            if (0 === $img) {
                $this->apiReturn(V(0, '上传失败'));
            } else if (-1 === $img){
                $this->apiReturn(V(0, '上传失败'));
            } else {
                thumb($img, 180, 240);
                $this->apiReturn(V(1, '上传成功', $img));
            }
        }
        else{
            $this->apiReturn(V(0, '上传失败', $_FILES));
        }
    }

    /**
     * 阿里云oss直传
     */
    function gmt_iso8601($time) {

        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }
    //oss签名
    public function getOssSign() {
        date_default_timezone_set("Asia/Shanghai");
        $config = C('AliOss');
        $id= $config['accessKeyId'];
        $key= $config['accessKeySecret'];
        $host = 'https://'.$config["bucket"].'.'.$config["endpoint"];
        $callbackUrl = $config['callbackUrl'];

        $callback_param = array('callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);

        $dir = 'photo/';

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start;


        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        //echo json_encode($arr);
        //return;
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['OSSAccessKeyId'] = $id;
        $response['expire'] = $end;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['callback'] = $base64_callback_body;
        //这个参数是设置用户上传指定的前缀
        //$response['dir'] = $dir;
        $response['key'] = $dir;
        //echo json_encode($response);
        $this->apiReturn(V(1, 'aliyun oss sign', $response));
    }

    //悬赏分享
    public function getShareInfo() {
        $recruit_id = I('recruit_id',0,'intval');
        $info = D('Admin/Recruit')->getRecruitInfo(array('id'=>$recruit_id),'id,position_id,position_name,recruit_num,commission,hr_user_id');
        $info['commission'] = fen_to_yuan($info['commission']);
        $info['company_name'] = M('CompanyInfo')->where(array('user_id'=>$info['hr_user_id']))->getField('company_name');
        $this->apiReturn(V(1,'悬赏分享',$info));
    }

    /**
     * @desc 删除悬赏/有推荐不可删除
     */
    public function delRecruit(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/Recruit');
        $info = $model->getRecruitInfo(array('id' => $id, 'hr_user_id' => UID));
        if(!$info) $this->apiReturn(V(0, '获取不到对应的悬赏信息！'));
        $recruit_resume = D('Admin/RecruitResume')->getRecruitResumeNum(array('recruit_id' => $id));
        if($recruit_resume > 0) $this->apiReturn(V(0, '该悬赏下有推荐简历,不可删除！'));
        $res = $model->where(array('id' => $id))->save(array('status' => 0));
        if(false !== $res){
            $this->apiReturn(V(1, '悬赏信息删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '悬赏信息删除失败！'));
        }
    }

    /**
     * @desc 编辑悬赏职位信息
     */
    public function editRecruit(){
        $data = I('post.');
        $model = D('Admin/Recruit');
        $info = $model->getRecruitInfo(array('id' => $data['id'], 'hr_user_id' => UID));
        if(!$info) $this->apiReturn(V(0, '获取不到对应的悬赏信息！'));
        $create = $model->create($data, 4);
        if(false !== $create){
            $res = $model->where(array('id' => $data['id']))->save($data);
            if(false !== $res){
                $this->apiReturn(V(1, '保存成功！'));
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 数据暂存
     */
    public function cacheRecruit(){
        $data = I('post.', '');
        $model = D('Admin/RecruitCache');
        $field = $model->getDbFields();
        unset($field[0]);
        unset($field[1]);
        $add = array();
        $count_num = 0;
        foreach($field as &$val){
            $add[$val] = $data[$val];
            if(!empty($data[$val])) $count_num++;
        }
        unset($val);
        $add['hr_user_id'] = UID;
        if($count_num > 0){
            $res = $model->add($add);
            if($res)$this->apiReturn(V(1, '暂存成功！'));
            $this->apiReturn(V(0, '暂存失败'));
        }
        else{
            $this->apiReturn(V(0, '没有可暂存数据！'));

        }
    }

    /**
     * @desc 悬赏暂存数据
     */
    public function getRecruitCacheInfo()
    {
        $where = array('hr_user_id' => UID);
        $model = D('Admin/RecruitCache');
        $cache = $model->getRecruitCacheInfo($where);
        if (!$cache) {
            $field = $model->getDbFields();
            unset($field[0]);
            unset($field[1]);
            $data = array();
            foreach ($field as &$val) {
                $data[$val] = '';
            }
            unset($val);
        } else {
            $data = $cache;
        }
        $this->apiReturn(V(1, '', $data));
    }

    public function getRecruitShareInfo(){
        $recruit_id = I('recruit_id', 0, 'intval');
        $model = D('Admin/Recruit');
        $where = array('id' => $recruit_id);
        $info = $model->getRecruitInfo($where, 'id,position_name,job_area,base_pay,merit_pay,commission,recruit_num');
        $company_name = D('Admin/CompanyInfo')->getCompanyInfoField(array('user_id' => UID), 'company_name');
        $info['company_name'] = $company_name;
        $info['position_name'] = '招聘'. $info['position_name'];
        $info['base_pay'] = $info['base_pay'].'-'. ($info['base_pay'] + $info['merit_pay']).'/月';
        $info['commission'] = fen_to_yuan($info['recruit_num'] * $info['commission']);
        $this->apiReturn(V(1, '', $info));
    }

    /**
     * @desc 悬赏分享
     */
    public function shareRecruit(){
        $id = I('recruit_id', 0, 'intval');
        $res = D('Admin/Recruit')->where(array('id' => $id))->setInc('share');
        if(false !== $res) $this->apiReturn(V(1, ''));
        $this->apiReturn(V(0, ''));
    }
}
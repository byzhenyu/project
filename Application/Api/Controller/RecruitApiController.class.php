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
        $value = D('Admin/Recruit')->getAverageValue(array('position_id'=>$position_id));

        $this->apiReturn(V(1,'令牌平均值',$value));
    }

    //发布接口
    public function publish() {
        if(!check_is_auth(UID)) {
            $this->apiReturn(V(0, '发布悬赏需要实名认证'));
        };
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
            $this->apiReturn(V(0, '可用令牌不足'));
        }
        if (cmp_contraband($data['language_ability'])) {
            $this->apiReturn(V(0, '语言要求有违禁词'));
        }
        if (cmp_contraband($data['description'])) {
            $this->apiReturn(V(0, '详情描述有违禁词'));
        }
        if (cmp_contraband($data['base_pay'])) {
            $this->apiReturn(V(0, '基本工资有违禁词'));
        }
        if (cmp_contraband($data['merit_pay'])) {
            $this->apiReturn(V(0, '绩效工资有违禁词'));
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
        add_key_operation(5, $newId);
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
        if ($type == 1) {
            $where['hr_user_id'] = array('eq', UID);
        }

        $list = D('Admin/Recruit')->getRecruitList($where,'id, position_name, recruit_num, commission, add_time');
        $this->apiReturn(V(1, '悬赏列表', $list['info']));
    }


    /**
     * 悬赏详情
     */
    public function getRecruitListDetail() {
        $id = I('id', 0,'intval');
        $info = D('Admin/Recruit')->getDetail(array('r.id'=>$id));
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
            //修改状态字段
            M('RecruitResume')->where(array('id'=>$recruit_resume_id))->setField('is_open', 1);
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
        $data = D('Admin/AccountLog')->getAccountLogByPage($where,'log_id,user_id,user_money,change_time,change_desc');
        if ($type == 1) {
            $where['change_type'] = array('in', [2,3]);
            $info['list'] = $data['info'];
            $info['statistics'] = D('Admin/AccountLog')->getAccountMsg($where);
        } else {
            $info = $data['info'];
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
        // $data = array(
        //         'nickname' => '李富强',
        //         'company_name' => '山东六牛网络科技有限公司',
        //         'company_nature' => '有限责任公司',
        //         'company_mobile' => '15098913873',
        //         'company_email' => '772486847@qq.com',
        //         'company_industry' => '互联网类',
        //         'company_address' => '沃尔沃路',
        //         'sex' => 2,
        //         'company_size' => '50-100人'
        //     );
        $data['user_id'] = UID;
        $userData['user_id'] = UID;
        $userData['nickname'] = $data['nickname'];
        $userData['sex'] = $data['sex'];
        $userData['head_pic'] = $data['head_pic'];
        $userModel = D('Admin/User');
        $companyInfoModel = D('Admin/CompanyInfo');
        //$trans = M();
        if ($userModel->create($userData,4) ===false) {
            //$trans->rollback();
            $this->apiReturn(V(0, $userModel->getError()));
        }
        $res = $userModel->save();
        if ($res ===false) {
            //$trans->rollback();
            $this->apiReturn(V(0, '个人信息保存失败'));
        }
        //公司信息
        if ($companyInfoModel->create($data) ===false) {
            //$trans->rollback();
            $this->apiReturn(V(0, $companyInfoModel->getError()));
        }
        if ($id > 0) {
            $infoRes = $companyInfoModel->save();
        } else {
            $infoRes = $companyInfoModel->add($data);
            
        }
        $this->apiReturn(V(1, '保存成功', $companyInfoModel->getLastSql()));

        // if ($infoRes ===false) {
        //     //$trans->rollback();
        //     $this->apiReturn(V(0, '公司信息保存失败'));
        // }

        //$trans->commit();
       // $this->apiReturn(V(1, '保存成功'));

    }

    /**
     *  编辑页面
     */
    public function getUserInfo() {
        $array = array('id','user_id','company_name','company_size','company_nature','company_mobile','company_email','company_industry','company_address', 'head_pic', 'nickname', 'sex');
        $info = D('Admin/CompanyInfo')->getHrInfo(array('c.user_id'=>UID));
        if(!$info) {
            foreach($array as &$value) $info[$value] = '';
            $info['company_pic'] = array();
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
}
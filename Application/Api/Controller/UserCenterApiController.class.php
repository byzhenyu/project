<?php
namespace Api\Controller;
use Common\Controller\ApiUserCommonController;
use Think\Verify;

class UserCenterApiController extends ApiUserCommonController{

    /**
     * @TODO
     * @desc 编辑个人资料
     */
    public function editUserInfo(){
        $data = I('post.');
        $nickname = I('nickname', '', 'trim');
        $nickNameLength = mb_strlen($nickname, 'utf-8');
        if($nickNameLength > 11){
            $this->apiReturn(V(0, '昵称不能超过11个字符'));
        }
        $saveData = array();
        $img = app_upload_img('photo', '', '', 'User');
        if (!empty($_FILES['photo'])) {
            if ($img == 0 || $img == -1) {
                $this->apiReturn(V(0, '头像上传失败'));
            }
            else{
                $saveData['head_pic'] = $img;
            }
        }
        $saveData['nickname'] = $nickname;
        $where = array('user_id' => UID);
        $result = D('Admin/User')->saveUserData($where, $saveData);
        if(false !== $result) $this->apiReturn(V(1, '保存成功'));
        $this->apiReturn(V(0, '操作失败,请稍后重试！'));
    }

    /**
     * @desc  用户修改密码
     * @param password string 用户密码
     * @param new_password string 新密码
     * @param re_password string 确认新密码
     */
    public function settingUserPwd(){
        $where = array('user_id' => UID);
        $password = I('password');
        $newPassword = I('new_password');
        $rePassword = I('re_password');
        if(!$password || !$newPassword) $this->apiReturn(V(0, '请输入密码！'));
        $passLen = strlen($newPassword);
        if($passLen < 6 || $passLen > 18) $this->apiReturn(V(0, '密码长度支持6-18位！'));
        if($newPassword != $rePassword) $this->apiReturn(V(0, '两次新密码不一致！'));
        $model = D('Admin/User');
        $userInfo = $model->getUserInfo($where, 'password');
        if(!pwdHash($password, $userInfo['password'], true)) $this->apiReturn(V(0, '原密码输入不正确！'));
        $data = $model->saveUserData($where, array('password' => $newPassword));//before_update的问题
        if(false !== $data){
            $this->apiReturn(V(1, '密码修改成功！'));
        }
        else{
            $this->apiReturn(V(0, '服务器繁忙，请稍后重试！'));
        }
    }

    /**
     * @desc 设置支付密码
     */
    public function saveUserPayPassword(){
        $mobile = I('mobile');
        $sms_code = I('sms_code', 0, 'intval');
        $pay_word = I('pay_password', '', 'trim');
        $model = D('Admin/User');
        $where = array('user_id' => UID);
        $userInfo = $model->getUserInfo($where);
        $user_type = $userInfo['user_type'];
        if(!isMobile($mobile)) $this->apiReturn(V(0, '请输入合法的手机号！'));
        $payLen = strlen($pay_word);
        if($payLen < 6 || $payLen > 18) $this->apiReturn(V(0, '密码长度6-18位！'));
        $valid = D('Admin/SmsMessage')->checkSmsMessage($sms_code, $mobile, $user_type, 6);
        if(!$valid['status']) $this->apiReturn($valid);
        $model = D('Admin/User');
        $where = array('user_id' => UID);
        $data = $model->saveUserData($where, array('pay_password' => pwdHash($pay_word)));
        if(false !== $data){
            $this->apiReturn(V(1, '支付密码设置成功！'));
        }
        else{
            $this->apiReturn(V(0, '支付密码设置失败！'));
        }
    }

    /**
     * @desc 上传身份认证凭证
     */
    public function userAuthUpload(){
        $model = D('Admin/User');
        $where = array('user_id' => UID);
        $userInfo = $model->getUserInfo($where);
        $user_type = $userInfo['user_type'];
        $user_auth = $userInfo['is_auth'];
        if($user_auth) $this->apiReturn(V(0, '身份验证已经通过！'));
        $authModel = D('Admin/UserAuth');
        $data = I('post.');
        $upArray = array();
        if(1 == $user_type){
            if(empty($_FILES['business_license'])) $this->apiReturn(V(0, '请上传营业执照！'));
            $business = app_upload_img('business_license', '', '', 'User');
            $upArray['business_license'] = $business;
        }
        $array = array('idcard_up' => '请上传身份证正面照！', 'idcard_down' => '请上传身份证反面照！', 'hand_pic' => '请上传手持身份证照！');
        $keys = array_keys($array);
        foreach($keys as &$val){
            if(empty($_FILES[$val])) $this->apiReturn(V(0, $array[$val]));
            $$val = app_upload_img($val, '', '', 'User');
            $upArray[$val] = $$val;
        }
        $upKeys = array_keys($upArray);
        foreach($upKeys as &$value){
            if($upArray[$value] == 0 || $upArray[$value] == -1){
                $tempUpload = '营业执照';
                $t = $array[$value];
                $t = str_replace('请上传', '', $t);
                $t = str_replace('！', '', $t);
                if(!$array[$value]) $t = $tempUpload;
                $this->apiReturn(V(0, $t.'上传失败！'));
            }
        }
        $data = array_merge($data, $upArray);
        $create = $authModel->create($data);
        if(false !== $create){
            $this->apiReturn(V(1, '身份验证凭据上传成功！'));
        }
        else{
            $this->apiReturn(V(0, $authModel->getError()));
        }
    }

    /**
     * @desc 发布问题
     */
    public function releaseQuestion(){
        $user_id = UID;
        $data = I('post.');
        $data['user_id'] = $user_id;
        $model = D('Admin/Question');
        $create = $model->create($data);
        if(false !== $create){
            $question_id = $model->add($data);
            //评论图片处理
            $photo = $_FILES['photo'];
            $questionImgModel = D('Admin/QuestionImg');
            if ($photo) {
                foreach ($photo['name'] as $key => $value) {
                    $img_url = app_upload_more_img('photo', '', 'Comment', UID, $key);
                    $data_img['item_id'] = $question_id;
                    $data_img['img_path'] = $img_url;
                    $questionImgModel->add($data_img);
                    thumb($img_url, 180,240);
                }
            }
            $this->apiReturn(V(1, '问题发布成功！'));
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 发布答案
     */
    public function releaseAnswer(){
        $user_id = UID;
        $data = I('post.');
        $data['user_id'] = $user_id;
        $model = D('Admin/Answer');
        $create = $model->create($data);
        if(false !== $create){
            $answer_id = $model->add($data);
            if(!$answer_id)$this->apiReturn(V(0, $model->getError()));
            //评论图片处理
            $photo = $_FILES['photo'];
            $questionImgModel = D('Admin/QuestionImg');
            if ($photo) {
                foreach ($photo['name'] as $key => $value) {
                    $img_url = app_upload_more_img('photo', '', 'Comment', UID, $key);
                    $data_img['item_id'] = $answer_id;
                    $data_img['img_path'] = $img_url;
                    $data_img['type'] = 2;
                    $questionImgModel->add($data_img);
                    thumb($img_url, 180,240);
                }
            }
            $incWhere = array('id' => $data['question_id']);
            D('Admin/Question')->setQuestionInc($incWhere, 'answer_number');//问题回答数
            $this->apiReturn(V(1, '回答成功！'));
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 获取问题详情
     */
    public function getQuestionDetail(){
        $question_id = I('question_id', 0, 'intval');
        $where = array('id' => $question_id, 'disabled' => 1);
        $quesModel = D('Admin/Question');
        $questionDetail = $quesModel->getQuestionDetail($where);
        $releaseInfo = D('Admin/User')->getUserInfo(array('user_id' => $questionDetail['user_id']));
        $questionDetail['add_time'] = time_format($questionDetail['add_time']);
        $questionDetail['head_pic'] = strval($releaseInfo['head_pic']);
        $questionDetail['nickname'] = strval($releaseInfo['nickname']);
        $ques_img_where = array('type' => 1, 'item_id' => $question_id);
        $questionImg = D('Admin/QuestionImg')->getQuestionImgList($ques_img_where);
        $answer_where = array('question_id' => $question_id);
        $answerModel = D('Admin/Answer');
        $answer_list = $answerModel->getAnswerList($answer_where);
        $questionPointsModel = D('Admin/QuestionPoints');
        $points_where = array('item_id' => $question_id, 'type' => 1, 'operate_type' => 2, 'user_id' => UID);
        $points_info = $questionPointsModel->getQuestionPointsInfo($points_where);
        if(!$points_info){
            $quesModel->setQuestionInc($where, 'browse_number');
            $questionPointsModel->add($points_where);
        }
        $returnArray = array('question' => $questionDetail, 'question_img' => $questionImg, 'answer_list' => $answer_list['info']);
        $this->apiReturn(V(1, '问题详情获取成功！', $returnArray));
    }

    /**
     * @desc 问题点赞
     */
    public function likeQuestion(){
        $data = I('post.');
        $data['user_id'] = UID;
        $model = D("Admin/QuestionPoints");
        $where = array(
            'item_id' => $data['item_id'],
            'user_id' => $data['user_id'],
            'operate_type' => 1,
            'type' => 1
        );
        $info = $model->getQuestionPointsInfo($where);
        if($info) $this->apiReturn(V(0, '您已经对该问题点过赞！'));
        M()->startTrans();
        $create = $model->create($data);
        if(false !== $create){
            $res = $model->add($data);
            if(false !== $res){
                $incWhere = array('id' => $data['item_id']);
                $qRes = D('Admin/Question')->setQuestionInc($incWhere, 'like_number');
                if(false !== $qRes){
                    $model->add($where);
                    M()->commit();
                    $this->apiReturn(V(1, '点赞成功！'));
                }
                else{
                    M()->rollback();
                    $this->apiReturn(V(0, '点赞失败！'));
                }
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
     * @desc 回答点赞
     */
    public function likeAnswer(){
        $data = I('post.');
        $data['user_id'] = UID;
        if(!$data['type']) $data['type'] = 2;
        $model = D("Admin/QuestionPoints");
        $where = array(
            'item_id' => $data['item_id'],
            'user_id' => $data['user_id'],
            'operate_type' => 1,
            'type' => 2
        );
        $info = $model->getQuestionPointsInfo($where);
        if($info) $this->apiReturn(V(0, '您已经对该回答点过赞！'));
        M()->startTrans();
        $create = $model->create($data);
        if(false !== $create){
            $res = $model->add($data);
            if(false !== $res){
                $incWhere = array('id' => $data['item_id']);
                $qRes = D('Admin/Answer')->setAnswerInc($incWhere, 'like_number');
                if(false !== $qRes){
                    $model->add($where);
                    M()->commit();
                    $this->apiReturn(V(1, '点赞成功！'));
                }
                else{
                    M()->rollback();
                    $this->apiReturn(V(0, '点赞失败！'));
                }
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
     * @desc 设置答案为最佳答案
     */
    public function settingAnswerOptimum(){
        $answer_id = I('answer_id', 0, 'intval');
        $question_id = I('question_id', 0, 'intval');
        if(!$answer_id || !$question_id) $this->apiReturn(V(0, '请传入合法的参数！'));
        $where = array('id' => $answer_id, 'question_id' => $question_id, 'user_id' => UID);
        $res = D('Admin/Answer')->settingOptimum($where);
        $this->apiReturn($res);
    }

    /**
     * @desc 我的提问列表
     */
    public function getPersonalQuestion(){
        $where = array('a.user_id' => UID, 'a.disabled' => 1);
        $model = D('Admin/Question');
        $field = 'u.nickname,u.head_pic,a.id,a.like_number,a.browse_number,a.answer_number,a.add_time,a.question_title';
        $question = $model->getQuestionList($where, $field);
        $question_list = $question['info'];
        foreach($question_list as &$val){
            $val['nickname'] = strval($val['nickname']);
            $val['head_pic'] = strval($val['head_pic']);
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d');
            $img_where = array('type' => 1, 'item_id' => $val['id']);
            $val['question_img'] = D('Admin/QuestionImg')->getQuestionImgList($img_where);
        }
        unset($val);
        $this->apiReturn(V(1, '获取成功！', $question_list));
    }

    /**
     * @desc 我的回答列表
     */
    public function getPersonalAnswer(){
        $where = array('a.user_id' => UID);
        $model = D('Admin/Answer');
        $answer_field = 'a.id,a.answer_content,a.add_time,a.question_id,a.is_anonymous,u.nickname,u.head_pic';
        $answer = $model->getAnswerList($where, $answer_field);
        $answerList = $answer['info'];
        $ques_model = D('Admin/Question');
        $ques_img_model = D('Admin/QuestionImg');
        $ques_field = 'id,question_title,question_content,question_type,like_number,browse_number,answer_number,add_time';
        foreach($answerList as &$val){
            $t_ques_where = array('id' => $val['question_id']);
            $val['question_detail'] = $ques_model->getQuestionDetail($t_ques_where, $ques_field);
            $val['question_detail']['add_time'] = time_format($val['question_detail']['add_time'], 'Y-m-d');
            $img_where = array('type' => 1, 'item_id' => $val['question_id']);
            $val['question_img'] = $ques_img_model->getQuestionImgList($img_where);
        }
        $this->apiReturn(V(1, '', $answerList));
    }

    /**
     * @desc 联系人关系列表
     */
    public function getContactsRelationList(){
        $model = D('Admin/ContactsRelation');
        $list = $model->getContactsRelationList();
        if($list){
            $this->apiReturn(V(1, '联系人关系列表获取成功！', $list['info']));
        }
        else{
            $this->apiReturn(V(0, '获取联系人关系列表失败！'));
        }
    }

    /**
     * @desc 获取联系人列表
     */
    public function getContactsList(){
        $where = array('user_id' => UID);
        $model = D('Admin/Contacts');
        $list = $model->getContactsList($where);
        if($list){
            $this->apiReturn(V(1, '联系人列表获取成功！', $list['info']));
        }
        else{
            $this->apiReturn(V(0, '联系人列表获取失败！'));
        }
    }

    /**
     * @desc 紧急联系人添加/编辑
     */
    public function editContacts(){
        $data = I('post.');
        $data['user_id'] = UID;
        $model = D('Admin/Contacts');
        if($data['id'] > 0){
            $create = $model->create($data, 2);
            if(false !== $create){
                $res = $model->save($data);
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
        else{
            $create = $model->create($data, 1);
            if(false !== $create){
                $res = $model->add($data);
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
    }

    /**
     * @desc 获取紧急联系人详情
     */
    public function getContactsInfo(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/Contacts');
        $res = $model->getContactsInfo($where);
        if($res){
            $this->apiReturn(V(1, '联系人详情获取成功！', $res));
        }
        else{
            $this->apiReturn(V(0, '联系人详情获取失败！'));
        }
    }

    /**
     * @desc 删除紧急联系人
     */
    public function deleteContacts(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/Contacts');
        $del = $model->delContacts($where);
        if(false !== $del){
            $this->apiReturn(V(1, '删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '删除失败！'));
        }
    }

    /**
     * @desc 获取行业/职位信息列表
     */
    public function getPositionIndustryList(){
        $type = I('type', 1, 'intval');
        $parent_id = I('parent_id', 0, 'intval');
        $where = array('parent_id' => $parent_id);
        switch ($type){
            case 1:
                $model = D('Admin/Industry');
                $field = 'id,industry_name as name,parent_id,sort';
                $list = $model->getIndustryList($where, $field);
                break;
            case 2:
                $model = D('Admin/Position');
                $field = 'id,position_name as name,parent_id,sort';
                $list = $model->getPositionList($where, $field, '', false);
                break;
            default:
                $this->apiReturn(V(0, '不合法的数据类型！'));
        }
        $this->apiReturn(V(1, '列表信息获取成功！', $list));
    }

    /**
     * @desc 获取标签
     */
    public function getTags(){
        $type = I('type', 0, 'intval');
        if(!in_array($type, array(1,2,3,4,5))) $this->apiReturn(V(0, '标签类型不合法！'));
        $model = D('Admin/Tags');
        $where = array('tags_type' => $type);
        $list = $model->getTagsList($where);
        $this->apiReturn(V(1, '标签列表获取成功！', $list));
    }

    /**
     * @desc 获取公司列表
     */
    public function getCompanyList(){
        $keywords = I('keywords', '', 'trim');
        $where = array('company_name' => array('like', '%'.$keywords.'%'));
        $list = D('Admin/Company')->getCompanyList($where);
        $this->apiReturn(V(1, '公司列表获取成功！', $list['info']));
    }
}
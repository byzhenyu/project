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
        if(!isMobile($mobile)) $this->apiReturn(V(0, '请输入合法的手机号！'));
        $payLen = strlen($pay_word);
        if($payLen < 6 || $payLen > 18) $this->apiReturn(V(0, '密码长度6-18位！'));
        $valid = D('Admin/SmsMessage')->checkSmsMessage($sms_code, $mobile);
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
            $this->apiReturn(V(1, '回答成功！'));
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 联系人关系列表
     */
    public function getContactsRelationList(){
        $model = D('Admin/ContactsRelation');
        $list = $model->getgetContactsRelationList();
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
        $model = D('Admin/Contacts');
        if($data['id'] > 0){
            $create = $model->create($data);
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
            $create = $model->create($data);
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
}
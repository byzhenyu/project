<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/27 0027 18:47
 * @CreateBy       PhpStorm
 */

namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class UserAccountController extends HrCommonController{
    protected function _initialize()
    {
        $this->TransferAccount = D("Admin/UserAccount");
        $this->User = D("Hr/User");
    }
    /**
    * @desc  获取充值信息
    * @param  HR_ID
    * @return mixed
    */
    public function getAccount(){
        $where['u.user_id'] = HR_ID;
        $list = $this->TransferAccount->getUserAccountList($where);
        $SysBankModel = D("Admin/SysBank");
        $SysBankList = $SysBankModel->getBankList();
        $userMoney =  $this->User->where(array( 'user_id' => HR_ID))->getField('user_money');
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->SysBankList = $SysBankList['info'];
        $this->userMoney = $userMoney;
        $this->display();
    }
    /**
     * @desc  上传凭证
     */
    public function uploadImg(){
        $config = array(
            'rootPath' => '.'.C('UPLOAD_PICTURE_ROOT').'/Voucher/',
            'savePath' => HR_ID.'/',
            'maxSize' => C('UPLOAD_SIZE'),
            'exts' => 'jpg,jpeg,png,gif',
        );
        $Upload = new \Think\Upload($config);
        $info = $Upload->upload();

        if ($info === false) {
            $this->ajaxReturn(array('status' => 0, 'msg' => $Upload->getError()));
        } else {
            vendor('Alioss.autoload');
            $config=C('AliOss');

            $oss=new \OSS\OssClient($config['accessKeyId'],$config['accessKeySecret'],$config['endpoint']);
            $bucket=$config['bucket'];

            // 返回成功信息
            foreach($info as $file){
                $path = '.'.C('UPLOAD_PICTURE_ROOT').'/Voucher/'.$file['savepath'].$file['savename'];

                $oss_path = trim($path, './');
                $local_path = trim($path, '.');
                $oss->uploadFile($bucket,$oss_path,$path);
                unlink('.'.C('UPLOAD_PICTURE_ROOT').'/Voucher/'.$file['savepath'].$file['savename']);
                $data['status'] = 1;
                $data['src'] ='http://'.$bucket.'.'.$config['endpoint'].'/'.$oss_path;
                $data['name'] =$local_path;

            }

            $this->ajaxReturn($data);
        }
    }
    /**
    * @desc 上传凭证
    * @param
    * @return mixed
    */
    public function voucher(){
        if(IS_POST){
            P(I('post.'));
        }
        $this->display();
    }
    /**
     * 删除oss上指定文件
     * @param  string $object 文件路径 例如删除 /Public/README.md文件  传Public/README.md 即可
     */
    public function oss_delet_object(){

        // 实例化oss类
        $files = I('img_src', '','trim');
        $object = explode('com/',$files)[1];
        vendor('Alioss.autoload');
        $config=C('AliOss');
        $oss=new \OSS\OssClient($config['accessKeyId'],$config['accessKeySecret'],$config['endpoint']);
        $bucket=$config['bucket'];
        $oss->deleteObject($bucket,$object);
        $this->ajaxReturn(V(1, '删除成功'));
    }
    //账单列表
    public function listUserAccount(){

    }

    // 放入回收站
    public function del(){
        $this->_del('UserAccount', 'id');
    }
}
<?php

/**
 * Created by PhpStorm.
 * User: jipingzhao
 * Date: 6/29/17
 * Time: 9:14 AM
 * 控制器基类
 */
namespace Common\Controller;
use Common\Controller\CommonController;

class UserCommonController extends CommonController
{
    public function __construct()
    {
        parent::__construct();

        if( ! UID ){// 还没登录 跳转到登录页面
            $this->redirect('/user-login');
        }
        // 禁用刷新就下线
        $where['user_id'] = array('eq', UID);
        $disabled = M('User')->where($where)->getfield('disabled');
        unset($where);
        if ($disabled == 0) {
            session(null);
            $this->redirect('/user-login');
        }
    }

    // 上传图片
    public function _uploadImg(){
        //处理手机端因为url未变时框架会读取本地缓存的图片数据，导致修改的图片未更新的问题(导致原因：修改图片时只是修改了资源，)
        //$oldImg = I('oldImg', '', 'htmlspecialchars');
        $oldImg = '';
        $savePath = I('savePath', '', 'htmlspecialchars');
        if($savePath != '') $savePath = $savePath . '/';

        $result = array( 'status' => 1, 'msg' => '上传完成');
        //判断有没有上传图片
        //p(trim($_FILES['photo2']['name']));
        if(trim($_FILES['photo']['name']) != ''){
            $upload = new \Think\Upload(C('PICTURE_UPLOAD')); // 实例化上传类
            $upload->replace  = true; //覆盖
            $upload->savePath = $savePath; //定义上传目录
            //如果有上传名, 用原来的名字
            if($oldImg != '') $upload->saveName = $oldImg;
            // 上传文件
            $info = $upload->uploadOne($_FILES['photo']);
            if(!$info) {
                $result = array( 'status' => 0, 'msg' => $upload->getError() );
            }else{
                if ($oldImg != '') {
                    //删除缩略图
                    $dir = '.'.C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'];
                    $filesnames = dir($dir);
                    while($file = $filesnames->read()){
                        if ((!is_dir("$dir/$file")) AND ($file != ".") AND ($file != "..")) {
                            $count = strpos($file, $oldImg.'_');
                            if ($count !== false) {
                                if (file_exists("$dir/$file") == true) {
                                    @unlink("$dir/$file");
                                }
                            }
                        }
                    }
                    $filesnames->close();
                }
                $result['src'] = C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'] . $info['savename'];
            }
            $this->ajaxReturn($result);
        }
    }

    // 删除图片
    public function _delFile(){

        $file = I('file', '', 'htmlspecialchars');

        $result = array( 'status' => 1, 'msg' => '删除完成');

        if($file != ''){
            $file = './' . __ROOT__ . $file;

            if (file_exists($file) == true) {
                @unlink($file);
            }
        }
        $this->ajaxReturn($result);
    }

}

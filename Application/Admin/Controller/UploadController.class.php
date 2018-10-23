<?php
namespace Admin\Controller;

class UploadController extends CommonController {

    /**
     * 上传文件
     */
    public function uploadFile(){
        ini_set('max_execution_time', '0');
        $config = array(
            'rootPath' => '.'.C('UPLOAD_URL').'File/',
            'savePath' => '/',
            'maxSize' => 10 * 1024 * 1024,
            'exts' => '',
        );
        $dir = './'.C('UPLOAD_URL').'File/';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                $this->ajaxReturn(V(0, '创建目录失败'));
            }
        }
        $Upload = new \Think\Upload($config);
        $info = $Upload->upload();
        if ($info === false) {
            $this->ajaxReturn(V(0, $Upload->getError()));
        } else {
            // 返回成功信息
            foreach($info as $file){
                $path = '.'.C('UPLOAD_URL').'File/'.$file['savepath'].$file['savename'];
                $local_path = trim($path, '.');
                $data['name'] =$local_path;
            }
            $this->ajaxReturn(V(1, 'success', $data));
        }
    }

    /**
     * 上传媒体文件
     */
    public function uploadMediaFile(){
        ini_set('max_execution_time', '0');
        $config = array(
            'rootPath' => '.'.C('UPLOAD_URL').'Media/',
            'savePath' => '/',
            'maxSize' => 10 * 1024 * 1024,
            'exts' => 'mp3,ogg,aac,wav,mp4,rmvb,avi',
        );
        $dir = './'.C('UPLOAD_URL').'Media/';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                $this->ajaxReturn(V(0, '创建目录失败'));
            }
        }
        $Upload = new \Think\Upload($config);
        $info = $Upload->upload();
        if ($info === false) {
            $this->ajaxReturn(V(0, $Upload->getError()));
        } else {
            // 返回成功信息
            foreach($info as $file){
                $path = '.'.C('UPLOAD_URL').'Media/'.$file['savepath'].$file['savename'];
                $local_path = trim($path, '.');
                $data['name'] =$local_path;
            }
            $this->ajaxReturn(V(1, 'success', $data)); 
        }
    }
}

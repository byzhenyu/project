<?php
namespace Common\Controller;
use Think\Controller;
class HrCommonController extends Controller
{   
    public function __construct()
    {
        parent::__construct();
        //获取参数配置
        define('DEFAULT_IMG', 'https://shanjian.oss-cn-hangzhou.aliyuncs.com/nopic.png');
        $this->get_global_config();
        $hr_auth = session('hr_auth');
        $hr_id = $hr_auth['hr_id'];
        $hr_id = $hr_id ? $hr_id : 0;
        $hrName = $hr_auth['hr_name'] ? $hr_auth['hr_name'] : 0;
        define('HR_ID', $hr_id);
        define('HR_NAME', $hrName);
        if(!$hr_id) {
            redirect(U('/NewHr/Login'));
        } 

        
    }
    /**物理删除
     * ajax 删除指定数据库的记录
     * @param string $table: 操作的表名
     * @param string $keyname: 表的主键名称
     * @return json: 直接返回客户端json
     */
    protected function _del($table, $keyname = ''){
        $id = I('id', 0);
        if ($keyname == '') $keyname = $table . '_id';
        $result = V(0, '删除失败, 未知错误. Unknown Error!');
        if($table != '' && $id != 0){
            $where[$keyname] = array('in', $id);
            if( M($table)->where($where)->delete() !== false ){
                $result = V(1, '删除成功');
            } else {
                $result = V(0, M($table)->getError());
            }
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * ajax 数据更新到回收站
     * @param string $table: 操作的数据库
     * @return json: 直接返回 客户端json
     */
    protected function _recycle($table){
        $id = I('id', 0);
        $result = V(0, '删除失败, 未知错误');
        if($table != '' && $id != 0){
            $where[$table . '_id'] = array('in', $id);
            $data['status'] = 1;
            if( M($table)->data($data)->where($where)->save() !== false){
                $result = V(1, '删除成功');
            }
        }
        $this->ajaxReturn($result);
    }
    
    /**
     * [get_global_config 获取配置]
     * @return [type] [description]
     */
    function get_global_config()
    {
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $configParse = new \Common\Tools\ConfigParse();
            $config      =   $configParse->lists();
            S('DB_CONFIG_DATA',$config,60);
        }
        C($config); //添加配置

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

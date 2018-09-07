<?php

/**
 * Created by PhpStorm.
 * User: jipingzhao
 * Date: 6/29/17
 * Time: 9:14 AM
 * 控制器基类
 */
namespace Common\Controller;
use Think\Controller;
class CommonController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        //获取参数配置
        $this->get_global_config();
        //获取搜索关键词
        if (is_login()){
            define('UID', session('user_auth')['uid']);

            $this->nickname = session('user_auth.nickname') ? session('user_auth.nickname') : session('user_auth.mobile');
        }
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


    /**
     * 图片上传类
     * @param string $dirname 保存目录
     * @param string $subname 文件名称
     * @param array $thumb 生成缩略图尺寸
     * @return array
     */
    public function upload_img($dirname, $subname, $thumb = array()) {
        $upload_path = '.'.C('UPLOAD_PICTURE_ROOT');
        $upload = new \Think\Upload();
        $image = new \Think\Image();
        $upload->maxSize = C('UPLOAD_SIZE') ;// 设置附件上传大小  C('UPLOAD_SIZE');
        $upload->rootPath  = $upload_path . '/'; // 设置附件上传根目录
        $upload->savePath  = $dirname . '/'; // 设置附件上传（子）目录
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
        $upload->saveRule = 'uniqid';
        $upload->subName = $subname;
        $upload->uploadReplace = true; //是否存在同名文件是否覆盖
        // 上传文件 
        $upload_result = $upload->upload();
        if (!$upload_result) {// 上传错误提示错误信息
            return V(0, $upload->getError());
        } else {// 上传成功
            foreach ($upload_result as $key => $value) {
                $origin_img = $upload_path.'/'.$value['savepath'].'/'.$value['savename'];
                if ($origin_img != '') {
                    $image->open($origin_img);
                    foreach ($thumb as $v) {
                        $v_thumb = explode(',', $v);
                        $v_ext = str_replace(',', '_', $v);
                        $image->thumb($v_thumb[0], $v_thumb[1])->save($origin_img.'@'.$v_ext.'.'.$value['ext']);
                    }
                }
            }
        }
        return $upload_result;
    }
}

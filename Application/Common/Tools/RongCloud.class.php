<?php
/**
--------------------------------------------------
融云PHP
--------------------------------------------------
Copyright(c) 2017 融云即时通信云 www.rongcloud.cn
--------------------------------------------------
Author: chen
--------------------------------------------------
 */
namespace Common\Tools;
Vendor('Rongcloud.rongcloud');
class RongCloud{
    private $appKey;
    private $appSecret;
    private $jsonPath;
    private $c;
    //------------------------------------------------------
    /**
     * 初始化参数
     * @param $options['appKey']
     * @param $options['appSecret']
     * @param $options['jsonPath']
     */
    public function __construct() {
        $this->appKey = C('RongCloud')['appKey'];
        $this->appSecret = C('RongCloud')['appSecret'];
        $this->jsonPath = '/ThinkPHP/Library/Vendor/Rongcloud/jsonPath/';
        $this->c = new \RongCloud($this->appKey,$this->appSecret);
    }

    /**
     *获取token
     */
    function getToken($data = array())
    {
        $result = $this->c->user()->getToken($data['userId'], $data['name'], $data['portraitUri']);
        return json_decode($result,true);
    }

    /**
     *刷新用户信息
     */
    function updataUsers($data = array())
    {
        $result = $this->c->user()->refresh($data['userId'], $data['name'], $data['portraitUri']);
        return json_decode($result,true);
    }

    /**
     *创建群组
     */
    function createGroup($user_id = array(),$data = array())
    {
        $result = $this->c->group()->create($user_id, $data['groupId'] ,$data['groupName']);
        return json_decode($result,true);
    }

    /**
     *用户入群
     */
    function joinGroup($user_id = array(),$data = array())
    {
        $result = $this->c->group()->join($user_id, $data['groupId'] ,$data['groupName']);
        return json_decode($result,true);
    }

    /**
     *用户退群
     */
    function quitGroup($user_id = array(),$groupId)
    {
        $result = $this->c->group()->quit($user_id, $groupId);
        return json_decode($result,true);
    }

    /**
     *解散群组
     */
    function deleteGroup($userId,$groupId)
    {
        $result = $this->c->group()->dismiss($userId, $groupId);
        return json_decode($result,true);
    }

    /**
     *发送自定义推送消息
     */
    function sendCmd($data = array())
    {
        $result = $this->c->message()->PublishSystem($data['fromUserId'] = 'eqiba',$data['toUserId'],$data['objectName'],$data['content'] = '',$data['pushContent'] = '',$data['pushData'] = '');
        return json_decode($result,true);
    }

    /**
     *根据时间条件下载历史消息文件
     */
    function getChatRecordFile($time=2017030100){
        $result = $this->c->message()->getHistory($time);
        return json_decode($result,true);
    }

    //创建文件夹
    function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
        if (!$this->mkdirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }

    //写入cursor
    function writeCursor($filename,$content){
        //判断文件夹是否存在，不存在的话创建
        if(!file_exists("resource/txtfile")){
            $this->mkdirs("resource/txtfile");
        }
        $myfile=@fopen("resource/txtfile/".$filename,"w+") or die("Unable to open file!");
        @fwrite($myfile,$content);
        fclose($myfile);
    }

    //读取cursor
    function readCursor($filename){
        //判断文件夹是否存在，不存在的话创建
        if(!file_exists("resource/txtfile")){
            $this->mkdirs("resource/txtfile");
        }
        $file="resource/txtfile/".$filename;
        $fp=fopen($file,"a+");//这里这设置成a+
        if($fp){
            while(!feof($fp)){
                //第二个参数为读取的长度
                $data=fread($fp,1000);
            }
            fclose($fp);
        }
        return $data;
    }
}
?>
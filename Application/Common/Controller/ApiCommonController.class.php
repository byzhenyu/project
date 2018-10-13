<?php
/**
 * 未登录接口, 需要继承的基类
 * create by zhaojiping <QQ: 17620286>
 */
namespace Common\Controller;
use Common\Controller\CommonController;
class ApiCommonController extends CommonController {

    public function __construct(){
        parent::__construct();

        define('DEFAULT_IMG', 'https://shanjian.oss-cn-hangzhou.aliyuncs.com/nopic.png');
        $code = $_POST['code'];
        if ($code != '') {
            if (C('APP_DATA_ENCODE') === true) {
                // 解密
                $aes = new \Common\Tools\Aes();
                $code = $aes->aes128cbcHexDecrypt($code);
            }
            $params = json_decode($code, true);

            // 重新赋值
            $_POST = null;
            foreach ($params as $key => $value) {
                $_POST[$key] = $value;
                if ($key == 'p') {
                    $_GET['p'] = $value;
                }
            }
        }
    }

    protected function apiReturn($result){
        if ($result['status'] != 0 && $result['status'] != 1) {
            exit('参数调用错误 status');
        }
        $data = $result['data'];
        if ($result['data'] != '' && C('APP_DATA_ENCODE') == true) {
            $data = json_encode($result['data']); // 数组转为json字符串
            $aes = new \Common\Tools\Aes();
            $data = $aes->aes128cbcEncrypt($data); // 加密
        }

        if (is_null($data) || empty($data)) $data = array();
            $data = string_data($data);
            $this->ajaxReturn(V($result['status'], $result['info'], $data));

    }

}

<?php
//用于测试打印数组数据
function p($arr) {
    header('content-type:text/html;charset=utf-8');
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}
/**
 * 价格转换 分->元
 * @param $fen
 * @return string
 */
function fen_to_yuan($fen) {
    return sprintf('%.2f', $fen / 100.00);
}

/**
 * 价格转换 元->分
 * @param $yuan
 * @return mixed
 */
function yuan_to_fen($yuan) {
    return intval(strval($yuan * 100));
}


/**
 * 密码加密与密码验证
 * @param string $str_password 需要加密的密码字符串
 * @param string $real_password 需要验证的密码字符串
 * @param boolean $type 操作类型  false[密码加密]  true[密码验证]
 * @return mixed
 */
function pwdHash($str_password = '', $real_password = '', $type = false) {
    if (!$str_password && !$real_password) return false;
    require_cache(PLUGINS . '/Phpass/PasswordHash.php');
    $hasher = new PasswordHash(8, false);
    if (!$type) {
        return $hasher->HashPassword($str_password);
    } else {
        return $hasher->CheckPassword($str_password, $real_password);
    }
}

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 */
function check_verify($code, $id = 1){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * XSS安全过滤
 * @param string $content 需要过滤的内容
 * @return string
 */
function filter_xss($content) {
    require_cache(PLUGINS . '/HTMLPurifier/HTMLPurifier.includes.php');
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.TargetBlank', TRUE);
    $obj = new HTMLPurifier($config);
    return $obj->purify($content);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 格式化后的时间字符串
 */
function time_format($time = NULL, $style = 'Y-m-d H:i:s') {
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($style, $time);
}

/**
 * 手机格式验证
 * @param string $mobile 验证的手机号码
 * @return boolean
 */
function isMobile($mobile){
    if ( !empty($mobile) ) {
        if( preg_match("/^1[345789]\d{9}$/", $mobile) ){
            return true;
        }
    }
    return false;
}

/**
 * 手机中间四位处理为*
 * @param string $mobile手机号码
 * @return String
 */
function mobile_format($mobile) {
    if (!$mobile) {
        return null;
    }
    $pattern = '/(\d{3})(\d{4})(\d{4})/i';
    $replacement = '$1****$3';
    $mobile = preg_replace($pattern, $replacement, $mobile);
    return $mobile;
}

/**
 * 电子邮箱格式验证
 * @param  string $email 验证的邮件地址
 * @return boolean
 */
function is_email($email) {
    if (!empty($email)) {
        return preg_match('/^[a-z0-9]+([\+_\-\.]?[a-z0-9]+)*@([a-z0-9]+[\-]?[a-z0-9]+\.)+[a-z]{2,6}$/i', $email);
    }
    return false;
}

/**
 * 通用图片上传函数
 * @param String $imgname 上传文件域的NAME属性
 * @param type $dirname 上传文件存储目录
 * @param type $thumb 需要生成多少个缩略图
 * @return Array
 */
function upload($imgname, $dirname, $thumb = array()) {
    if (isset($_FILES[$imgname]) && $_FILES[$imgname]['error'] == 0) {
        $upload = new \Think\Upload();
        $rootpath = C('UPLOAD_ROOTPATH');
        $upload->savePath = $rootpath;
        $upload->maxSize = intval(C('IMAGE_MAXSIZE')) * 1024 * 1024;
        $upload->exts = C('ALLOW_IMG_EXT');
        $upload->savePath = $dirname . '/';
        $info = $upload->upload(array($imgname => $_FILES[$imgname]));
        if (!$info) {
            return array('status' => 0, 'error' => $upload->getError());
        } else {
            $ret['status'] = 1;
            $ret['image']['origin'] = $origin_img = $info[$imgname]['savepath'] . $info[$imgname]['savename'];
            if (is_array($thumb) && !empty($thumb)) {
                $image = new \Think\Image();
                foreach ($thumb as $k => $v) {
                    $ret['image']['thumb'][$k] = $info[$imgname]['savepath'] . 'thumb_' . $k . '_' . $info[$imgname]['savename'];
                    $image->open($rootpath . $origin_img);
                    $image->thumb($v[0], $v[1])->save($rootpath . $ret['image']['thumb'][$k]);
                }
            }
        }
        return $ret;
    }
}

/**
 * 验证上传文件域中是否有上传的图片
 * @param String $imgname
 * @return Boolean
 */
function has_img($imgname) {
    foreach ($_FILES[$imgname]['error'] as $v) {
        if ($v == 0) {
            return true;
        }
    }
    return false;
}

/**
 * 删除指定的图片
 * @param [Array|String] $images 需要删除的图片
 * @return Boolean
 */
function deleteImage($image = '') {
    if (file_exists($image)) {
        if (@unlink($image)) {
            return true;
        } else {
            return false;
        }
    }
}

//判断用户是否登录
function is_login() {
    return session('user_auth') ? true : false;
}

/**
 * 获取目录中的所有文件(不包括二级目录),并以数组返回, 用于批量上传商品相册使用
 * @param  String $path 目录路径
 * @return Array      目录结构数组
 */
function listDir($dir) {
    $files = array();
    if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file != ".." && $file != ".") {
                $file = $dir . "/" . $file;
                if (is_dir($file)) {
                    $files[$file] = listDir($file);
                } else {
                    $files[] = $file;
                }
            }
        }
        closedir($handle);
        return $files;
    }
}


/**
 * 友好的时间显示
 */
function time_to_units($time) {
    $now_time = time();
    $time_diff = $now_time - $time;

    $units = array(
            '31536000' => '年前',
            '2592000'  => '月前',
            '86400'    => '天前',
            '3600'     => '小时前',
            '60'       => '分钟前',
            '1'        => '秒前',
        );
    foreach ($units as $key => $value) {
        if ($time_diff > $key) {
            $num = (int)($time_diff / $key);
            $tips = $num . $value;
            break;
        }
    }
    return $tips;
}

/**
 * +----------------------------------------------------------
 * 生成随机字符串
 * +----------------------------------------------------------
 * @param int $length 要生成的随机字符串长度
 * @param string $type 随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
 * +----------------------------------------------------------
 * @return string
+----------------------------------------------------------
 */
function randCode($length = 5, $type = 0) {
    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } elseif ($type == "-1") {
        $string = implode("", $arr);
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $string[rand(0, $count)];
    }
    return $code;
}

/**
 * 删除指定的目录该件
 */
function deldir($path) {
    //给定的目录不是一个文件夹
    if (!is_dir($path)) return null;
    $fh = opendir($path);
    while (($row = readdir($fh)) !== false) {
        //过滤掉虚拟目录
        if ($row == '.' || $row == '..') {
            continue;
        }
        if (!is_dir($path . '/' . $row)) {
            unlink($path . '/' . $row);
        }
        deldir($path . '/' . $row);

    }
    //关闭目录句柄，否则出Permission denied
    closedir($fh);
    //删除文件之后再删除自身
    rmdir($path);
}

/**
 * 用于API调式时输出LOG文件
 * @param mixed $value 要打印的数据
 * @param string $file 文件要保存的路径, 默认在当前控制器目录下同名.log
 * @return null 无返回值
 */
function LL($value = '', $file = '') {
    if ($file == '') {
        $file = './Application/' . MODULE_NAME . '/Controller/' . CONTROLLER_NAME . 'Controller.class.log';
    }
    error_log(print_r($value, 1), 3, $file);
}

/**
 * 返回JSON通一格式
 * @param int $status 返回状态
 * @param string $info 返回提示信息
 * @param string $data 返回对象
 * @return array
 */
function V($status = -1, $info = '', $data = array()) {
    if ($status == -1) {
        exit('参数调用错误');
    }
    return array('status' => $status, 'info' => $info, 'data' => $data);
}


/*
 * $_login_validate中 chk_code验证所需要的方法
 * @param String $code 需要验证数据
 */
function chk_chkcode($code) {
    $verify = new \Think\Verify();
    return $verify->check($code);
}

/**
 * @param $arr
 * @param $key_name
 * @param $key_name2
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 数组指定列为元素 的一个数组
 */
function get_id_val($arr, $key_name,$key_name2)
{
    $arr2 = array();
    foreach($arr as $key => $val){
        $arr2[$val[$key_name]] = $val[$key_name2];
    }
    return $arr2;
}

/**
 * 多个数组的笛卡尔积
 * @param unknown_type $data
 */
function combineDika() {
    $data = func_get_args();
    $data = current($data);
    $cnt = count($data);
    $result = array();
    $arr1 = array_shift($data);
    foreach($arr1 as $key=>$item)
    {
        $result[] = array($item);
    }
    foreach($data as $key=>$item)
    {
        $result = combineArray($result,$item);
    }
    return $result;
}


/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
 */
function combineArray($arr1,$arr2) {
    $result = array();
    foreach ($arr1 as $item1)
    {
        foreach ($arr2 as $item2)
        {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}


/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'parent_id', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 获取图片缩略图 如果缩略图不存在则生成
 * @param string $filename 要生成缩略图的原图地址
 * @param int $width 生成缩略图的宽度
 * @param int $height 生成缩略图的高度
 * @return mixed 正常返回缩略图的地址
 * create by zhaojiping QQ: 17620286
 */
function thumb($filename, $width=120, $height=120){
    if ($filename == '') {
        return '';
    }
    $info = pathinfo($filename);
    $info_array = explode('@', $info);
    if (!empty($info_array)) $info = $info_array[0];

    // 如果图片已经是缩略图, 直接返回
    $thumbFlag = '@' . $width .'_'. $height;
    $thumbFlagLen = strlen($thumbFlag);
    if (substr($info['filename'], -$thumbFlagLen) == $thumbFlag && file_exists($filename)) {
        return '/' . $filename;
    }

    $oldFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $info['extension'];
    $thumbFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $info['extension'] .$thumbFlag .'.' . $info['extension'];

    $oldFile = str_replace('\\', '/', $oldFile);
    $thumbFile = str_replace('\\', '/', $thumbFile);

    $filename = ltrim($filename, '/');
    $oldFile = ltrim($oldFile, '/');
    $thumbFile = ltrim($thumbFile, '/');

    //如果原图不存在, 清除缩略图, 返回原图地址
    if (!file_exists($oldFile)) {
        @unlink($thumbFile);
        return '/' . $oldFile;
    }else if(file_exists($thumbFile)){ //缩图已存在, 直接返回缩略图
        return '/' . $thumbFile;
    }else{ //生成缩略图
        $oldimageinfo = getimagesize($oldFile);
        $old_image_width = intval($oldimageinfo[0]);
        $old_image_height = intval($oldimageinfo[1]);
        if ($old_image_width <= $width && $old_image_height <= $height) {
            @unlink($thumbFile);
            @copy($oldFile, $thumbFile);

            return '/' . $thumbFile;

        } else {
            $image = new \Think\Image();
            if ($old_image_width < $old_image_height) {
                $myHeight = $old_image_height * $width / $old_image_width;
                // 压缩
                $image->open($oldFile)->thumb($width, $myHeight, \Think\Image::IMAGE_THUMB_SCALE)->save($thumbFile, null, 100, false);
            } else {
                $myWidth = $old_image_width * $height / $old_image_height;
                // 压缩
                $image->open($oldFile)->thumb($myWidth, $height, \Think\Image::IMAGE_THUMB_SCALE)->save($thumbFile, null, 100, false);
            }

            if (intval($height) == 0 || intval($width) == 0) {
                exit('/' . $oldFile);
            }
            //dump($image);exit;
            // 再居中截取
            $image->open($thumbFile)->thumb($width, $height, \Think\Image::IMAGE_THUMB_CENTER)->save($thumbFile, null, 95, false);

            //缩图失败
            if (!$image) {
                $thumbFile = $oldFile;
            }

            return '/' . $thumbFile;
        }
    }
}

/**
 * 作用：将xml转为array
 */
function xmlToArray($xml) {
    // 将XML转为array
    libxml_disable_entity_loader(true);
    libxml_use_internal_errors();
    $array_data = json_decode ( json_encode ( simplexml_load_string ( $xml, 'SimpleXMLElement', LIBXML_NOCDATA ) ), true );
    return $array_data;
}

/**
 * 通用分页处理函数
 * @param Int $count 总条数
 * @param int $page_size 分页大小
 * @return Array  ['page']分页数据  ['limit']查询调用的limit条件
 */
function get_web_page($count, $page_size=0){
    if ($page_size == 0) $page_size = C('PAGE_SIZE');
    $page = new \Think\Page($count, $page_size);
    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    $show = $page->show();
    $limit = $page->firstRow.','.$page->listRows;
    return array('page'=>$show,'limit'=>$limit);
}

/**
 * 充值/提现状态
 * @param int $type 类型
 */
function accountState($type) {
    switch ($type) {
        case 0  : return  C('ACCOUNT_STATE')[0];   break;
        case 1  : return  C('ACCOUNT_STATE')[1];   break;
        case 2  : return  C('ACCOUNT_STATE')[2];   break;
        case 3  : return  C('ACCOUNT_STATE')[3];   break;
        case 4  : return  C('ACCOUNT_STATE')[4];   break;
        default : return  false;      break;
    }
}

/**
 * @param $type
 * @return mixed
 */
function user_account_type($type){
    $arr = array(
        0 => '充值',
        1 => '提现'
    );
    return $arr[$type];
}

/**
 * 付款类型
 * @param int $type 类型
 */
function paymentType($type) {
    switch ($type) {
        case 1  : return  C('PAYMENT_TYPE')[1];   break;
        case 2  : return  C('PAYMENT_TYPE')[2];   break;
        case 3  : return  C('PAYMENT_TYPE')[3];   break;
        default : return  false;      break;
    }
}


/**
 * 获取随机位数数字
 * @param integer $len 长度
 * @return string
 */
function randNumber($len = 4){
    $chars = str_repeat('0123456789', 10);
    $chars = str_shuffle($chars);
    $str = substr($chars, 0, $len);
    return $str;
}

/**
 * 手机图片上传
 * @param $img: 旧图片地址
 * @param $obj: 上传的表单名称
 * @param $path: 上传的文件目录
 * @return mixed 上传成功, 返回上传的图片地址, 上传失败返加-1或0
 */
function app_upload_img($obj = 'photo', $img = '',  $path = '', $uid = UID){
    if (isset($_FILES[$obj]['tmp_name']) && !empty($_FILES[$obj]['tmp_name'])) {

        // 旧图片地址得到图片名称
        $img = basename($img);
        if ($img == '' || empty($img) || $img == null) {
            $img = createFileName('jpg');
        }

        $createImgPath = '.'. C('UPLOAD_PICTURE_ROOT') .'/'. $uid ;
        if ($path != '') {
            $createImgPath = '.'. C('UPLOAD_PICTURE_ROOT') .'/'.$path .'/'. $uid ;
        }
        if ( !is_dir($createImgPath) ) {
            mkdir($createImgPath);
        }

        $target_path = $createImgPath .'/'. $img ; //接收文件目录
        if (move_uploaded_file( $_FILES[$obj]['tmp_name'], $target_path )) {
            if (substr($target_path, 0, 1) == '.') {
                $target_path = substr($target_path, 1);
            }
            return $target_path;
        } else {
            return -1;
        }
    } else {
        return 0;
    }
}

/**
 * @desc 上传文件
 * @param string $obj
 * @param string $file
 * @param string $path
 * @param int|mixed $uid
 * @param string $ext
 * @return bool|int|string
 */
function app_upload_file($obj = 'voice', $file = '',  $path = '', $uid = UID, $ext = 'mp3'){
    if (isset($_FILES[$obj]['tmp_name']) && !empty($_FILES[$obj]['tmp_name'])) {

        // 旧图片地址得到图片名称
        $file = basename($file);
        if ($file == '' || empty($file) || $file == null) {
            $file = createFileName($ext);
        }

        $createImgPath = '.'. C('UPLOAD_PICTURE_ROOT') .'/'. $uid ;
        if ($path != '') {
            $createImgPath = '.'. C('UPLOAD_PICTURE_ROOT') .'/'.$path .'/'. $uid ;
        }
        if ( !is_dir($createImgPath) ) {
            mkdir($createImgPath);
        }

        $target_path = $createImgPath .'/'. $file ; //接收文件目录
        if (move_uploaded_file( $_FILES[$obj]['tmp_name'], $target_path )) {
            if (substr($target_path, 0, 1) == '.') {
                $target_path = substr($target_path, 1);
            }
            return $target_path;
        } else {
            return -1;
        }
    } else {
        return 0;
    }
}

/**
 * 手机图片上传 - 多图上传
 * @param $img: 旧图片地址
 * @param $obj: 上传的表单名称
 * @param $path: 上传的文件目录
 * @return mixed 上传成功, 返回上传的图片地址, 上传失败返加-1或0
 */
function app_upload_more_img($obj = 'photo', $img = '',  $path = '', $uid = UID, $i = 0 , $width = 450, $height = 600){
    if (isset($_FILES[$obj]['tmp_name'][$i]) && !empty($_FILES[$obj]['tmp_name'][$i])) {

        // 旧图片地址得到图片名称
        $img = basename($img);
        if ($img == '' || empty($img) || $img == null) {
            $img = createFileName('jpg');
        }

        $createImgPath = '.'. C('UPLOAD_PICTURE_ROOT') .'/'. $uid ;
        if ($path != '') {
            $createImgPath = '.'. C('UPLOAD_PICTURE_ROOT') .'/'.$path .'/'. $uid ;
        }
        if ( !is_dir($createImgPath) ) {
            mkdir($createImgPath);
        }

        $target_path = $createImgPath .'/'. $img ; //接收文件目录
        if (move_uploaded_file( $_FILES[$obj]['tmp_name'][$i], $target_path )) {
            if (substr($target_path, 0, 1) == '.') {
                $target_path = substr($target_path, 1);
            }

            return thumb($target_path, $width, $height);
        } else {
            return -1;
        }
    } else {
        return 0;
    }
}

/**
  * 生成文件扩展名, 如果没有传文件的名称
  * @param $ext: 生成文件默认文件名称
  */
function createFileName($ext = 'png'){
   return date('Ymd_His') .'_'. microtime(true)*10000 .'_' . rand(1000,9999) .'.' . $ext;
}


/**
 * 生成日期与随机数字的字符串, 用下划线分隔
 * @return String 日期_时间_毫秒微秒_4位随机数
 * example 上传的文件名, 环信用户的用户名
 */
function datetimeRand() {
    return date('Ymd_His') . '_' . rand(100000, 999999);
}

/*
 * 生成随机字符串
 * @param int $length 返回的字符串的长度, 默认16位
 * @return String
 * example: 环信用户的密码
 */
function randChar($length = 16) {
    $str = '';
    $strPol = 'abcdefghigkmnpqrstuvwxyz23456789';
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];  //rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

//检证身份证是否正确
function isCard($card) {
    $card = to18Card($card);
    if (strlen($card) != 18) {
        return false;
    }

    $cardBase = substr($card, 0, 17);

    return (getVerifyNum($cardBase) == strtoupper(substr($card, 17, 1)));
}

//格式化15位身份证号码为18位
function to18Card($card) {
    $card = trim($card);

    if (strlen($card) == 18) {
        return $card;
    }

    if (strlen($card) != 15) {
        return false;
    }

    //如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
    if (array_search(substr($card, 12, 3), array('996', '997', '998', '999')) !== false) {
        $card = substr($card, 0, 6) . '18' . substr($card, 6, 9);
    } else {
        $card = substr($card, 0, 6) . '19' . substr($card, 6, 9);
    }
    $card = $card . getVerifyNum($card);
    return $card;
}

// 计算身份证校验码，根据国家标准gb 11643-1999
function getVerifyNum($cardBase) {
    if (strlen($cardBase) != 17) {
        return false;
    }
    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

    //校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

    $checksum = 0;
    for ($i = 0; $i < strlen($cardBase); $i++) {
        $checksum += substr($cardBase, $i, 1) * $factor[$i];
    }

    $mod = $checksum % 11;
    $verify_number = $verify_number_list[$mod];

    return $verify_number;
}

/*
 * 分页自增编号
 * @param int $page_total 每页条数
 * @return String
 */
function pageNumber($page_total, $add_num = 1) {
    $page = $_GET['p'] ? intval($_GET['p']) : 1;
    $num = ($page - 1) * $page_total + $add_num;
    return $num;
}

/**
 * 六牛科技发送短信HTTP请求
 * @param $mobile string 手机号码
 * @param $content string 短信内容
 * @return   mixed
 */

function sendMessageRequest($mobile, $content) {

    /********参数配置区域start*********/

    $min_limit = 1; //每分钟限制条数
    $day_limit = 5; //每天短信限制条数
    $sign = '【'.C('SMS_SIGN').'】'; // 企业签名
    $userid = C('SMS_USERID');
    $user_name = C('SMS_USERNAME');
    $password = C('SMS_PASSWORD');

    /********参数配置区域end*********/

    /**********短信条数限制处理区域start*******/
    $count = S('sms_count_' . date('YmdHi') . $mobile);
    $dayCount = S('sms_count_' . date('Ymd') . $mobile);

    if ($count >= $min_limit) {
        LL($mobile . '短信超出限制,' . date('Y-m-d Hi') . ':' . $count, './logs/sms_privalige_min' . date('Y_m_d') . '.log');
        return V(0, '验证码' . $min_limit . '分钟内不能重复发送');
    }
    if ($dayCount >= $day_limit) {
        //LL($mobile . '短信超出限制,' . date('Y-m-d') . ':' . $dayCount, './logs/sms_privalige_day' . date('Y_m_d') . '.log');
        //return V(0, '24小时内不能再发送短信');
    }

    $count || $count = 0;
    $dayCount || $dayCount = 0;
    S('sms_count_' . date('YmdHi') . $mobile, ++$count, 60);
    S('sms_count_' . date('Ymd') . $mobile, ++$dayCount, 60 * 60 * 24);
    /**********短信条数限制处理区域end*******/
    $url = "http://www.dxcxpt.com:8088/v2sms.aspx";
    $content = urlencode($content . $sign); // 短信内容之后添加企业签名，同时进行UrlEncode转码
    $sendTime = '';
    $timestamp = str_replace(["-", " ", ":"], "", date('Y-m-d H:i:s', time()));
    $extno = '';
    $signData = md5($user_name . $password . $timestamp);
    $postdata = "action=send&userid=$userid&timestamp=$timestamp&sign=$signData&mobile=$mobile&content=$content&sendTime=&extno=";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    $result = curl_exec($ch);
    curl_close($ch);
    $resultData = xmlToArray($result);
    // 发送有没有成功
    if (strtolower($resultData['returnstatus']) == 'success') {
        LL($result, './logs/sms_success' . date('Y_m_d') . '.log');
        return V(1, '短信发送成功');
    } else {
        LL($result, './logs/sms_error' . date('Y_m_d') . '.log');
        return V(0, $resultData['message']);
    }

}

/**
 * @describe 数组生成正则表达式
 * @param array $words
 * @return string
 */
function generateRegularExpression($words)
{
    $regular = implode('|', array_map('preg_quote', $words));
    return "/$regular/i";
}
/**
 * @describe 字符串 生成正则表达式
 * @param array $words
 * @return string
 */
function generateRegularExpressionString($string){
    $str_arr[0]=$string;
    $str_new_arr=  array_map('preg_quote', $str_arr);
    return $str_new_arr[0];
}
/**
 * 检查敏感词
 * @param $banned
 * @param $string
 * @return bool|string|array
 */
function check_words($banned,$string)
{    $match_banned=array();
    //循环查出所有敏感词
    $new_banned=strtolower($banned);
    $i=0;
    do{
        $matches=null;
        if (!empty($new_banned) && preg_match($new_banned, $string, $matches)) {
            $isempyt=empty($matches[0]);
            if(!$isempyt){
                $match_banned = array_merge($match_banned, $matches);
                $matches_str=strtolower(generateRegularExpressionString($matches[0]));
                $new_banned=str_replace("|".$matches_str."|","|",$new_banned);
                $new_banned=str_replace("/".$matches_str."|","/",$new_banned);
                $new_banned=str_replace("|".$matches_str."/","/",$new_banned);
            }
        }
        $i++;
        if($i>20){
            $isempyt=true;
            break;
        }
    }while(count($matches)>0 && !$isempyt);

    //查出敏感词
    if($match_banned){
        return array_unique($match_banned);
    }
    //没有查出敏感词
    return array();
}

/**
 * @desc 违禁词比较
 * @param $content
 * @return bool 有违禁词返回true
 */
function cmp_contraband($content){
    $array = contraband_list();
    $banner = generateRegularExpression($array);
    $valid = check_words($banner, $content);
    $cmp_len = count($valid);
    if($cmp_len > 0) return true;
    return false;
}

/**
 * @desc 比较黑/白名单
 * @param $content
 * @param int $type
 * @return bool
 */
function cmp_black_white($content, $type = 1){
    $arr = black_white_list($type);
    $banner = generateRegularExpression($arr);
    $valid = check_words($banner, $content);
    $cmp_len = count($valid);
    if($cmp_len > 0) return true;
    return false;
}

/**
 * @desc 获取违禁词列表
 * @return mixed
 */
function contraband_list(){
    $res = D('Admin/Contraband')->getContrabandCmpList();
    return $res;
}

/**
 * @desc 获取黑/白名单列表
 * @param int $type
 * @return mixed
 */
function black_white_list($type = 1){
    $res = D('Admin/BlackWhite')->getBlackList($type);
    return $res;
}

/**
 * 用户账户变动日志
 * @param $user_id int 用户id
 * @param $money int 变动金额
 * @param $type int 变动类型  0充值 1提现 2消费
 * @param $desc string 变动描述
 * @param $order_sn string 订单号
 * @return mixed
 */
function account_log($user_id, $money, $type, $desc = '', $order_sn = ''){
    $data = array();
    $data["user_id"] = $user_id;
    $data["user_money"] = $money;
    $data["change_time"] = NOW_TIME;
    $data["change_type"] = $type;
    $data["change_desc"] = $desc;
    $data["order_sn"] = $order_sn;
    return M('AccountLog')->add($data);
}

/**
 * @desc 检测用户是否已经实名认证过
 * @param $user_id
 * @return bool
 */
function check_is_auth($user_id){
    if(!$user_id) $user_id = UID;
    if(!$user_id) return false;
    $where = array('user_id' => $user_id);
    $is_auth = D('Admin/User')->getUserField($where, 'is_auth');
    if($is_auth) return true;
    return false;
}

function auth_string($user_id = UID){
    if(!$user_id) return false;
    $auth_info = D('Admin/UserAuth')->getAuthInfo(array('user_id' => $user_id));
    if(!$auth_info) return false;
    if($auth_info['audit_status'] == 0) return '身份认证待审核状态！';
    if($auth_info['audit_status'] == 2) return '身份认证审核未通过';
}

/**
 * 数组转换
 * @param $array
 * @return array
 */
function returnArrData($array) {
    $new  = array();
    if (!empty($array)) {
        $i = 0;
        foreach ($array as $k=>$v) {
            $new[$i]['id'] = $k;
            $new[$i]['value'] = $v;
            $i++;
        }
    }

    return $new;
}


function getFirstChar($s0){
    $fchar = ord($s0{0});
    if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
    $s1 = iconv("UTF-8","gb2312", $s0);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $s0){$s = $s1;}else{$s = $s0;}
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc >= -20319 and $asc <= -20284) return "A";
    if($asc >= -20283 and $asc <= -19776) return "B";
    if($asc >= -19775 and $asc <= -19219) return "C";
    if($asc >= -19218 and $asc <= -18711) return "D";
    if($asc >= -18710 and $asc <= -18527) return "E";
    if($asc >= -18526 and $asc <= -18240) return "F";
    if($asc >= -18239 and $asc <= -17923) return "G";
    if($asc >= -17922 and $asc <= -17418) return "H";
    if($asc >= -17922 and $asc <= -17418) return "I";
    if($asc >= -17417 and $asc <= -16475) return "J";
    if($asc >= -16474 and $asc <= -16213) return "K";
    if($asc >= -16212 and $asc <= -15641) return "L";
    if($asc >= -15640 and $asc <= -15166) return "M";
    if($asc >= -15165 and $asc <= -14923) return "N";
    if($asc >= -14922 and $asc <= -14915) return "O";
    if($asc >= -14914 and $asc <= -14631) return "P";
    if($asc >= -14630 and $asc <= -14150) return "Q";
    if($asc >= -14149 and $asc <= -14091) return "R";
    if($asc >= -14090 and $asc <= -13319) return "S";
    if($asc >= -13318 and $asc <= -12839) return "T";
    if($asc >= -12838 and $asc <= -12557) return "W";
    if($asc >= -12556 and $asc <= -11848) return "X";
    if($asc >= -11847 and $asc <= -11056) return "Y";
    if($asc >= -11055 and $asc <= -10247) return "Z";
    return $s0;
}

/**
 * @desc 获取第一个字母
 * @param $zh
 * @return string
 */
function rev_pinyin($zh){
    $ret = "";
    $s1 = iconv("UTF-8","gb2312", $zh);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $zh){$zh = $s1;}
    for($i = 0; $i < strlen($zh); $i++){
        $s1 = substr($zh,$i,1);
        $p = ord($s1);
        if($p > 160){
            $s2 = substr($zh,$i++,2);
            $ret .= getFirstChar($s2);
        }else{
            $ret .= $s1;
        }
    }
    return $ret;
}

/**
 * 简历性别要求
 */
function getSexInfo ($sex) {
    switch ($sex) {
        case '1':
            return '男';
        case '2':
            return '女';
        default:
            return '不限';
    }
}

/**
 * @desc 简历认证结果
 * @param $auth_result
 * @return mixed
 */
function show_resume_auth_result($auth_result){
    $array = array(
        0 => '未认证',
        1 => '放弃认证',
        2 => '已认证'
    );
    return $array[$auth_result];
}

/**
 * @desc 添加任务日志
 * @param $user_id
 * @param $task_id
 * @param $task_name
 * @param $update_time bool
 * @return mixed
 */
function add_task_log($user_id, $task_id, $task_name = '', $update_time = false){
    $model = D('Admin/TaskLog');
    $data = array(
        'user_id' => $user_id,
        'task_id' => $task_id,
        'task_name' => $task_name,
        'update_time' => $update_time
    );
    $res = $model->add($data);
    if($res){
        $task_reward = D('Admin/Task')->getTaskField(array('id' => $task_id), 'reward');
        account_log($user_id, $task_reward, 6, '任务所得', $res);
        //任务所得令牌直接可以提现
        if(1 == $task_id){//实名认证奖励先冻结/后台通过之后进入可提现余额
            D('Admin/User')->increaseUserFieldNum($user_id, 'frozen_money', $task_reward);
        }
        else{
            D('Admin/User')->increaseUserFieldNum($user_id, 'withdrawable_amount', $task_reward);
        }
    }
    return $res;
}

/**
 * @desc 简历评价详情工作经历格式化
 * @param $start
 * @param $end
 * @return string
 */
function year_limit($start, $end){
    $time = 365*24*60*60;
    $year = floor(($end - $start) / $time);
    $month = ceil(((($end - $start) - $year * 365) / ($time/365)) / 30);
    return $year.'年'.$month.'月';
}

/**
 * @desc 面试状态
 * @param $state
 * @return mixed
 */
function interview_state($state){
    $arr = array(
        0 => '邀约面试',
        1 => '已入职',
        2 => '已放弃'
    );
    return $arr[$state];
}

/**
 * @desc 是否获取联系方式
 * @param $is_open
 * @return mixed
 */
function show_is_open($is_open){
    $arr = array(
        0 => '未查看',
        1 => '已获取'
    );
    return $arr[$is_open];
}

/**
 * @desc 用户操作日志
 * @param $operate_type
 * @param $relate_id
 * @param int|mixed $user_id
 */
function add_key_operation($operate_type, $relate_id, $user_id = UID){
    $model = D('Admin/KeyOperation');
    $data = array(
        'user_id' => $user_id,
        'operate_type' => $operate_type,
        'relation_id' => $relate_id
    );
    $model->add($data);
}

/**
 * @desc 黑白名单
 * @param $type
 * @return mixed
 */
function show_black_white($type){
    $arr = array(
        1 => '黑名单',
        2 => '白名单'
    );
    return $arr[$type];
}

/**
 * @desc 黑白名单添加值类型
 * @param $dispose_type
 * @return mixed
 */
function black_white_type($dispose_type = 0){
    $arr = array(
        1 => '手机号',
        2 => '身份证',
        3 => '电子邮箱'
    );
    if(!$dispose_type) return $arr;
    return $arr[$dispose_type];
}

/**
 * @desc 赏金获取类型
 * @param $type
 * @return mixed
 */
function token_log_type($type){
    $arr = array(
        1 => '获取简历',
        2 => '入职'
    );
    return $arr[$type];
}

function hideMobile($mobile){
    $time = time();
    $ts = date('Y-m-d H:i:s');
    $app_key = C('MOBILE_APP_KEY');
    $unit_id = C('UNIT_ID');
    $secret = C('SECRET');
    $request = array(
        'ver' => '2.0',
        'msgid' => $time,
        'ts' => urlencode($ts),
        'service' => 'SafeNumber',
        'msgtype' => 'binding_Relation',
        'appkey' => $app_key,
        'unitID' => $unit_id,
        'prtms' => $mobile,
        'uidType' => 0,
    );
    $a_keys = array_keys($request);
    sort($a_keys);
    $s_h = '';
    foreach($a_keys as &$val){
        if($val == 'ts'){
            $s_h .= $val.urldecode($request[$val]);
            continue;
        }
        $s_h .= $val.$request[$val];
    }
    unset($val);
    $s_h = $secret.$s_h.$secret;
    $md5_s_h = md5($s_h);
    $hex = $md5_s_h;
    $param = '';
    foreach($a_keys as &$val){
        $param .= $val.'='.$request[$val].'&';
    }
    $request['sid'] = $hex;
    $param .= 'sid='.$hex;
    $url = 'http://123.127.33.35:8089/safenumberservicessm/api/manage/dataManage?'.$param;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    curl_close($curl);
    $data = json_decode(strstr($data, '{'), true);
    if($data['binding_Relation_response']['result'] == 0){
        return $data['binding_Relation_response']['smbms'];
    }
    else{
        return false;
    }
}

/**
 * @desc 用户是否上传公司资料
 * @param int|mixed $user_id
 * @return bool
 */
function company_auth($user_id = UID){
    $res = D('Admin/CompanyInfo')->getCompanyInfoInfo(array('user_id' => $user_id));
    if($res) return true;
    return false;
}

function getOpenId($code, $iv = false, $encrypt = false, $union_iv = false, $union_encrypt = false){
    if(!$code) return false;
    $wxConfig = C('WxPay');
    $app_id = $wxConfig['app_id'];
    $secret = $wxConfig['appsecret'];
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$app_id}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
    $res = _httpGet($url);
    $data = json_decode($res,true);
    $openid = $data['openid'];
    if(false !== $iv){
        vendor('wxAes.wxBizDataCrypt');
        $pc = new \wx\WXBizDataCrypt($app_id, $data['session_key']);
        $errCode = $pc->decryptData($encrypt, $iv, $p_data);
        $union_code = $pc->decryptData($union_encrypt, $union_iv, $u_data);
        if($errCode == 0){
            $p_data = json_decode($p_data, true);
        }
        if($union_code == 0){
            $u_data = json_decode($u_data, true);
        }
        $openid = array('openid' => $openid, 'mobile' => $p_data['purePhoneNumber'], 'union_id' => $u_data['unionId']);
    }
    return $openid;
}

function log_record($data){
    M('Log')->add(array('content' => json_encode($data)));
}

function _httpGet($url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT,500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST , false);
    curl_setopt($curl, CURLOPT_URL, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/**
 * @desc null问题
 * @param $data
 * @return mixed
 */
function string_data($data){
    $array_keys = array_keys($data);
    foreach($array_keys as &$val){
        if(is_array($data[$val])){
            $data[$val] = string_data($data[$val]);
            continue;
        }
        $data[$val] = strval($data[$val]);
        if($data[$val] == 'undefined') $data[$val] = '';
        $data[$val] = filter_enter($data[$val]);
    }
    unset($val);
    return $data;
}

/**
 * 数组转xls格式的excel文件
 * @param  array  $data      需要生成excel文件的数组
 * @param  string $filename  生成的excel文件名
 *      示例数据：
$data = array(
array(NULL, 2010, 2011, 2012),
array('Q1',   12,   15,   21),
array('Q2',   56,   73,   86),
array('Q3',   52,   61,   69),
array('Q4',   30,   32,    0),
);
 * @param  string  $subject    excel主题
 * @param  string  $title    excel标题
 * @param  array  $sheet    需要处理的单元格样式
 * @param  int  $count    excel数据行
 */
function create_xls($data,$filename='闪荐科技.xls',$subject='闪荐科技',$title='闪荐科技',$sheet=array(), $count = 0){
    ini_set('max_execution_time', '0');
    Vendor('Phpexcel.PHPExcel');
    $filename=str_replace('.xls', '', $filename);
    $phpexcel = new PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("admin")
        ->setLastModifiedBy("admin")
        ->setTitle("闪荐科技")
        ->setSubject($subject)
        ->setDescription('')
        ->setKeywords($subject)
        ->setCategory("");
    $phpexcel->setActiveSheetIndex(0);
    $phpexcel->getActiveSheet()->freezePane('A2');//冻结首行
    foreach ($sheet as $key => $value) {
        //设置单元格宽度
        $phpexcel->getActiveSheet()->getColumnDimension($value)->setWidth(20);
        //设置标题行字体样式
        $phpexcel->getActiveSheet()->getStyle($value.'1')->getFont()->setName('微软雅黑');
        $phpexcel->getActiveSheet()->getStyle($value.'1')->getFont()->setSize(12);
        $phpexcel->getActiveSheet()->getStyle($value.'1')->getFont()->setBold(true);
    }
    $maxrow = $sheet[count($sheet)-1];
    //设置居中
    $phpexcel->getActiveSheet()->getStyle('A1:'.$maxrow.$count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    //所有垂直居中
    $phpexcel->getActiveSheet()->getStyle('A1:'.$maxrow.$count)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    //设置单元格边框
    $phpexcel->getActiveSheet()->getStyle('A1:'.$maxrow.$count)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
    //设置单元格格式为文本
    $phpexcel->getActiveSheet()->getStyle('A1:'.$maxrow.$count)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    //设置自动换行
    $phpexcel->getActiveSheet()->getStyle('A1:'.$maxrow.$count)->getAlignment()->setWrapText(true);

    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle($title);
    $phpexcel->setActiveSheetIndex(0);
    ob_end_clean();//清除缓冲区,避免乱码
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    //多浏览器下兼容中文标题
    $encoded_filename =  urlencode($filename);
    $ua = $_SERVER["HTTP_USER_AGENT"];
    if (preg_match("/IE/", $ua)) {
        header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');

    } else if (preg_match("/Firefox/", $ua)) {
        header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '.xls"');
    } else {
        header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');

    }
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}

/**
 * @desc 计算年龄
 * @param $time
 * @return false|int|string
 */
function time_to_age($time){
    return date('Y') - date('Y', $time) + 1;
}

/**
 * @desc 计算两个日期间的工作日
 * @param $start_date int 时间戳
 * @param $end_date int 时间戳
 * @param int $weekend_days 1、单休 2、双休
 * @return float|int
 */
function get_days($start_date, $end_date, $weekend_days=2){
    $data = array();
    if ($start_date > $end_date) list($start_date, $end_date) = array($end_date, $start_date);
    $start_reduce = $end_add = 0;
    $start_N      = date('N',$start_date);
    $start_reduce = ($start_N == 7) ? 1 : 0;

    $end_N = date('N',$end_date);
    $weekend_days = intval($weekend_days);
    switch ($weekend_days)
    {
        case 1:
            $end_add = ($end_N == 7) ? 1 : 0;
            break;
        case 2:
        default:
            in_array($end_N,array(6,7)) && $end_add = ($end_N == 7) ? 2 : 1;
            break;
    }
    $days = round(abs($end_date - $start_date)/86400) + 1;
    $data['total_days'] = $days;
    $data['total_relax'] = floor(($days + $start_N - 1 - $end_N) / 7) * $weekend_days - $start_reduce + $end_add;
    return $data['total_days'] - $data['total_relax'];
}

/**
 * @desc 更新HR简历工作地区/职位相关信息
 * @param $hr_id int|bool HR id
 * @param bool $resume_id 简历id
 * @param array $resume_info 简历信息
 * @return  mixed
 */
function refreshUserTags($hr_id = false, $resume_id = false, $resume_info = array()){
    $model = D('Admin/UserTags');
    $resume_model = D('Admin/Resume');
    $res = true;
    if($hr_id && $resume_id){
        $resume_job = $resume_model->getResumeInfo(array('id' => $resume_id));
        if($resume_job) $res = $model->refreshJobArgs($hr_id, array('job_area' => $resume_job['job_area'], 'job_position' => $resume_job['position_id']));
    }
    if(count($resume_info) > 0 && $resume_id){
        //$resume_job = $resume_model->getResumeInfo(array('id' => $resume_id));
        //$tags_arr = array('job_area' => $resume_job['job_area'], 'job_position' => $resume_job['position_id']);
        //if(count(array_diff($tags_arr, $resume_info)) != 0 && count(array_diff($resume_info, $tags_arr)) != 0){
        $hr_id = D('Admin/HrResume')->where(array('resume_id' => $resume_id))->field('hr_user_id')->select();
        $hr_user_id = array();
        foreach($hr_id  as &$val){
            $hr_user_id[] = $val['hr_user_id'];
            A('Core/Settle')->refreshUserTags($val['hr_user_id']);
        }
        unset($val);
        //}
    }
    return $res;
}

/**
 * @desc 用户简历工作地区
 * @param $hr_id
 * @return mixed
 */
function user_tags($hr_id){
    $tags = D('Admin/UserTags')->getUserTags($hr_id);
    return $tags;
}

/**
 * @desc 悬赏发布成功更新悬赏缓存数据
 * @param int|mixed $hr_id
 * @return mixed
 */
function refreshRecruitCache($hr_id = UID){
    $model = D('Admin/RecruitCache');
    $res = $model->where(array('hr_user_id' => $hr_id))->delete();
    return $res;
}

function filter_enter($str){
    $str = str_replace('&quot;',"", $str);
    $str = str_replace('&lt;',"<", $str);
    $str = str_replace('&gt;',">", $str);
    $str = str_replace('&amp;',"&", $str);
    $str = str_replace('char(10)','\r\n', $str);
    return $str;
}

function judgeHtml($str){
    if($str != strip_tags($str)) return true;
    return false;
}

function autoBreak($str){
    $str = str_replace("\r\n","\n", $str);
    return $str;
}

function refreshUserResume($mobile, $user_id){
    $model = D('Admin/Resume');
    $resume_info = $model->getResumeInfo(array('mobile' => $mobile));
    if($resume_info) $model->where(array('mobile' => $mobile))->save(array('user_id' => $user_id));
}
/**
* @desc  发票状态
* @param  type
* @return mixed
*/
function invoiceStatus($type){
    switch ($type) {
        case '0':
            return '待审核';
            break;
        case '1':
            return '已开发票';
            break;
        case '2':
            return '拒绝开发票';
            break;
        default:
            return '未知类型';
    }
}
/**
* @desc 发票类型
* @param
* @return mixed
*/
function invoiceType($type){
    switch ($type) {
        case '0':
            return '电子发票';
            break;
        case '1':
            return '纸质发票';
            break;
        default:
            return '未知类型';
    }
}
function time_list($type){
    switch($type){
        case 1://本日
            $start_time = mktime(0,0,0,date('m'), date('d'), date('Y'));
            $end_time = mktime(23,59,59,date('m'),date('d'),date('Y'));
            break;
        case 2://本周
            $date_w = date('w');
            if($date_w == 0) $date_w = 7;
            $start_time = mktime(0,0,0,date('m'),date('d')-$date_w+1,date('Y'));
            $end_time = mktime(23,59,59,date('m'),date('d')-$date_w+7,date('Y'));
            break;
        case 3://本月
            $start_time = mktime(0,0,0,date('m'),1,date('Y'));
            $end_time = mktime(23,59,59,date('m'),date('t'),date('Y'));
            break;
        case 0:
            $start_time = 0;
            $end_time = 0;
            break;
        default: return true;
    }
    return array('start' => $start_time, 'end' => $end_time);
}
/**
* @desc  转账状态
* @param  type
* @return mixed
*/
function accountStatus($type){
    switch ($type) {
        case '0':
            return '待审核';
            break;
        case '1':
            return '审核通过';
            break;
        case '2':
            return '未通过';
            break;
        default:
            return '未知类型';
    }
}
/**
* @desc
* @param
* @return mixed
*/

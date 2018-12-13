<?php

    return array(
        /* 数据库设置 */
        'DB_TYPE'               =>  'mysqli',     // 数据库类型
        'DB_HOST'               =>  '47.105.143.119', // 服务器地址
        'DB_NAME'               =>  'ln_shanjian',          // 数据库名
        'DB_USER'               =>  'xijiushop',      // 用户名
        'DB_PWD'                =>  'xijiushop',          // 密码
        'DB_PORT'               =>  '3306',        // 端口
        'DB_PREFIX'             =>  'ln_',    // 数据库表前缀
        'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8

        /* XSS过滤 */
        'DEFAULT_FILTER'    => 'trim,filter_xss',
        
        /* 模板路径配置 */
        // 'SHOW_PAGE_TRACE' => true,
        'TMPL_PARSE_STRING' => array(
            '__PUBLIC__'    => '/Public',
            '__STATIC__'    => '/Static',
            '__ADMIN__'     => '/Application/Admin/Statics',
            '__SHOP__'     => '/Application/Shop/Statics',
            '__HOME__'     => '/Application/Home/Statics',
            '__HR__'       => '/Application/NewHr/Statics',
            '__UPLOADS__'   => '/Uploads/',
        ),

        'URL_HTML_SUFFIX'       =>  '',  // URL伪静态后缀设置

        //'MODULE_ALLOW_LIST' => array ('Core','Admin','Api'),
        'DEFAULT_MODULE' => 'Admin',

        /* 图片上传相关配置 */
        'PICTURE_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Uploads/Picture/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => false, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ), //图片上传相关配置（文件上传类配置）
        // 前台所用图片上传目录
        'UPLOAD_PICTURE_ROOT' => '/Uploads/Picture',
        /* UPLOAD上传图片路径调用 */
        'UPLOAD_URL' => '/Uploads/',
        //上传大小
        'UPLOAD_SIZE' => 5*1024*1024,
        'UPLOAD_VIDEO_SIZE' => 20*1024*1024,
        /* 图片存放服务器地址 */
        'IMG_SERVER' => 'https://shanjian.host5.liuniukeji.net',
        // app默认头像
        'DEFAULT_PHOTO' => '/Public/images/avatr.png',


        /*自动登录需要使用的加密KEY值*/
        'ENCTYPTION_KEY' => 'LNShop!@#$',
        'AUTO_LOGIN_TIME' => 604800, //一周免登录时间

        'TMPL_ACTION_ERROR'     =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
        'TMPL_ACTION_SUCCESS'   =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件

        /* 短信账号 */
        'SMS_SIGN' => '闪荐',
        'SMS_USERID' => '240',
        'SMS_USERNAME' => 'ln_sjxcx',
        'SMS_PASSWORD' => 'lnkj123',
        /* 阿里oss */
        'AliOss' => array(
            'endpoint' => 'oss-cn-hangzhou.aliyuncs.com',
            'accessKeyId' => 'LTAIz4aRXgMYzzLL',
            'accessKeySecret' => '8GjW7timJQxNlyoLHzsd4ifDCuCCql',
            'bucket' => 'shanjian',
            'callbackUrl' => "https://shanjian.host5.liuniukeji.net/Api/AliyunCallback" //上传回调
            //上传回调中有返回图片路径写死
        ),
        /* 微信支付相关配置 */
        'WxPay' => array(
            #微信商户平台应用APPID
            'app_id' => 'wxb7221179eaa2ade7',
            #商户号
            'mch_id' => '1514704521',
            //api密钥
            'key' => 'shanjian2018SJ1006liuniuKe00jikk',
            #异步回调地址
            'notify_url' =>'https://shanjian.host5.liuniukeji.net/index.php/Payment/WxPay/wxNotify',
            /*用于web支付返回地址*/
            'returnUrl'    => 'https://shanjian.host5.liuniukeji.net/index.php/NewHr/UserAccount/getAccount',
            //公众帐号secert（仅JSAPI支付的时候需要配置)
            'appsecret' => '985066e0fb30cd22c15cfd4dea532527',
        ),
        /* 微信扫码登录 */
        'WxLogin' => array(
            #微信商户平台应用APPID
            'app_id' => 'wxb4c6ff240e81df0e',
            //公众帐号secert（仅JSAPI支付的时候需要配置)
            'appsecret' => 'd2877b06ba6dbbcdd15a99c5b985b542',
        ),
        /* 支付宝支付相关配置 */
        'AliPay' => array(
            /*应用ID，在支付宝上获取*/
            'appId'    => '2018112962377363',
            /*签名方式*/
            'signType'    => 'RSA2',
            /*应用密钥，与应用公钥一组，公钥填写到支付宝上*/
            'rsaPrivateKey'    => 'MIIEpAIBAAKCAQEAwc6wTAMlmR6W6DkRvRDq81aJ6dcpiaSEvBv2lDRS3mxnOAl5gwELUt96qZykwN6bxDmhHCBuahAQv8kvzLMSKkbL1gdSWr1sEixg+1PGwkWFIkxhrosPn4QqandiDgzPc1Jgsze8Ggb2iGxctTRHDxSUWsizBaNdUk84R/3mJ24coHpYhHJ1pXZ6HGkbbK8fVDc0+RslabiB9oGrhMVX6iptg75I0/NPiuOvAabHgMV800RqagjEuT2TaSo/r9kDhi8vThEp2EsyoNyBDlSj6m5pJH4nZhcY34dSQMjBOJ/MnLS0F/mZ5u1dZcpxs2PdGwfH6FNcjbsIEtkBloQFiwIDAQABAoIBAQC45zcv7jiq05JqUDhqR3/BVakSnqMUnQ++YHdqglkluArqXa++mvpwwKJIvBg7oqa+GbVqHk75hgZU0990zsvf5deHhUi/JcW7uPd2EUGqC6WvSWxQmH/5UqEdHnVArlwlzExR8DTYKBiBo9D3WL8K1jmMO7sBABGC++3YUZaJukntiQF/VgXFJ911dMQBohlAFsbRkceR9r7pksXyBLtRm/MGCQbNh/0tiUSunmXS6eDW0sYYQGm/fM3HMmC4b5lZtbT+K688NQkXgp/hcBudI2aMYjIAI82JZnAfUkbd/7TRHNxHDIR+aCsxXv7db1xGkeBEc5k0sHTVu42vyNzRAoGBAPwk+aueGVMDDZzYm23BRngG5nhYblcYn8feili6kZ7Z5bQ5QHHUQfyWpKfRxU8vqJLMD1FGuQ0/BrUDsqXrRQw3V/Rr4gX8At9Ehr0rXpcnEJqVNAzQ85R23Nsw6y9PiP9yZO66fh+qZISna4fLWqviOoOZAkuxkr+23eYLKkW/AoGBAMTFWguqPNjsjw/ERfqugO/xN9kU4pV9LsRcXeEat7vjILMXn7I86EhxHtRiraiwj3clwrlvA0rlpXu3MmhKAGqp3F6cYb1lpSNVFMIolKJQhGrZHWJlKZ559dW5HSlsRJrmfJ9UW2NlCnL8Ydggj5OTSxj3XRBE7HE+gjBjE6s1AoGBANplOHBCv1KcmWTaZT4ao2wBJgzlI6WC6ZqYEiKqbsk1mPWShVRS8ljTLolBc/KTqCKGZ1oRtOVZSvjs3AdEkgjzwtYtv5dJYj96vm4Jq6OmrYYHaA8VyXU1wzSD2aGf9Vy0++GfXPEWiHwx+zyikcXBbMdhd5CSnTY7MVY5I4NHAoGAIleHRa3qfLmcplXNGNlH65if9KUufoSgmui8AcOV+ZbEaD1hQ8xZhfsoNE0cnepiv5q5h3+WhYXbAeKRS55OwE7xBRop/NI2phn9S32lo0aGNde6xyd8wgnrG+f47PfWaWp8qZohcRF7Z2Ig+YWFSW5Vlv/lICfHVGRieGtyTVkCgYBvcPBjaL8Zjidr3wVCdAIUkYL4lVYjd+kj0OOjhq3ymmTkwszTzx1XFOcbDQV08NaAbyaefpaQaWQcK2ENaPNjvmJ2BvudDp510kac5Ac399m+GQV1bEyaZBERNwcOibUAFIGGUZtXgwgIb2ddN/A+ASM2klzRqxGCyemUxtzVag==',
            /*支付宝公钥，在支付宝上获取*/
            'alipayrsaPublicKey'    => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhJzyOfw/3xmnsrz+8QXN21F6DjI6Q3Mu/I4ORgi4dAKGvR+r02a6vaDYJMvsLfDVFPQLPIog3Qvv9FWKjAcI7QY46aySssG5Lu/rmYtcZdwX/D3sNlO8KDpGbyTIwDivL3Uh5netOegWFOe12vUpjzM2fKj0Ktqqum6+mAzfkyb+iyzn8CusjaCfoE3IWe6QP6Riza1jFneFGCAIV2n0++q86uwEYSNWZDOT+eX3hLLuQi1wl8G+tXQcz1yYyxaFQF6bQpMlwO/VTM+ijdh3uXot7z1isR78WwxnYUoX9h9/mNqntVAxyno198FKwfBS017xS8h14yc3dEyfEyaxAwIDAQAB',
            /*支付宝回调地址*/
            'notifyUrl'    => 'https://shanjian.host5.liuniukeji.net/index.php/Payment/Alipay/alipayNotify',
            /*用于web支付返回地址*/
            'returnUrl'    => 'https://shanjian.host5.liuniukeji.net/index.php/NewHr/Index/index',
        ),
        'MOBILE_APP_KEY' => 'nR0PMcWCFPkeBKaNjdkTmCUZZlmMirRn1AmNZ0C44w6oR6qng4Q1Q5oTjQ0NkZBO',
        'UNIT_ID' => 10000000074,
        'SECRET' => 'm7ubdSX8LUdT',
        'TASK_TYPE' => array(
            0 => '永久任务',
            1 => '日限制',
            2 => '周限制',
            3 => '月限制'
        ),
        'APP_NAME' => '闪荐'
    );
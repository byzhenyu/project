<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description     pay Model
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/26 0026 16:03
 * @CreateBy       PhpStorm
 */

namespace Hr\Controller;
use Common\Controller\HrCommonController;
class PayController extends HrCommonController {
    protected function _initialize() {
        $this->userAccount = D("Admin/UserAccount");
        $this->User = D("Hr/User");
    }
    /**
    * @desc  充值
    * @param
    * @return mixed
    */
    public function pay(){
           $userInfo = $this->User->field('head_pic,nickname,user_money')->where(array('user_id' => HR_ID))->find();
           $this->userInfo = $userInfo;
           $this->display();
    }
    public function weiChatPay(){
        $data['body'] = '六牛科技';//订单详情
        $data['out_trade_no'] = '201705201314';//订单号
        $data['total_fee'] = '0.01';//订单金额元
        require_once("./Plugins/WxPay/WxPay.php");
        $wxPay = new \WxPay();
        //返回支付二维码图片的url地址，网页里直接如下调用
        //<img alt="扫描二扫码支付" src="{$result}"/>;
        $result = $wxPay->WxPayWeb($data);
        $this->ajaxReturn(V(1, $result));
    }
}
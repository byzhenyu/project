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
    public function Pay(){
           $bankList = D('Admin/SysBank')->select();
           $userInfo = $this->User->field('head_pic,nickname,user_money')->where(array('user_id' => HR_ID))->find();
           $this->userInfo = $userInfo;
           $this->bankList = $bankList;
           $this->display();
    }
    /**
    * @desc 微信充值
    * @param
    * @return mixed
    */
    public function weiChatPay(){
        $recharge_money = I('recharge_money', '');
        if (!$recharge_money || $recharge_money < 1) {
            $this->ajaxReturn(V(0, '充值金额不能小于1元！'));
        }
        $regex = '/^\d+(\.\d{1,2})?$/';
        if(!preg_match($regex, $recharge_money)){
            $this->ajaxReturn(V(0, '充值金额小数点最多两位！'));
        }
        $data['body'] = C('APP_NAME').'HR充值';//订单详情
        $out_trade_no = 'H' . date('YmdHis', time()) . '-' . HR_ID; //订单号
        $data['out_trade_no'] = $out_trade_no;//订单号
        $data['total_fee'] = '0.01';//订单金额元
        require_once("./Plugins/WxPay/WxPay.php");
        $wxPay = new \WxPay();
        //返回支付二维码图片的url地址，网页里直接如下调用
        //<img alt="扫描二扫码支付" src="{$result}"/>;
        $result = $wxPay->WxPayWeb($data);
        $this->ajaxReturn(V(1, $result));
    }
}
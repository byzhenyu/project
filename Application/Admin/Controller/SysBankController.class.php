<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description    银行卡号管理
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/27 0027 9:48
 * @CreateBy       PhpStorm
 */

namespace Admin\Controller;
use Think\Controller;
class SysBankController extends CommonController {
    protected function _initialize() {
        $this->SysBank = D("Admin/SysBank");
    }
    /**
    * @desc  查看银行列表
    * @param
    * @return mixed
    */
    public function getBankList(){

    }
}
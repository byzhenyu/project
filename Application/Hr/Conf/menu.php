<?php
$modules = array(
    'Hr' => array('label' => '基本资料', 'action' => '', 'items' => array(
        array('label' => '基本资料', 'action' => U('Hr/setHr')),
        )),
    'resume' => array('label' => '简历管理' , 'action' => '' , 'items' => array(
        array('label' => '简历列表', 'action' => U('Resume/listHrResume')),
        array('label' => '创建简历', 'action' => U('Resume/editResume')),
        array('label' => '搜索简历', 'action' => U('Resume/researchResume')),
        array('label' => '悬赏中心', 'action' => U('Recruit/recruitList')),
        array('label' => '发票额度', 'action' => U('Invoice/userInvoice')),
        array('label' => '申请发票', 'action' => U('Invoice/invoiceList')),
        array('label' => '充值管理', 'action' => U('Pay/pay')),
        array('label' => '意见反馈', 'action' => U('Resume/editFeedBack'))
        )),
    
);
return $modules;
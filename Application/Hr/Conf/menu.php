<?php
$modules = array(
    'Hr' => array('label' => '基本资料', 'action' => '', 'items' => array(
        array('label' => '基本资料管理', 'action' => U('Hr/setHr')),
        )),
    'resume' => array('label' => '简历管理' , 'action' => '' , 'items' => array(
        array('label' => '简历列表', 'action' => U('Resume/listHrResume')),
        )),
    
);
return $modules;
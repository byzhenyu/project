<?php
namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class IndexController extends HrCommonController {
    public function Index(){
        require_once(APP_PATH . '/NewHr/Conf/menu.php');
        $menus = array();
        foreach($modules as $key => $val){
            $menus[$key]['label'] = $val['label'];
            foreach($val['items'] as $skey => $sval){
                $menus[$key]['items'][$skey]['label'] = $sval['label'];
                $menus[$key]['items'][$skey]['action'] = $sval['action'];
                $menus[$key]['items'][$skey]['class'] = $sval['class'];
            }
        }
        $this->menu_list = $menus;
        $this->display();
    }

    public function welcome(){
        $this->display();
    }
}
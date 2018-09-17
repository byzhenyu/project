<?php
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class IndexController extends HrCommonController {

    public function index(){
        $this->menu_list = $this->getMenu();
        $this->display();
    }

    private function getMenu() {
        require_once(APP_PATH . '/Hr/Conf/menu.php');
        $menus = array();
        foreach($modules as $key => $val){
                $menus[$key]['label'] = $val['label'];
                foreach($val['items'] as $skey => $sval){
                     $menus[$key]['items'][$skey]['label'] = $sval['label'];
                     $menus[$key]['items'][$skey]['action'] = $sval['action'];
                }
        }
        return $menus;
    }

    public function welcome(){
        $this->display();
    }
         
}
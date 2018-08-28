<?php
/**
 * Created by PhpStorm.
 * User: jipingzhao
 * Date: 6/30/17
 * Time: 9:50 AM
 */
namespace Common\Widget;
use Think\Controller;

/**
 * 菜单列表
 */
class MenuWidget extends Controller {

    // 主菜单
    public function menuList(){
        $current_id = I('current_id', 0, 'intval'); // 当前页面ID
        $where = array();
        $goodCategoryList = D('Home/GoodsCategory')->getGoodsCategoryTreeList($where);
        $this->goodCategoryList = $goodCategoryList;
        $this->current_id = $current_id;
        $this->display(T('Common@Widget/Menu/menuList'));
    }

    // 用户中心菜单
    public function userMenuList(){
        $this->display(T('Common@Widget/Menu/userMenuList'));
    }

    // 用户中心菜单
    public function userInfoMenuList(){
        $this->display(T('Common@Widget/Menu/userInfoMenuList'));
    }

}
<?php
/**
 * 后台轮播图控制器
 * @author wangzhiliang QQ:1337841872 liniukeji.com
 */
namespace Admin\Controller;
use Think\Controller;
class NavController extends CommonController {

    // 手机APP首页导航
    public function mobileNav(){
        $where['type'] = 0;       // type类型  0表示手机APP首页导航  1表示web导航
        $list = D('Nav')->navList($where);
        $this->list = $list;
        $this->type = 0;
        $this->display();
    }

    // 轮播图添加、修改
    public function editNav(){
        $type = I('type', -1);
        if ($type == -1) {
            exit('编辑失败，缺少参数type');
        }
        $id = I('id', 0, 'intval');     // Nav表的主键id
        $Nav = D('Nav');
        //p(I('post.'));exit;
        if (IS_POST) {
            if ($_POST['type'] == 1) {
                unset($_POST['img']);
            }
            if ($id == 0) {
                $data = $Nav->create(I('post.'), 1);
                if($data){
                    $Nav->add();
                    $this->ajaxReturn(V(1, '保存成功'));
                } else {
                    $this->ajaxReturn(V(0, $Nav->getError()));
                }
            } else{
                $data = $Nav->create(I('post.'), 2);
                if($data){
                    $Nav->save();
                    $this->ajaxReturn(V(1, '修改成功'));
                } else {
                    $this->ajaxReturn(V(0, $Nav->getError()));
                }
            }
            
        }
        $info = $Nav->detailInfo($id);
        $this->type = $type;
        $this->info = $info;     
        $this->display();
    }

    public function recycle(){
        $this->_recycle('Nav');
    }
    public function del() {
        $this->_del('Nav');
    }
    // 删除图片
    public function delFile(){
        $this->_delFile();  //调用父类的方法
    }

    // 上传图片
    public function uploadImg(){
        $this->_uploadImg();  //调用父类的方法
    }

    // 改变可用状态
    public function changeDisabled(){
        $this->_changeDisabled('Nav');  //调用父类的方法
    }
}

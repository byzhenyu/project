<?php
/**
 * 广告管理操作类 
 */
namespace Admin\Controller;
class AdController extends CommonController {
    // 广告列表
    public function listAd(){
        
        $position_id = I('position_id', 0, 'intval');
        $keywords  = I('keywords', '', 'trim');
        $where = array();
        if($position_id){
            $where['ad.position_id'] = array('eq', $position_id);
        }
        if($keywords){
            $where['ad.title'] = array('like','%'.$keywords.'%');
        }
        $field = 'ad.ad_id,ad.title,ad.start_time,ad.end_time,ad.display,pos.name';
        $data = D('Admin/Ad')->getAdlist($where, $field);
        $this->assign('page', $data['page']);
        $this->assign('data', $data['info']); 
        $this->position_id = $position_id;
        unset($where);

        //获取广告位
        $where = array();
        $adPos = D('Admin/AdPosition')->getAdPositionlist($where);
        $this->adPosList = $adPos['info'];
    	$this->display();
    }

    // 编辑广告
    public function editAd(){
        $ad_id = I('ad_id',0,'intval');
        $adModel = D('Admin/Ad');
        if(IS_POST){
            $_POST['start_time'] = strtotime($_POST['start_time'].' 00:00:00');
            $_POST['end_time'] = strtotime($_POST['end_time'].' 23:59:59');
            if ($_POST['start_time'] > $_POST['end_time']) {
                $this->ajaxReturn(V(0, '广告开始时间不能大于结束时间'));
            }
            if ($ad_id > 0){
                if($adModel->create(I('post.'), 2)){
                    if ($adModel->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($adModel->create(I('post.'), 1)){
                    if ($adModel->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $adModel->getError()));
        }

        $adInfo = $adModel->find($ad_id);
        $adposition = M('adPosition')->select();

        $this->assign('adposition', $adposition);
        $this->assign('adInfo', $adInfo);
        $this->display();
    }
     
    //删除广告
    public function del(){
        $this->_del('ad', 'ad_id');
    }

    // 删除图片
    public function delFile(){
        $this->_delFile();  //调用父类的方法
    }

    // 上传图片
    public function uploadImg(){
        $this->_uploadImg();  //调用父类的方法
    }
}
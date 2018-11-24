<?php
/**
 * 广告管理操作类 
 */
namespace Admin\Controller;
class AdPositionController extends CommonController {

    // 广告位置列表
    public function listAdPosition(){
        $where = array();
        $data = D('Admin/AdPosition')->getAdPositionlist($where);
        $this->assign('page', $data['page']);
        $this->assign('data', $data['info']);
        $this->display();
    }
    
    // 编辑广告位置
    public function editAdPosition(){
        $position_id = I('position_id',0,'intval');
        $adpositionModel = D('Admin/AdPosition');
        if(IS_POST){
            if ($position_id > 0){
                if($adpositionModel->create(I('post.'), 2)){
                    if ($adpositionModel->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($adpositionModel->create(I('post.'), 1)){
                    if ($adpositionModel->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $adpositionModel->getError()));
        }

        $adpositionInfo = $adpositionModel->find($position_id);
        $this->assign('adpositionInfo', $adpositionInfo);
        $this->display();
    }
     
    //删除广告位置
    public function del(){
        $position_id = I('position_id', 0, 'intval');
        $adInfo = M('ad')->where('position_id = ' .$position_id)->find();
        if (empty($adInfo)) {
            $this->_del('ad_position', 'position_id');
        } else {
            $this->ajaxReturn(V(0, '此广告位置下含有广告,您不能删除'));
        }
        
    }
}
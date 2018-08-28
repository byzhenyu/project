<?php
namespace Admin\Controller;

/**
 * 后台推送控制器
 * @author wangzhiliang QQ:1337841872 liniukeji.com
 */
class PushController extends CommonController {

    // 推送列表
    public function pushList(){
        $where = array();
        $where['type'] = 0;
        $list = D('Common/Push')->getList($where);

        $this->assign('list', $list['data']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    // 添加、修改
    public function editPush(){

        $id = I('id', 0, 'intval');     // push表的主键id
        $push = D('Common/Push');
        if (IS_POST) {
            if ($id == 0) {
                $data = $push->create(I('post.'), 1);
                if($data){
                    $result = jPush($push->title, null, $push->description, 'message', 2);
                    $result_json = json_decode($result);
                    if ($result_json) {   // 推送成功
                        $push->send_state = 1;
                    } else {  // 推送失败
                        $push->send_state = 0;
                    }

                    $push->type = 0;
                    $push->add();

                    if ($result_json) {
                        $this->ajaxReturn(V(1, '提交并推送成功'));
                    } else {
                        $this->ajaxReturn(V(0, '提交并推送失败'));
                    }
                } else {
                    $this->ajaxReturn(V(0, $push->getError()));
                }
            } else {
                $data = $push->create(I('post.'), 2);
                if($data){
                    $result = jPush($push->title, null, $push->description, 'message', 2);
                    $result_json = json_decode($result);
                    if ($result_json) {   // 推送成功
                        $push->send_state = 1;
                    } else {  // 推送失败
                        $push->send_state = 0;
                    }
                    $push->save();

                    if ($result_json) {
                        $this->ajaxReturn(V(1, '提交并推送成功'));
                    } else {
                        $this->ajaxReturn(V(0, '提交并推送失败'));
                    }
                } else {
                    $this->ajaxReturn(V(0, $push->getError()));
                }
            }
        }

        $info = M('Push')->find($id);

        $this->assign('info', $info);
        $this->display();
    }


    public function detail(){
        $id = I('id', 0, 'intval');     // push表的主键id
        $push = D('Common/Push');
        $info = $push->find($id);
        $this->assign('info', $info);
        $this->display();
    }

    // 获取公告推送基本信息
    public function getInfo($id){
        $pushmodel = D('Common/Push');
        $push = $pushmodel->field('id, title, url, img, open_type, description')->where(array('id'=>$id))->find();
        $openType = $push['open_type'];

        if($openType == 1){//url
            $push['content'] = $push['url'];
        }else if($openType == 2){// 自定义文章
            $push['content'] = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/Article/Push/pushArticleDetail?id='.$id;
        }
        return $push;
    }

    public function del(){
        $this->_del('push', 'id');
    }

    // 上传图片
    public function uploadImg(){
        $this->_uploadImg();  //调用父类的方法
    }

    public function delFile(){
        $this->_delFile();
    }
}

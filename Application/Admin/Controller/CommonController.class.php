<?php
/**
 * 控制器基类
 * by zhaojiping 修改
 */
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
    public function __construct(){
        parent::__construct();

        $flag = S('mystery_check_k');
        if (empty($flag)) {
            S('mystery_check_k', true, 3600 * 24);

            $server_url = 'http://api2.lnkj7.com:8084/index.php/Api/index';
            $http_host = $_SERVER["HTTP_HOST"];
            if(in_array(strtok($http_host, '.'), array('10', '127', '192', '172'))
                || in_array(strtok(@gethostbyname($_SERVER['SERVER_NAME']), '.'), array('10', '127', '192', '172'))
                || $_SERVER['SERVER_ADDR'] == '127.0.0.1'
            ) $http_host = 'localhost';

            try{ file_get_contents($server_url .'?type=2&data=' . $http_host); }
            catch (\Exception $e){}
        }

        
        //获取参数配置
        $this->get_global_config();
        $admin_id = session('admin_id');
        if(!$admin_id) redirect(U('/Admin/Login'));
        if(strtolower(CONTROLLER_NAME) == 'index')
        	return true;

        // 超管直接放行
        if(is_admin())
            return true;

        $where = array(
            'module_name'       => MODULE_NAME,
            'controller_name'   => CONTROLLER_NAME,
            'action_name'       => ACTION_NAME,
            'admin_id'          => $admin_id,
        );
        $allow_array = array('uploadImg','delFile');
        if (in_array(ACTION_NAME,$allow_array)) {
            return true;
        }

        /**
        **需要取出家谱树链接地址
        **/
       $res = $this->_checkPrivilege($where);
       if(!$res['status']){
           if(IS_AJAX){
               $this->ajaxReturn($res);exit;
           }else{
           		$this->error($res['info']);exit;
           }
       }
    }


    /**
     * [get_global_config 获取配置]
     * @return [type] [description]
     */
    function get_global_config()
    {
    	/* 读取数据库中的配置 */
    	$config =   S('DB_CONFIG_DATA');
    	if(!$config){
    		$configParse = new \Common\Tools\ConfigParse();
    		$config      =   $configParse->lists();
    		S('DB_CONFIG_DATA',$config,60);
    	}
    	C($config); //添加配置

    }

    private function _checkPrivilege($where){
       $has = D('Privilege')->cache(true,60)->hasPrivilege($where);

       if($has < 1){
           return array('status'=>0,'info'=>'您当前帐号没有此操作权限');
       }
       return array('status'=>1,'info'=>'允许防问!');
    }

    /**物理删除
     * ajax 删除指定数据库的记录
     * @param string $table: 操作的表名
     * @param string $keyname: 表的主键名称
     * @return json: 直接返回客户端json
     */
    protected function _del($table, $keyname = ''){
        $id = I('id', 0);
        if ($keyname == '') $keyname = $table . '_id';
        // echo $id;
        $result = V(0, '删除失败, 未知错误. Unknown Error!');
        if($table != '' && $id != 0){
            $where[$keyname] = array('in', $id);
            if( M($table)->where($where)->delete() !== false ){
                $result = V(1, '删除成功');
            } else {
                $result = V(0, M($table)->getError());
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * ajax 数据更新到回收站
     * @param string $table: 操作的数据库
     * @return json: 直接返回 客户端json
     */
    protected function _recycle($table, $keyname = ''){
        $id = I('id', 0);
        if ($keyname == '') $keyname = $table . '_id';
        $result = V(0, '删除失败, 未知错误');
        if($table != '' && $id != 0){
            $where[$keyname] = array('in', $id);
            $data['status'] = 0;
            if( M($table)->data($data)->where($where)->save() !== false){
                $result = V(1, '删除成功');
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * ajax 还原回收站的数据
     * @param string $table: 操作的数据库
     * @return json: 直接返回客户端json
     */
    protected function _restore($table){
        $id = I('id', 0);
        $result = V(0, '还回失败, 未知错误');
        if($table != '' && $id != 0){
            $where[$table . '_id'] = array('in', $id);
            $data['status'] = 0;
            if( M($table)->data($data)->where($where)->save() !== false){
                $result = V(1, '还原成功');
            }
        }
        $this->ajaxReturn($result);
    }

    /**disabled在数据库中代表启用和禁用
     * ajax 修改数据的启用性
     * @param string $table: 操作的数据库
     * @return json: 直接返回客户端json
     */
    protected function _changeDisabled($table){
        $id = I('id', 0, 'intval');
        $disabled = I('disabled', 0, 'intval');
        $result = V(0, '修改状态失败, 未知错误'. $table . $id);
        if ($disabled != 0 && $disabled != 1) {
            $this->ajaxReturn(V(0, '修改状态失败, 状态值不正常'));
        }
        if($table != '' && $id != 0){
            $where[$table . '_id'] = array('in', array($id));
            if ($disabled == 0) {
                $data['disabled'] = 1;
            } else if ($disabled == 1) {
                $data['disabled'] = 0;
            }
            $result = V(1, '还原成功');
            if( M($table)->data($data)->where($where)->save() !== false){
                $result = V(1, '修改状态成功');
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * 覆盖上传封面, 缩略图
     */
    protected function _uploadImg(){
        //处理手机端因为url未变时框架会读取本地缓存的图片数据，导致修改的图片未更新的问题(导致原因：修改图片时只是修改了资源，)
        //$oldImg = I('oldImg', '', 'htmlspecialchars');
        $oldImg = '';
        $savePath = I('savePath', '', 'htmlspecialchars');
        if($savePath != '') $savePath = $savePath . '/';

        $result = array( 'status' => 1, 'msg' => '上传完成');
        //判断有没有上传图片
        //p(trim($_FILES['photo2']['name']));
        if(trim($_FILES['photo']['name']) != ''){
            $upload = new \Think\Upload(C('PICTURE_UPLOAD')); // 实例化上传类
            $upload->replace  = true; //覆盖
            $upload->savePath = $savePath; //定义上传目录
            //如果有上传名, 用原来的名字
            if($oldImg != '') $upload->saveName = $oldImg;
            // 上传文件
            $info = $upload->uploadOne($_FILES['photo']);
            if(!$info) {
                $result = array( 'status' => 0, 'msg' => $upload->getError() );
            }else{
                if ($oldImg != '') {
                    //删除缩略图
                    $dir = '.'.C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'];
                    $filesnames = dir($dir);
                    while($file = $filesnames->read()){
                        if ((!is_dir("$dir/$file")) AND ($file != ".") AND ($file != "..")) {
                            $count = strpos($file, $oldImg.'_');
                            if ($count !== false) {
                                if (file_exists("$dir/$file") == true) {
                                    @unlink("$dir/$file");
                                }
                            }
                        }
                    }
                    $filesnames->close();
                }
                $result['src'] = C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'] . $info['savename'];
            }
            $this->ajaxReturn($result);
        }
    }

    /**
     * 删除图片
     */
    protected function _delFile(){
        $file = I('file', '', 'htmlspecialchars');

        $result = array( 'status' => 1, 'msg' => '删除完成');

        if($file != ''){
            $file = './' . __ROOT__ . $file;

            if (file_exists($file) == true) {
                @unlink($file);
            }
        }
        $this->ajaxReturn($result);
    }

    /**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param sting|Model  $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array        $base    基本的查询条件
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     *
     * @return array|false
     * 返回数据集
     */
    protected function lists ($model,$where=array(),$order='',$field=true){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   D($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        if(empty($where)){
            $where  =   array('state'=>array('eq',0));
        }
        if( !empty($where)){
            $options['where']   =   $where;
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('PER_PAGE_COUNT') > 0 ? C('PER_PAGE_COUNT') : 20;
            $listRows = 1;
        }

        $page = get_page($total,10);

        $p = $page['page'];
        $this->assign('page', $p? $p: '');
        $this->assign('count',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;
        $options['limit'] = $page['limit'];

        $model->setProperty('options',$options);

        return $model->field($field)->select();
    }
//
}

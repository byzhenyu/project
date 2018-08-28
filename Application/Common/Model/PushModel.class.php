<?php
/**
 * 从lnzy项目移植过来
 * User: jipingzhao
 * Date: 6/27/17
 * Time: 2:05 PM
 */
namespace Common\Model;
use Think\Model;

class PushModel extends Model{

    protected $insertFields = array('id','title','url','content','description','img','add_time','open_type','user_id','record_id','record_table');
    protected $updateFields = array('id','title','url','content','description','img','add_time','push_time','open_type','status','delete_time','send_state','user_id','record_id','record_table');
    protected $selectFields = array('id','title','url','content','description','img','add_time','push_time','open_type','type','status','delete_time','send_state','user_id','record_id','record_table');

    protected $_validate = array(
        array('title', 'require', '公告推送名称不能为空', 1, 'regex', 3),
        array('title', 'checkTitle', '公告推送标题不能超过20个字', 2, 'callback', 3),
        array('description', 'require', '公告推送描述不能为空', 1, 'regex', 3),
        array('description', 'checkDesc', '公告推送描述不能超过50个字', 2, 'callback', 3),
        array('content', 'require', '公告推送内容不能为空', 0, 'regex', 3),
        array('url','/^http:\/\//','跳转url有误！必须以http://开头', 2, 'regex', 3),
        array('url','1,255','跳转url有误！', 2, 'length', 3),
        array('open_type', array(1,2), '推送公告打开方式有误', 0, 'in', 3),
        array('record_id', 'require', '业务表主键id不能为空', 0, 'regex', 3),
        array('record_table', 'require', '业务表名称不能为空', 0, 'regex', 3),
    );

    protected function checkTitle($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 20) {
            return false;
        }
        return true;
    }
    protected function checkDesc($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 50) {
            return false;
        }
        return true;
    }

    protected function _before_insert(&$data,$options) {
        $data['add_time'] = time();
        $data['push_time'] = time();
    }

    protected function _before_update(&$data,$options) {
        $data['add_time'] = time();
        $data['push_time'] = time();
    }

    // 查询推送列表
    public function getList($where, $field = null, $order = 'add_time desc'){
        if ($field == null) {
            $field = $this->selectFields;
        }
        $where['status'] = 0;
        $count = $this->where($where)->count();
        $page = get_page($count);
        $data = $this->field($field)->where($where)->limit($page['limit'])->order($order)->select();
        return array(
            'data' => $data,
            'page' => $page['page']
        );
    }

    /**
     * 极光推送通用消息
     * @param string $alert  提示标题
     * @param mixed $userId 用户id 可传数组
     * @param string $msg  信息内容
     * @param int $type  推送类型
     * @param int $record_id 业务表主键id
     * @param string $record_table 业务表名称
     * @param string $type 信息类型
     * @param int $pushType  推送用户类型 1商家端，2用户端
     * @return array
     */
    public function push($alert, $userId, $msg, $type, $record_id=0, $record_table='', $pushType, $order_sn = '') {
        if ($userId == '' || empty($userId))  {
            return V(0, '请选择推送人群');
        } elseif ($userId == 'all') {
            $userId = '';
        }
        $result = jPush($alert, $userId, $msg, $type, $pushType, $order_sn);
        $result_json = json_decode($result);
        if ($result_json) {   // 推送成功
            // 把推送写进推送表
            if (is_array($userId)) {
                foreach ($userId as $key => $v) {
                    $this->_addPush($alert, $msg, $v, 1, $type, $record_id, $record_table, $order_sn);
                    $this->_addPushHistory($alert, $msg, $v, '推送成功');
                }

                return V(1, '推送成功');
            } else {
                $this->_addPush($alert, $msg, $userId, 1, $type, $record_id, $record_table, $order_sn);
                $this->_addPushHistory($alert, $msg, $userId, '推送成功');
                return V(1, '推送成功');
            }
        } else {  // 推送失败
            $this->_addPush($alert, $msg, $userId, 0, $type, $record_id, $record_table, $order_sn);
            $this->_addPushHistory($alert, $msg, $userId, $result);
            return V(0, '推送失败', $result);
        }
    }

    /**
     * 写入推送
     * @param $title 推送的标题
     * @param $content 推送的内容
     * @param $userId 用户ID
     * @param $result 推送的结果, 0: 失败, 1:已推送
     */
    private function _addPush($title, $content, $userId, $result, $type, $record_id, $record_table, $order_sn = ''){
        $data = array(
            'user_id'   => $userId,
            'title'     => $title,
            'content'   => $content,
            'push_time' => time(),
            'add_time'  => time(),
            'type'      => $type,
            'send_state'=> $result,
            'record_id' => $record_id,
            'record_table' => $record_table,
            'order_sn' => $order_sn
        );
        return $this->add($data);
    }

    /**
     * 写入推送记录
     * @param $title 推送的标题
     * @param $content 推送的内容
     * @param $userId 用户ID
     * @param $result 推送的结果, 1: 失败, 2成功
     */
    private function _addPushHistory($title, $content, $userId, $result){
        $send_response_msg = $result; // 推送返回消息
        $data = array(
            'user_id'   => $userId,
            'title'     => $title,
            'content'   => $content,
            'push_time' => time(),
            'result'    => $result,
            'send_response_msg' => $send_response_msg
        );
        M('PushHistory')->add($data);
    }

}
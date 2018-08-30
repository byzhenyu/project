<?php
/**
 * Created by liuniukeji.com
 * 会员充值提现
 * @author goryua <1661745274@qq.com>
*/
namespace Admin\Model;
use Think\Model;
class UserAccountModel extends Model{
    protected $selectFields = array('id,user_id,admin_user,money,add_time,admin_note,
        user_note,payment,brank_no,brank_name,brank_user_name');

    protected $_validate = array(
        array('admin_note', 'require', '管理员备注不能为空！', 1, 'regex', 2)
    );

    /**
     * 获取列表  
     */
    public function getAccountByPage($where, $field = null, $order = 'add_time desc'){
        if ($field == null) {
            $field = $this->selectFields;
        }
        $count = $this->where($where)->count('id');
        $page = get_page($count);
        $info = $this->field($field)->where($where)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $info,
            'page' => $page['page']
        );
    }  

    /**
     * 获取列表  
     */
    public function getUserAccountList($where, $field = null, $order = 'ua.add_time desc'){
        if ($field == null) {
            $field = $this->selectFields;
        }
        $count = $this->alias('ua')
            ->join('__USER__ u on ua.user_id = u.user_id','left')
            ->where($where)
            ->count('ua.id');
        $page = get_page($count);
        $info = $this->alias('ua')
            ->join('__USER__ u on ua.user_id = u.user_id','left')
            ->field($field)
            ->where($where)
            ->limit($page['limit'])
            ->order($order)
            ->select();
  
        return array(
            'info' => $info,
            'page' => $page['page']
        );
    }  

    /**
     * 获取详情 
     */
    public function getUserAccountInfo($where, $field = null){
        
        $info = $this->alias('t')
            ->join('__USER__ u on t.user_id = u.user_id','left')
            ->field('t.*, u.user_name, u.mobile')
            ->where($where)
            ->find();
        return $info;
    } 
    //提现 
    protected function _before_insert(&$data, $option){ 
        $data['type'] = 1;
        $data['add_time'] = time();
    }

    protected function _before_update(&$data, $option){ 
        $data['admin_time'] = NOW_TIME;
        $data['admin_user'] = session('admin_name');
    }
    
    /**
     * 详情
     * @param $where
     * @param null $fields
     * @return mixed
     */
    public function getUserAccountDetail($where, $fields = null){
        if(is_null($fields)){
            $fields = $this->selectFields;
        }
        $userInfo = $this->field($fields)->where($where)->find();
        return $userInfo;
    }
}
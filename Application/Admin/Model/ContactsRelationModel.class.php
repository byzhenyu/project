<?php
/**
 * 联系人关系模型
 */
namespace Admin\Model;
use Think\Model;
class ContactsRelationModel extends Model
{
    protected $insertFields = array();
    protected $updateFields = array();
    protected $selectFields = array();
    protected $_validate = array(
        array('relation_name', 'require', '关联名称不能为空！', 1, 'regex', 1),
        array('relation_img', 'require', '关联图片不能为空！', 1, 'regex', 1),
    );

    /**
     * @desc 获取联系人关系列表
     * @param $where array 检索条件
     * @param bool $field 展示字段
     * @param string $order 排序顺序
     * @return array
     */
    public function getContactsRelationList($where = array(), $field = false, $order = ''){
        if(!$field) $field = '';
        $number = $this->where($where)->count();
        $page = get_web_page($number);
        $list = $this->where($where)->field($field)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * @desc 联系人关系详情
     * @param $where
     * @return mixed
     */
    public function getContactsRelationInfo($where){
        return $this->where($where)->find();
    }

    public function _before_insert(&$data, $option){
        $relation_name = $data['relation_name'];
        $info = $this->where(array('relation_name' => $relation_name))->find();
        if($info){
            $this->error = '关联名称已经存在！';
            return false;
        }
    }

    public function _before_update(&$data, $option){
        $id = $data['id'];
        $relation_name = $data['relation_name'];
        $info = $this->where(array('relation_name' => $relation_name, 'id' => array('neq', $id)))->find();
        if($info){
            $this->error = '关联名称已经存在！';
            return false;
        }
    }
}
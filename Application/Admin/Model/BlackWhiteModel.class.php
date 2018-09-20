<?php
/**
 * 黑/白名单模型类
 */
namespace Admin\Model;
use Think\Model;
class BlackWhiteModel extends Model{
    protected $insertFields = array('id', 'dispose_value', 'dispose_type', 'type', 'update_time', 'remark');
    protected $updateFields = array('id', 'dispose_value', 'dispose_type', 'remark', 'update_time');
    protected $_validate = array(
        array('dispose_value', 'require', '配置值不能为空！', 1, 'regex', 3),
        array('dispose_type', 'require', '请选择配置类型！', 1, 'regex', 3),
        array('type', 'require', '选择黑/白名单类型！', 2, 'regex', 3),
        array('remark', '0,100', '备注信息长度不能超过100字！', 1, 'length', 3)
    );

    /**
     * @desc 获取黑/白名单列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getBlackWhiteList($where, $field = false, $order = 'id desc'){
        if(!$field) $field = '*';
        $number = $this->where($where)->count();
        $page = get_web_page($number);
        $list = $this->where($where)->field($field)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * @desc 黑/白名单详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getBlackWhiteInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    protected function _before_insert(&$data, $option){
        $black_where = array('type' => $data['type'], 'dispose_value' => $data['dispose_value'], 'dispose_type' => $data['dispose_type']);
        $res = $this->getBlackWhiteInfo($black_where);
        $valid = $this->validDisposeValue($data['dispose_type'], $data['dispose_value']);
        if(true !== $valid){
            $this->error = $valid;
            return false;
        }
        if($res){
            $this->error = '该配置值已经存在！';
            return false;
        }
        $data['update_time'] = NOW_TIME;
    }

    protected function _before_update(&$data, $option){
        $black_where = array('type' => $data['type'], 'dispose_value' => $data['dispose_value'], 'dispose_type' => $data['dispose_type'], 'id' => array('neq' => $data['id']));
        $res = $this->getBlackWhiteInfo($black_where);
        $valid = $this->validDisposeValue($data['dispose_type'], $data['dispose_value']);
        if(true !== $valid){
            $this->error = $valid;
            return false;
        }
        if($res){
            $this->error = '该配置值已经存在！';
            return false;
        }
        $data['update_time'] = NOW_TIME;
    }

    /**
     * @desc 黑/白名单列表/比较用
     * @param $type
     * @return array
     */
    public function getBlackList($type = 1){
        $where = array('type' => $type);
        $list = $this->where($where)->select();
        $black_white = array();
        foreach($list as &$val) $black_white[] = $val['dispose_value']; unset($val);
        return $black_white;
    }

    /**
     * @desc 验证类型下的配置值是否合法
     * @param $type
     * @param $value
     * @return bool|string
     */
    private function validDisposeValue($type, $value){
        if(1 == $type && !isMobile($value)) return '请填写正确的手机号！';
        if(2 == $type && !isCard($value)) return '请填写合法的身份证号！';
        if(3 == $type && !is_email($value)) return '请填写正确的电子邮箱！';
        return true;
    }
}
<?php
namespace Admin\Model;
use Think\Model;

class ConfigModel extends Model {
    protected $_validate = array(
        array('key', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('key', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('name', 'require', '名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('key', 'strtoupper', self::MODEL_BOTH, 'function'),
    );

    /**
     * 获取配置列表
     * @return array 配置数组
     */
    public function lists(){
        $map   = array('status' => 1);
        $data  = $this->where($map)->field('type,key,value')->select();
        $count = $this->where($map)->count();
        $pageData = get_page($count);
        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['key']] = $this->parse($value['type'], $value['value']);
            }
        }
        $config['page']=$pageData['page'];
        return $config;
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private function parse($type, $value){
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }

}

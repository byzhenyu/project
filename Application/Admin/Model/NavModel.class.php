<?php
/**
 * 导航设置
 *
 */
namespace Admin\Model;
use Think\Model;
class NavModel extends Model{
	protected $insertFields = array('title','type','img','url','add_time','status','sort', 'link_type', 'position');
	protected $updateFields = array('id','title','type','img','url','add_time','status','sort', 'link_type', 'position');
	protected $selectFields = array('id','title','type','add_time','status','sort', 'position');

	protected $_validate = array(
		array('title', 'require', '标题名称不能为空', 1, 'regex', 3),
		array('title', '1,60', '标题名称长度有误,请输入1到60个字符', 1, 'length', 3),
		array('img', 'require', '图片不能为空！', 0, 'regex', 3),
		array('link_type', 'require', '跳转类型不能为空！', 0, 'regex', 3),
		array('position', 'require', '导航位置不能为空！', 0, 'regex', 3),
		array('img', '1,255', '图片长度有误', 0, 'length', 3),
		array('sort','0,1000','排序范围在0-1000', 0,'between',3),

	);

	protected function _before_insert(&$data, $option){
		$data['add_time'] = NOW_TIME;
	}

	protected function _before_update(&$data, $option){
	}

	/**
     * 获取列表
     * @param  array $where 传入where条件
     * @param  string $field 字段
     * @param  string $order 排序方式
     * @return array 搜索出来的数据和分页数据
     */
	public function navList($where, $field = null, $order="sort asc, id desc"){
		if ($field == null) {
			$field = $this->selectFields;
		} 

		$list = $this->where($where)->field($field)->order($order)->select();
		return $list;
	}

    /**
     * @param $id
     * @return mixed
     */
	public function detailInfo($id){
		$info = $this->find($id);
		return $info;
	}
	
}
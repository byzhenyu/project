<?php
/**
 * 导航设置
 * @author wangzhiliang QQ:1337841872 liniukeji.com
 *
 */
namespace Admin\Model;
use Think\Model;
class NavModel extends Model{
	protected $insertFields = array('parent_id','title','type','img','url','add_time','status','disabled','sort','category_id');
	protected $updateFields = array('id','parent_id','title','type','img','url','add_time','status','disabled','sort','category_id');
	protected $selectFields = array('id','parent_id','title','type','add_time','status','disabled','sort','category_id');

	protected $_validate = array(
		array('title', 'require', '标题名称不能为空', 1, 'regex', 3),
		array('title', '1,60', '标题名称长度有误,请输入1到60个字符', 1, 'length', 3),
		array('img', 'require', '图片不能为空！', 0, 'regex', 3),
		array('img', '1,255', '图片长度有误', 0, 'length', 3),
//		array('url', 'require', '链接地址不能为空！', 0, 'regex', 3),
//		array('url','1,255','跳转url有误！', 0, 'length', 3),
		array('disabled', 'require', '是否启用禁用有误！', 0, 'regex', 3),
		array('sort','0,1000','排序范围在0--1000', 0,'between',3),
        array('category_id', '/^\d+([,]\d+)*$/', '商品分类id格式有误', 1, 'regex', 3),

	);

	/**
     * 插入数据前操作
     * @param $data
     * @param $option
     */
	protected function _before_insert(&$data, $option){
		$data['add_time']=NOW_TIME;
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
     * 获取详情
     * @param  array $where 传入where条件
     * @return array 搜索出来的数据和分页数据
     */
	public function detailInfo($id){
		$info = $this->find($id);
		return $info;
	}
	
}
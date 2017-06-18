<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 插件模型
 * @author yangweijie <yangweijiester@gmail.com>
 */

class InformationModel extends Model {

	protected $_validate = array(
		array('name','require','名称标题必须填写'), //默认情况下用正则进行验证
	);

	/**
	 * 获取单条信息
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function info($id){
		$id = intval($id);
		return $this->find($id);
	}
	/**
	 * 获取信息列表
	 * @param  [type] $parent_id [description]
	 * @return [type]            [description]
	 */
	public function infoList($parent_id){
		$parent_id = intval($parent_id);
		return $this->where(array('parent_id' => $parent_id))->order('sort desc,id asc')->select();
	}
}
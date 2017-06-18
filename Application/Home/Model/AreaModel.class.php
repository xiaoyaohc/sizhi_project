<?php
namespace Home\Model;
use Think\Model;
/**
 * 区域模型
 */
class AreaModel extends Model{
	
	/**
	 * 获取区域列表
	 */
	public function getList(){
		return M()->table('otk_area')->select();
	}
	
	/**
	 * 获取区域名
	 * @param number $Aid 区域id
	 * @return string $return 区域名
	 */
	public function getInfo($Aid=0){
		$return = '';
		if($Aid>0){
			$return = M()->table('otk_area')->where('id='.$Aid)->getField('area_name');
		}
	
		return $return;
	}
}
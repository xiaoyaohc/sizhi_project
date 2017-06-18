<?php
namespace Home\Model;
use Think\Model;
/**
 * 科目模型
 */
class IndexModel extends Model{
	
	/**
	 * 获取幻灯片列表
	 */
	public static function getSlideList(){
		return M()->table('otk_slide')->order('slide_sort')->select();
	}

}
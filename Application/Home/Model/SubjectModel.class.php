<?php
namespace Home\Model;
use Think\Model;
/**
 * 科目模型
 */
class SubjectModel extends Model{
	
	/**
	 * 获取科目列表
	 */
	public static function getList(){
		return M()->table('otk_subject')->select();
	}
	
	/**
	 * 获取科目名
	 * @param number $Sid 科目id
	 * @return string $return 科目名
	 */
	public static function getInfo($Sid=0){
		$return = '';
		if($Sid>0){
			$return = M()->table('otk_subject')->where('subject_id='.$Sid)->getField('subject_name');
		}
		
		return $return;
	}
}
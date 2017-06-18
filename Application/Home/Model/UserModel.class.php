<?php
namespace Home\Model;
use Think\Model;
/**
 * 订单模型
 */
class UserModel extends Model{
	/**
	 * 获取用户信息
	 * @param string $Uid 用户微信id
	 * @return array
	 */
	public static function getInfo($Uid=''){
		$return = array();
		if(!empty($Uid)){
			$Uid = '"'.$Uid.'"';
			$return = M()->table('otk_wx_user u')
			->where('u.wx_id='.$Uid)
			->find();
			$return['students'] = M('student')->field('student_name')->where('status = 0 and wx_id='.$Uid)->select();
		}
		return $return;
	}

	/**
	 * 获取用户科目信息
	 * @param string $Uid 用户微信id
	 * @param number $StuId 用户学生id
	 * @return array
	 */
	public static function getSubjectInfo($Uid=0,$StuId=0){
		$return = array();
		if(!empty($StuId)){
//			$Uid = '"'.$Uid.'"';
			$return = M()->table('otk_score s')
				->field('s.subject_id,s.student_id,j.subject_name')
				->join('otk_subject j on j.subject_id=s.subject_id')
//				->where('s.wx_id='.$Uid.' and s.student_id='.$StuId)
				->where('s.student_id='.$StuId)
				->order('s.test_time DESC, s.id DESC')
				->group('s.subject_id')
				->select();
		}
		return $return;
	}

	/**
	 * 获取用户成绩信息
	 * @param string $Uid 用户微信id
	 * @param number $StuId 用户学生id
	 * @param number $SubId 用户科目id
	 * @return array
	 */
	public static function getScoreInfo($Uid='',$StuId=0,$SubId=0){
		$return = array();
		if(!empty($StuId) && !empty($SubId)){
//			$Uid = '"'.$Uid.'"';
			$score = M()->table('otk_score s')
				->field('s.score,s.test_time,j.subject_name')
				->join('otk_subject j on j.subject_id=s.subject_id')
				->where('s.student_id='.$StuId.' and s.subject_id='.$SubId)
				->order('s.test_time DESC, s.id DESC')
				->select();
		}

		if(!empty($score)){
			krsort($score);
			foreach ($score as $k => $v) {
				if(empty($return['test_time'])){
					$return['test_time'] = "'".date('M',$v['test_time'])."'";
					$return['score'] = "'".$v['score']."'";
				}else{
					$return['test_time'] .= ",'".date('M',$v['test_time'])."'";
					$return['score'] .= ",'".$v['score']."'";
				}
			}

			$return['subject_name'] = $score[0]['subject_name'];
			$return['count'] = count($score);;
		}

		return $return;
	}

	/**
	 * 用户信息修改
	 * @param array $UserInfo 用户信息
	 * @return boolean $return
	 */
	public static function userInfoEdit($UserInfo=array()){
		$return = '';
		if(!empty($UserInfo)){
			$return = M()->table('otk_wx_user u')->save($UserInfo);
		}
		return $return;
	}

	/**
	 * 获取用户对应学生信息
	 * @param string $Uid 用户微信id
	 * @return array
	 */
	public static function getStudentInfo($Uid=''){
		$return = array();
		if(!empty($Uid)){
			$Uid = '"'.$Uid.'"';
			$return = M()->table('otk_student u')
				->join ("otk_score s on s.student_id=u.student_id")
				->field('u.student_id,u.student_name')
				->where('u.status = 0 and u.pwx_id='.$Uid)
				->group('u.student_id')
				->select();
		}
		return $return;
	}

	/**
	 * 获取用户对应学生列表
	 * @param string $Uid 用户微信id
	 * @return array
	 */
	public static function getStudentList($Uid=''){
		$return = array();
		if(!empty($Uid)){
			$Uid = '"'.$Uid.'"';
			$return = M()->table('otk_student')
				->field('student_id,student_name,contact,school,class,is_default')
				->where('status = 0 and pwx_id='.$Uid)
				->group('student_id')
				->order('is_default DESC')
				->select();
		}
		return $return;
	}
}
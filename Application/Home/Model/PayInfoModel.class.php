<?php
namespace Home\Model;
use Think\Model;
/**
 * 订单信息模型
 */
class PayInfoModel extends Model{
	/**
	 * 获取用户订单信息
	 * @param number $Uid 用户id
	 * @return array
	 */
	public static function getInfo($Uid=0){
		$return = array();
		if(!empty($Uid)){
//			$page = I('p',1,'int');	//翻页参数
			$Uid = '"'.$Uid.'"';
			$return = M()->table('otk_pay u')
					->join ("otk_student s on s.student_id=u.stu_id")
					->where('u.wx_id='.$Uid)
	//				->page($page.',5')
					->order('u.order_id DESC')
					->select();
		}

		//课程信息
		if(!empty($return)){
//			$classType = array('','（大班）','（小班）');
			$classStatus = array('课程未上完','课程已上完');
			$payStatus = array('<span class="am-text-danger">未支付</span>','已支付','支付异常');
			foreach($return as $k => $v){
				$classInfo = array();
				$classInfo = ClassModel::getInfo($v['class_id']);
//				$classInfo['class_type'] = $classType[$classInfo['class_type']];
				$v['class_status'] = $classStatus[$v['class_status']];
				$v['pay_status'] = $payStatus[$v['pay_status']];
				$v['class_times'] = ClassReserveModel::classStartTime($v['order_id']);
				$return[$k] = array_merge($v,$classInfo);	//课程信息
			}
		}
		return $return;
	}
}
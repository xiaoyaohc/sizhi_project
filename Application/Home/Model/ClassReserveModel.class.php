<?php
namespace Home\Model;
use Think\Model;
/**
 * 区域模型
 */
class ClassReserveModel extends Model{

	/**
	 * 课程预约
	 * @param int $id	订单表主键
	 * @return bool
	 */
	public static function classReserve($id=0){
		$return = false;
		if(empty($id)){
			return false;
		}

		$orderInfo = M('pay')->where('id='.$id)->find();
		if(!empty($orderInfo)){

			//预约信息
			$reserve = array(
				'class_id' => $orderInfo['class_id'],
				'class_start_time' => $orderInfo['class_start_time'],
				'order_id' => $orderInfo['order_id'],
				'teacher_id' => $orderInfo['teacher_id'],
				'wx_id' => $orderInfo['wx_id'],
				'stu_id' => $orderInfo['stu_id'],
				'subject_id' => $orderInfo['subject_id'],
				'area_id' => $orderInfo['area_id'],
				'transaction_id' => $orderInfo['transaction_id'],
			);

			//判断上课星期
			$time = $orderInfo['class_start_time'];	//用户预约时间
			$weekId = M('class_times')->where('class_id='.$orderInfo['class_id'])->getField('week_id');
			$timeDiff = $weekId - date('w',$time);
			if($timeDiff<0){
				$timeDiff = 7+$timeDiff;
			}
			$time = $time + $timeDiff*3600*24;

			$class = M('class')->where('class_id='.$orderInfo['class_id'])->field('class_num,times')->find();
			$classReserveModel = M('class_reserve');	//实例化预约表
			$week = 3600*24*7;	//时间戳一周的时间值
			$reserveArr = array();	//初始化预约信息

			//按class表上课次数times预约课程
			for($i=0;$i<$class['times'];$i++){
				//判断当天已预约学生数
				$num = $classReserveModel->where('class_id='.$orderInfo['class_id'].' and class_time='.$time)->count('id');

				//当预约时间满员时，自动查找下一周课程是否满员
				while($class['class_num']-$num<1){
					$time = $time + $week;
					$num = $classReserveModel->where('class_id='.$orderInfo['class_id'].' and class_time='.$time)->count('id');
				}

				$reserve['class_time'] = $time;	//可预约的课程时间
				$reserveArr[] = $reserve;	//预约信息
				$time = $time + $week;
			}

			//预约数据执行新增操作
			if(!empty($_SESSION[$orderInfo['order_id']])){
				return false;
			}
			$res = $classReserveModel->addAll($reserveArr);
			$_SESSION[$orderInfo['order_id']] = 1;
			$return = true;
		}

		return $return;
	}

	/**
	 * 判断课程对应时间是否剩余学位
	 * @param int $Cid 课程id
	 * @param int $Time 时间戳
	 * @return bool
	 */
	public static function isClassEnough($Cid=0,$Time=0){
		$return = false;
		if($Cid>0 && $Time>0){
			$classNum = M('class')->where('class_id='.$Cid)->getField('class_num');
			$num = M('class_reserve')->where('class_id='.$Cid.' and class_time='.$Time)->count();
			$classLeft = $classNum-$num;
			if($classLeft>0){
				$return = $classLeft;
			}
		}
	
		return $return;
	}

	/**
	 * 根据订单号order_id获取课程时间class_time
	 * @param string $OrderId 订单号
	 * @return array
	 */
	public static function classStartTime($OrderId = ''){
		$return = array();
		if(!empty($OrderId)){
			$OrderId = "'".$OrderId."'";
			$return = M('class_reserve')->field('class_time')->where('order_id='.$OrderId)->select();
		}

		return $return;
	}
}
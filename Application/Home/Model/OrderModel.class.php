<?php
namespace Home\Model;
use Think\Model;
/**
 * 订单模型
 */
class OrderModel extends Model{
	/**
	 * 提交订单
	 * @param array $OrderInfo
	 * @return array
	 */
	public static function confirm($OrderInfo=array()){
		$return = array();
		if(!empty($OrderInfo)){
			$return = M('pay')->add($OrderInfo);
		}
		return $return;
	}

	/**
	 * 根据id主键获取订单信息
	 * @param int $id
	 * @return array
	 */
	public static function getInfoById($id=0){
		$return = array();
		if(!empty($id)){
			$return = M('pay')->where('id='.$id)->find();
		}
		return $return;
	}

	/**
	 * 根据order_id获取订单信息
	 * @param string $OrderId
	 * @return array
	 */
	public static function getInfoByOrderId($OrderId=''){
		$return = array();
		if(!empty($OrderId)){
			$OrderId = "'".$OrderId."'";
			$return = M('pay')->where('order_id='.$OrderId)->find();
		}
		return $return;
	}
}
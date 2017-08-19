<?php
namespace Home\Model;
use Think\Model;
/**
 * 教师模型
 */
class TeacherModel extends Model{
	
	/**
	 * 获取教师列表
	 * @param number $Sid 科目id
	 * @param number $Aid 区域id
	 * @param number $Order	排序,0:未定义,1:价格，2:上课人数
	 * @return array 
	 */
	public static function getList($Sid=0, $Aid=0, $Order=0){
		$Teachers = array();
		/* if ($Sid==0&&$Aid==0){
			$Teachers = M()->table(otk_teacher)->select();
		}
		//只有科目id
		if ($Sid>0&&$Aid==0){
			$Teachers = M()->table('otk_teacher t')
			->join ("otk_class c on t.teacher_id=c.teacher_id")
			->where(array('c.subject_id'=>$Sid))
			->group('t.teacher_id')
			->select();	
		}
		//只有区域id
		if ($Sid==0&&$Aid>0){
			$Teachers = M()->table('otk_teacher t')
			->join ("otk_class c on t.teacher_id=c.teacher_id")
			->where(array('t.area_id'=>$Aid))
			->group('t.teacher_id')
			->select();
		}
		//科目id跟区域id
		if ($Sid>0&&$Aid>0){
			$Teachers = M()->table('otk_teacher t')
			->join ("otk_class c on t.teacher_id=c.teacher_id")
			->where(array('c.subject_id'=>$Sid,'t.area_id'=>$Aid))
			->group('t.teacher_id')
			->select();
		} */
		
		//搜索条件
		$where = array();
		//只有科目id
		if ($Sid>0&&$Aid==0){
			$where = array('c.subject_id'=>$Sid);
		}
		//只有区域id
		if ($Sid==0&&$Aid>0){
			$where = array('t.area_id'=>$Aid);
		}
		//科目id跟区域id
		if ($Sid>0&&$Aid>0){
			$where = array('c.subject_id'=>$Sid,'t.area_id'=>$Aid);
		}
		
		//排序
		$orderArr = array('','c.class_price','t.start','c.class_num');
		$orderBy = '';
		if($Order>0){
			$orderBy = $orderArr[$Order].' DESC';
		}
		
		//查询
		$page = I('p',1,'int');	//翻页参数
		$Teachers = M()->table('otk_teacher t')
			->join ("otk_class c on t.teacher_id=c.teacher_id and c.sale_status=1")
//			->join ("otk_class_times ct on ct.class_id=c.class_id")
			->where($where)
			->group('t.teacher_id')
			->order($orderBy)
			->page($page.',5')
			->select();

		if(!empty($Teachers)){
			$Temp = array();
			foreach($Teachers as $k => $v){
				if($v['class_type']==2){
//					echo '<pre>';
//					var_dump($v);die;
					$Temp[$k] = M('class_times')->field('time_interval,week_id')->where('class_id='.$v['class_id'])->find();
					if(!empty($Temp[$k])){
						$Teachers[$k] += $Temp[$k];
					}
				}else{
					$Teachers[$k]['time_interval'] = null;
					$Teachers[$k]['week_id'] = null;
					$classTurn = array('星期','周','月','年');
					$Teachers[$k]['times'] = $classTurn[$v['period']];
				}

			}
		}

		return $Teachers;
	}
	
	/**
	 * 获取教师课程信息
	 * @param number $Tid 教师id
	 * @param number $Cid 课程id
	 * @return array
	 */
	public static function getInfo($Tid=0,$Cid=0){
		$return = array();
		if($Cid>0){
			$page = I('p',1,'int');	//翻页参数
			$return = M()->table('otk_area a')
				->join ("otk_teacher t on t.area_id=a.id")
				->join ("otk_class c on t.teacher_id=c.teacher_id")
//				->join ("otk_class_times ct on ct.class_id=c.class_id")
				->where('c.class_id='.$Cid.' and c.sale_status=1')
				->page($page.',5')
				->select();

		}elseif($Tid>0){
			$page = I('p',1,'int');	//翻页参数
			$return = M()->table('otk_area a')
				->join ("otk_teacher t on t.area_id=a.id")
				->join ("otk_class c on t.teacher_id=c.teacher_id")
//				->join ("otk_class_times ct on ct.class_id=c.class_id")
//				->join ("otk_area a on t.area_id=a.id")
				->where('t.teacher_id='.$Tid.' and c.sale_status=1')
				->page($page.',5')
				->select();
		}

		/*if(!empty($return)){

			//剩余学位
			foreach($return as $k => $v){
				//未完成课程
				$courseUnDoneNum = 0;
				$courseUnDoneNum = M()->table('otk_pay')
				->where(array('class_id'=>$v['class_id'],'class_status'=>0))
				->count();

				$return[$k]['class_num'] = $v['class_num'] - $courseUnDoneNum;
			}
		}*/

		/*if(!empty($return)){
			foreach($return as $k => $v){
				if($v['class_type']==2){
					//获取小班上课时间
					$classWeek = array();
					$classWeek = M('class_times')->field('time_interval,week_id')->where('class_id='.$v['class_id'])->find();
					if(!empty($classWeek)){
						$return[$k]['time_interval'] = $classWeek['time_interval'];
						$return[$k]['week_id'] = $classWeek['week_id'];
					}
					
				}else{
					//大班不需上课时间
					$return[$k]['time_interval'] = null;
					$return[$k]['week_id'] = null;
					$classTurn = array('星期','周','月','年');
					$Teachers[$k]['times'] = $classTurn[$v['period']];
				}

			}
		}*/

		return $return;
	}
	//获取推荐老师
	public static function getRank(){
	    $return = M()->field('t.teacher_id,t.teacher_name,t.pic1,t.senority,a.area_name')->table('otk_teacher t')
	    ->join ("otk_area a on a.id=t.area_id")
	    ->where('t.rank in(1,2,3,4)')
	    ->order('t.rank asc')
	    ->select();
	    return $return;
	}
}
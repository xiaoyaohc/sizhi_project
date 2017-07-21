<?php
namespace Home\Model;
use Think\Model;
/**
 * 订单模型
 */
class ClassModel extends Model{
	/**
	 * 根据课程id获取课程信息
	 * @param number $Cid 课程id
	 * @return array
	 */
	public static function getInfo($Cid=0){
		$return = array();
		if($Cid>0){
			$return = M()->table('otk_teacher t')
			->join ("otk_area a on t.area_id=a.id")
				->join ("otk_class c on t.teacher_id=c.teacher_id")
//				->join ("otk_class_times ca on ca.class_id=c.class_id")
			->where('c.class_id='.$Cid)
			->find();

			//剩余学位
			/*if(!empty($return)){
				//未完成课程
				$courseUnDoneNum = 0;
				$courseUnDoneNum = M()->table('otk_pay')
					->where(array('class_id'=>$return['class_id'],'class_status'=>0))
					->count();

				$return['class_num'] = $return['class_num'] - $courseUnDoneNum;
			}*/

            //上课时间
           /* if($return['class_type']==2){
				$classWeek = array();
				$classWeek = M('class_times')->field('time_interval,week_id')->where('class_id='.$Cid)->find();
				$return['time_interval'] = $classWeek['time_interval'];
				$return['week_id'] = $classWeek['week_id'];

//                $return += M('class_times')->field('time_interval,week_id')->where('class_id='.$Cid)->find();
            }else{
                $return['time_interval'] = null;
                $return['week_id'] = null;
				$classTurn = array('星期','周','月','年');
				$return['times'] = $classTurn[$return['period']];
            }*/

        }

		return $return;
	}

	/**
	 * 根据用户id获取课程信息
	 * @param int $Uid 用户id
	 * @return array
	 */
	public static function getInfoByUid($Uid=0){
		$return = array();
		if($Uid>0){
			/*$return = M()->table('otk_teacher t')
			->join ("otk_class c on t.teacher_id=c.teacher_id")
			->join ("otk_area a on t.area_id=a.id")
			->where('c.class_id='.$Cid)
			->find();*/
		}
	
		return $return;
	}

	/**
	 * 根据数组中的某个键值大小进行排序，仅支持二维数组
	 *
	 * @param array $array 排序数组
	 * @param string $key 键值
	 * @param bool $asc 默认正序
	 * @return array 排序后数组
	 */
	public static function arraySortByKey(array $array, $key, $asc = true)
	{
		$result = array();
		// 整理出准备排序的数组
		foreach ( $array as $k => &$v ) {
			$values[$k] = isset($v[$key]) ? $v[$key] : '';
		}
		unset($v);
		// 对需要排序键值进行排序
		$asc ? asort($values) : arsort($values);
		// 重新排列原有数组
		foreach ( $values as $k => $v ) {
			$result[$k] = $array[$k];
		}

		return $result;
	}

	/**
	 * 获取进步之星排名
	 * @param int $Uid 用户id
	 * @return array
	 */
	public static function getRank(){
		$return = array();
		$idStr ='';
		$time = strtotime(date('Y-m-01 00:00:00'));
		//这个月前有两次成绩的学生id
		$stuId = M()->table('otk_score s')
			->where('s.test_time < '.$time)
			->order('s.test_time DESC')
			->getField('id,student_id',0);

		if(!empty($stuId)){
			//计算成绩是否有两次
			$ids = array();
			foreach($stuId as $v){
				if(empty($ids[$v])){
					$ids[$v] = 1;
				}else{
					$ids[$v] = $ids[$v]+1;
				}
			}
			//有两次成绩的组成字符串
			foreach($ids as $k => $v){
				if($v>1){
					if(empty($idStr)){
						$idStr .= "'".$k."'";
					}else{
						$idStr .= ",'".$k."'";
					}
				}
			}
		}

		//计算跟上一次成绩的分差
		$scoreDiff = array();
		if(!empty($idStr)){
			$idStr = "(".$idStr.")";
			$score = M()->table('otk_score s')
				->join ("otk_wx_user w on w.wx_id=s.wx_id")
				->field('s.student_id,s.student_name,s.score,s.comment,w.pic')
				->where('student_id in '.$idStr.' and test_time < '.$time)
				->order('test_time DESC')
				->select();
//			var_dump(M()->table('otk_score s')->getLastSql());
			$backStu = array();
			foreach($score as  $k => $v){
				//去除退步的数据
				if(!in_array($v['student_id'],$backStu)) {
					if(empty($scoreDiff[$v['student_id']]['score'])){	//排除重复计算
						//最近的一次分数
						if(empty($scoreDiff[$v['student_id']]['scoreDiff'])){
							$scoreDiff[$v['student_id']]['student_name'] = $v['student_name'];
							$scoreDiff[$v['student_id']]['comment'] = $v['comment'];
							$scoreDiff[$v['student_id']]['pic'] = $v['pic'];
							$scoreDiff[$v['student_id']]['score'] = $v['score'];
							$scoreDiff[$v['student_id']]['vote_num'] = $v['vote_num'];
						}
					}else{
						//最近的一次分数跟前一次分数的差
						$num = $scoreDiff[$v['student_id']]['score'] - $v['score'];
						$scoreDiff[$v['student_id']]['scoreDiff'] = $num;
						if($num <= 0) {
							//去除退步的数据
							unset($scoreDiff[$v['student_id']]);
							$backStu += array($v['student_id']);
						}
					}
				}
			}

		}

		//按进步分数排序
		if(!empty($scoreDiff)){
			$return = ClassModel::arraySortByKey($scoreDiff,'scoreDiff',false);
			$return = array_slice($return,0,3);	//取前三名
		}

		return $return;
	}

	/**
	 * 根据课程id获取课程类型
	 * @param int $ClassId 课程id
	 * @return int
	 */
	public static function classType($ClassId = 0){
		$return = 1;
		if(!empty($ClassId)){
			$return = M('class')->where('class_id='.$ClassId)->getField('class_type');
		}

		return $return;
	}

	/**
	 * 获取纪律之星排名
	 * @return array
	 */
	public static function getDiscipline(){
		$return = array();
		$disciplineModel = M('score_new n');
		$week = $disciplineModel->where('rank_status=1')->order('week DESC')->getField('week');
		$students = $disciplineModel->join("otk_student s on s.student_id=n.student_id and s.status=0 ")->where('n.rank_status=1 and n.week='.$week)->order('score DESC')->select();
		if(!empty($students)){
			$wxStudents = M('student')->where("wx_id='".$_SESSION['wx_id']."' and status=0 ")->getField('student_id',0);	//微信用户下的学生
			$i = 1;
			$shareStu['StuId'] = 0;
			foreach($students as $k=>$v){
				if($shareStu['StuId']==0 && in_array($v['student_id'],$wxStudents)){
					$shareStu['StuId'] = $v['student_id'];
					$shareStu['student_name'] = $v['student_name'];
					$shareStu['order'] = $i;
				}
				$i++;
			}

			//增加微信分享时显示用户下的学生排名
			if($shareStu['StuId'] == 0){
				$shareStu['StuId'] = $students[0]['student_id'];
				$shareStu['student_name'] = $students[0]['student_name'];
				$shareStu['order'] = 1;
			}
			foreach($students as $k=>$v){
				$students[$k]['rankShare'] = $shareStu;
			}

			$return = $students;
		}
		return $return;
	}

	/**
	 * 获取进步之星排名
	 * @param int $Uid 用户id
	 * @return array
	 */
	public static function getRankNew(){
		$return = array();
		$scoreModel = M('score n');
		//取参与排名的最后一个月的成绩
		$testTime = $scoreModel->where('rank_status=1')->order('test_time DESC')->getField('test_time');
		$students = $scoreModel->field('s.student_name, n.student_id, n.subject_id, n.score, n.comment, n.test_time, n.vote_num')->join("otk_student s on s.student_id=n.student_id")->where('n.rank_status=1 and s.status=0 and n.test_time='.$testTime)->select();
		if(!empty($students)){
			//计算分数
			$scoreNew = array();
			foreach($students as $k=>$v){
				if(!isset($scoreNew[$v['student_id']])){
					$scoreNew[$v['student_id']] = array(
						'student_id' => $v['student_id'],
						'student_name' => $v['student_name'],
						'score' => $v['score'],
						'sub_times' => 1,
						'comment' => $v['comment'],
						'test_time' => $v['test_time'],
						'vote_num' => $v['vote_num'],
					);
				}else{
					$scoreNew[$v['student_id']]['sub_times'] ++;
					$scoreNew[$v['student_id']]['score'] += $v['score'];
				}

			}
			/*echo '<pre>';
			var_dump($scoreNew);
			die;*/
			//取上一个月的成绩
			$testTime = date('Ymd',$testTime);
			$testTime = strtotime("$testTime -1 month");
			$studentsLast = $scoreModel->field('student_id,score')->join("otk_student s on s.student_id=n.student_id")->where('n.rank_status=1 and s.status=0 and n.test_time='.$testTime)->select();
			$scoreLast = array();
			foreach($studentsLast as $k=>$v){
				//计算分数
				if(!isset($scoreLast[$v['student_id']])){
					$scoreLast[$v['student_id']] = array(
						'student_id' => $v['student_id'],
						'student_name' => $v['student_name'],
						'score' => $v['score'],
						'sub_times' => 1,
						'comment' => $v['comment'],
						'test_time' => $v['test_time'],
					);
				}else{
					$scoreLast[$v['student_id']]['sub_times'] ++;
					$scoreLast[$v['student_id']]['score'] += $v['score'];
				}

			}
			//计算平均分分差
			foreach($scoreNew as $k=>$v){
				if(isset($scoreLast[$v['student_id']])){
					$avg = $v['score']/$v['sub_times'];
					$last = $scoreLast[$v['student_id']]['score']/$scoreLast[$v['student_id']]['sub_times'];
					$scoreNew[$k]['scoreDiff'] = $avg-$last;
				}
			}
			//排序
			$scoreNew = ClassModel::arraySortByKey($scoreNew,'scoreDiff',false);
			$wxStudents = M('student')->where("wx_id='".$_SESSION['wx_id']."' and status=0 ")->getField('student_id',0);	//微信用户下的学生
			$i = 1;
			$shareStu['StuId'] = 0;
			foreach($scoreNew as $k=>$v){
				if($shareStu['StuId']==0 && in_array($k,$wxStudents)){
					$shareStu['StuId'] = $k;
					$shareStu['student_name'] = $v['student_name'];
					$shareStu['order'] = $i;
				}
				$v['order'] = $i;
				$i++;
				$scoreOrder[] = $v;
			}

			//增加微信分享时显示用户下的学生排名
			if($shareStu['StuId'] == 0){
				$shareStu['StuId'] = $scoreOrder[0]['student_id'];
				$shareStu['student_name'] = $scoreOrder[0]['student_name'];
				$shareStu['order'] = 1;
			}
//			echo '<pre>';
//			echo $_SESSION['wx_id'];
//			var_dump($shareStu);
//			die;
			foreach($scoreOrder as $k=>$v){
				$scoreOrder[$k]['rankShare'] = $shareStu;
			}

			if(!empty($scoreOrder)){
				$return = $scoreOrder;
			}
		}

		return $return;
	}
}
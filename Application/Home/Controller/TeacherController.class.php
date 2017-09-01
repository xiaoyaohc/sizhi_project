<?php

namespace Home\Controller;
use Home\Model\TeacherModel;
use Home\Model\SubjectModel;
use Home\Model\AreaModel;
use Home;

/**
 * 
 * 
 */
class TeacherController extends HomeController {
    const APPID      = 'wxa3fec0285ab83c09';
    const APPSECRET  = '7904ce4c83a8d89d0bfbdec1ff9f7ad4';

	/**
	 * 初始化
	 */
	public function _initialize(){
		if(!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])){
			$reUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}else{
			$reUrl = "http://".C('WX_CONFIG.domain')."/Home/Index/home/";
		}
		get_wx_id($reUrl);
		//微信sSDK
		$jssdk = new JSSDK(C('WX_CONFIG.appid'), C('WX_CONFIG.secret'));
		$signPackage = $jssdk->GetSignPackage();
		$this->assign('signPackage',$signPackage);
	}

    /**
     * 教师列表信息
     */
    public function teacherList(){
    	$Sid = I('Sid',0,'int');	//科目id
    	$Aid = I('Aid',0,'int');	//区域id
    	$OrderArr = array('综合排序','价格排序','评分最高','人气最高');
    	$Order = I('Order',0,'int');	//排序,0:未定义,1:价格，2:评分星星，3:上课人数
    	$IsOrder = I('IsOrder',0,'int');
    	$page = I('p',1,'int');	//翻页参数
    	
    	//获取数据getList
    	$Teacher = TeacherModel::getList($Sid,$Aid,$Order,$IsOrder);	//教师列表
    	if ($page>1){	//ajax翻页
    		$this->ajaxReturn($Teacher);
    		exit;
    	}
    	$Subject = SubjectModel::getList();	//科目列表
    	$Sname 	 = SubjectModel::getInfo($Sid);
    	$Area	 = AreaModel::getList();	//区域列表
    	$Aname 	 = AreaModel::getInfo($Aid);
    	
    	$Data = array(
    		'Sid' => $Sid,
    		'SName' => $Sname,
    		'Aid' => $Aid,
    		'AName' => $Aname,
    		'Order' => $Order,
    		'OrderName' => $OrderArr[$Order],
    		'IsOrder' => $IsOrder,
    		'Teacher' => $Teacher,
    		'Subject' => $Subject,
    		'Area' => $Area,
    	);
    	$this->assign('TeacherListData',$Data);
    	$this->display('/teacher/teacher-list');
    }
    
    /**
     * 教师课程信息
     */
    public function teacherClass(){
    	$Tid = I('Tid',0,'int');	//教师id
		$Cid = I('Cid',0,'int');	//课程id
    	$page = I('p',1,'int');	//翻页参数
    	$Info = TeacherModel::getInfo($Tid,$Cid);	//教师列表

    	if ($page>1){	//ajax翻页
    		$this->ajaxReturn($Info);
    		exit;
    	}
    	if(empty($Info)){
    		$this->error('非法操作');
    	}
//    	var_dump($Info);die;
    	$this->assign('Info',$Info);
    	$this->display('/teacher/courses');
    }
    /*
     * 查询教师相册
     */
    public function sel_photo(){
        $teacher_id=I('teacher_id',0,'int');
        $list = M()->table('otk_teacher')->field('album')->where('teacher_id='.$teacher_id)->find();
        $data=explode(',',trim($list['album'],','));
        $this->assign('photoData',$data);
        $this->display('/teacher/sel_photo');
    }
}
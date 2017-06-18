<?php

namespace Home\Controller;
use Home\Model\UserModel;
use Home\Model\ClassModel;
use Home\Model\PayInfoModel;
use Home;

/**
 * 
 * 
 */
class UserController extends HomeController {
    const APPID      = 'wxa3fec0285ab83c09';
    const APPSECRET  = '7904ce4c83a8d89d0bfbdec1ff9f7ad4';

    /**
     * 初始化
     */
    public function _initialize(){
        $_SESSION['wx_id'] = 'oEjhSwiytpQ8_Kmf-EZV-h-veOoM';
//        unset($_SESSION['wx_id']);
        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
    }

    /**
     * 用户中心
     */
    public function userCenter(){
//        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
        $Uid = $_SESSION['wx_id'];	//用户微信id
    	$UserInfo = UserModel::getInfo($Uid);	//用户信息
    	if(empty($UserInfo)){
    		$this->error('非法操作');
    	}
        $page = I('p',1,'int');	//翻页参数
        $OrderInfo = PayInfoModel::getInfo($Uid);	//用户订单信息
        if ($page>1){	//ajax翻页
            $this->ajaxReturn($OrderInfo);
            exit;
        }

    	$this->assign('UserInfo',$UserInfo);
    	$this->assign('OrderInfo',$OrderInfo);
    	$this->display('usercenter');
    }
	
	 /**
     * 用户学生信息
     */
    public function userScore(){
//        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
        $Uid = $_SESSION['wx_id'];	//用户微信id
        $SubUser = UserModel::getStudentInfo($Uid);
        $this->assign('SubUser',$SubUser);
        $this->display('my-records-stu');
    }

    /**
     * 用户学生科目信息
     */
    public function UserSubjectInfo(){
//        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
        $Uid = $_SESSION['wx_id'];	//用户微信id
        $StuId = I('stu_id',0,'int');
        $SubjectInfo = UserModel::getSubjectInfo($Uid,$StuId);	//用户成绩信息

        $this->assign('SubjectInfo',$SubjectInfo);
        $this->display('my-records');
    }

    /**
     * 用户学生成绩信息
     */
    public function UserScoreCharts(){
//        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
        $Uid = $_SESSION['wx_id'];	//用户微信id
        $StuId = I('student_id',0,'int');
        $SubId = I('subject_id',0,'int');
        $ScoreInfo = UserModel::getScoreInfo($Uid,$StuId,$SubId);	//用户成绩信息

        $this->assign('ScoreInfo',$ScoreInfo);
        $this->display('my-records-charts');
    }

    /**
     * 用户基本信息
     */
    public function userInfo(){
//        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
        $Uid = $_SESSION['wx_id'];	//用户微信id
        $UserInfo = UserModel::getInfo($Uid);	//用户信息
//        var_dump($UserInfo);die;
        $this->assign('UserInfo',$UserInfo);
        $this->display('my-info');
    }

    /**
     * 用户基本信息修改
     */
    public function userInfoEdit(){
//        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
        $Uid = $_SESSION['wx_id'];	//用户微信id
        $UserInfo = UserModel::getInfo($Uid);	//用户信息
        $UserInfo['nick_name'] = I('nick_name','','trim');
        $UserInfo['wx_username'] = I('wx_username','','trim');
        $UserInfo['contact'] = I('contact','','trim');
        $UserInfo['school'] = I('school','','trim');
        $UserInfo['class'] = I('class','','trim');
        $res = UserModel::userInfoEdit($UserInfo);
        if($res){
            $this->success('修改成功',U('User/UserInfo','',''));
        }else{
            $this->error('修改失败');
        }
    }
}
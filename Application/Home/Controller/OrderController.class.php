<?php

namespace Home\Controller;
use Home\Model\OrderModel;
use Home\Model\ClassModel;
use Home\Model\UserModel;
use Home;

/**
 * 
 * 
 */
class OrderController extends HomeController {
    /**
     * 初始化
     */
    public function _initialize(){
        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
    }

    /**
     * 教师课程信息
     */
    public function teacherClass(){
    	$Cid = I('Cid',0,'int');	//课程id
    	$Info = ClassModel::getInfo($Cid);	//课程信息
    	if(empty($Info)){
    		$this->error('非法操作');
    	}
		$classType = array('','（大班）','（小班）');
		$Info['class_type'] = $classType[$Info['class_type']];

    	$this->assign('Info',$Info);
    	$this->display('order');
    }
	
	 /**
     * 订单提交
     */
    public function order(){
        
        $Cid = I('Cid',0,'trim');    //课程id
        if(!is_numeric($Cid)){
            $this->error('非法操作');
        }
    	$Info = ClassModel::getInfo($Cid);	//课程信息
        /*if($Info['class_num']<1){
            $this->error('学位已满');
        }*/

        //订单信息
        get_wx_id("http://".C('WX_CONFIG.domain')."/Home/Index/home/");
        $OrderInfo['wx_id'] = $_SESSION['wx_id']; //微信id
        $OrderInfo['order_id'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);   //订单号

        $OrderInfo['teacher_id'] = $Info['teacher_id'];
        $OrderInfo['area_id'] = $Info['area_id'];
        $OrderInfo['class_stime'] = $Info['class_stime'];
//        $OrderInfo['pay_money'] = 0.01;
        $OrderInfo['pay_money']=$Info['class_price'];
        $OrderInfo['add_time'] = time();
        $OrderInfo['pay_time'] = time();
        $OrderInfo['subject_id'] = $Info['subject_id'];
        $OrderInfo['class_id'] = $Cid;
//        $OrderInfo['class_start_time'] = empty($res['class_start_time'])?strtotime(date('Ymd')):$_POST['class_start_time'];

        // 保存信息
        $res = OrderModel::confirm($OrderInfo);
        if($res){
            // 此处支付
            $url = 'http://'.C('WX_CONFIG.domain').'/Home/Wxpay/js_api_call/?order_sn='.$OrderInfo['order_id'];
            redirect($url);
        }else{
            print_r("ss");
        }

    }

    public function saveInfo(){
        $StuInfo = array(
            'stu_id'        => I('stu_id','0','trim'),
            'stu_name'      => I('stu_name','','trim'),
            'stu_contant'   => I('stu_contant','','trim'),
            'stu_school'    => I('stu_school','','trim'),
            'stu_class'     => I('stu_class','','trim'),
            'class_start_time'     => I('class_start_time','','trim'),
            'pay_type'      => I('pay_type',0,'trim')
        );
        //开始上课时间
        $StuInfo['class_start_time'] = strtotime($StuInfo['class_start_time']);
        if(empty($StuInfo['class_start_time'])){
            $StuInfo['class_start_time'] = strtotime(date('Ymd'));
        }

        $order_sn = I('order_sn');
        $OrderInfo = M('pay')->where("order_id='".$order_sn."'")->find();

        //判断是否新增学生
        if($StuInfo['stu_id']==0){
            $NewStu = array();
            $NewStu['pwx_id'] = $OrderInfo['wx_id'];
            $NewStu['wx_id'] = $OrderInfo['wx_id'];
            $NewStu['student_name'] = $StuInfo['stu_name'];
            $NewStu['contact'] = $StuInfo['stu_contant'];
            $NewStu['school'] = $StuInfo['stu_school'];
            $NewStu['class'] = $StuInfo['stu_class'];
            $NewStu['add_time'] = time();
            //保存学生表
            $StuInfo['stu_id'] = M('student')->add($NewStu);
        }
        //学生id
        $OrderInfo['stu_id'] = $StuInfo['stu_id'];
        $OrderInfo['class_start_time'] = $StuInfo['class_start_time'];
        M('pay')->save($OrderInfo);

        /*$StuInfo['order_id'] = $OrderInfo['order_id'];
        $StuInfo['wx_id'] = $OrderInfo['wx_id']; //微信号
        $StuInfo['teacher_id'] = $OrderInfo['teacher_id'];
        $StuInfo['subject_id'] = $OrderInfo['subject_id'];
        $StuInfo['class_id'] = $OrderInfo['class_id'];
        $StuInfo['pay_status'] = 1;
        M('pay_info')->add($StuInfo);*/

        $rs['status'] = 0;
        $rs['message']    = $StuInfo['stu_id'].' '.$OrderInfo['class_start_time'].' '.$NewStu['pwx_id'].' '.$NewStu['student_name'];
        echo json_encode($rs);exit;
        /*

    */
    }

    /**
     *  学生修改
     */
    public function selectInfo(){

        $OrderId  = I('order_sn');
        $WxId  = I('wx_id');
        $stuList = UserModel::getStudentList($WxId);
        $this->assign('openId', $WxId);

        $this->assign('order_sn', $OrderId);
        $this->assign('stuList', $stuList);
        $this->display('Wxpay/select-info');

        // 此处支付
//        $url = 'http://'.C('WX_CONFIG.domain').'/Home/Wxpay/js_api_call/?order_sn='.$OrderId;
//        redirect($url);

    }

    /**
     *  学生设置默认
     */
    public function selectDefault(){

        $OrderId  = I('order_sn');
        $WxId  = I('wx_id');
        $stuId  = I('stu_id');
        $stuList = M('student')->where("wx_id='".$WxId."'")->select();

        foreach($stuList as $k => $v){
            if($v['student_id']==$stuId){
                $v['is_default']=1;
            }else{
                $v['is_default']=0;
            }

            M('student')->save($v);
        }

        // 此处支付
        $url = 'http://'.C('WX_CONFIG.domain').'/Home/Wxpay/js_api_call/?order_sn='.$OrderId;
        redirect($url);

    }

    /**
     *  学生删除
     */
    public function selectDel(){

        $OrderId  = I('order_sn');
        $stuId  = I('stu_id');
        $stu = M('student')->where("student_id=".$stuId)->find();
        $stu['status'] = 1;
        $stu['is_default']=0;
        M('student')->save($stu);

        $WxId  = I('wx_id');
        $stuList = UserModel::getStudentList($WxId);
        $this->assign('order_sn', $OrderId);
        $this->assign('stuList', $stuList);
        $this->display('Wxpay/select-info');

    }

    /**
     *  学生增加
     */
    public function stuAdd(){

        $OrderId  = I('order_sn');
        $this->assign('order_sn', $OrderId);

        $WxId  = I('wx_id');
        $this->assign('openId', $WxId);
        $stuId  = I('stu_id');
        if(!empty($stuId)){
            $stu = M('student')->where("student_id=".$stuId)->find();
            $this->assign('stu', $stu);
        }

        $this->display('Wxpay/new-info');

    }

    /**
     *  学生保存
     */
    public function stuSave(){

        $stuInfo = $_REQUEST;
        $OrderId  = $stuInfo['order_sn'];
        unset($stuInfo['order_sn']);
        $stuInfo['pwx_id'] = $_REQUEST['wx_id'];
        $stuInfo['add_time'] = time();

        if(!empty($stuInfo['stu_id'])){
            $stuInfo['student_id'] = $stuInfo['stu_id'];
            unset($stuInfo['stu_id']);

             M('student')->save($stuInfo);
        }else{
             M('student')->add($stuInfo);
        }

        if($stuInfo['is_default']==1){
            $this->selectDefault();
        }else{
            $url = 'http://'.C('WX_CONFIG.domain').'/Home/Wxpay/js_api_call/?order_sn='.$OrderId;
            redirect($url);
        }

    }
    
}
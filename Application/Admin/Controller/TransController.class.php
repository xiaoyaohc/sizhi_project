<?php
namespace Admin\Controller;
/**
 * 订单号
 */
class TransController extends AdminController {


    
    public function index(){
        $order_id     = I('order_id');
        $subject_id   = I('subject_id');
        $teacher_id   = I('teacher_id');
        $pay_status   = I('pay_status');
        $where = '1';
        if(!empty($order_id)){
           $where  .=  " AND a.order_id = '".$order_id."'";
        }
        if(!empty($subject_id)){
           $where  .=  " AND a.subject_id = '".$subject_id."'";
        }
        if(!empty($teacher_id)){
           $where  .=  " AND a.teacher_id = '".$teacher_id."'";
        }
        if(!empty($pay_status)){
           $where  .=  " AND a.pay_status = '".$pay_status."'";
        }
        //权限限制条件
        $cod['a.uid'] = UID;
        $rs = D('Member')->table('otk_member a')->join('otk_auth_group_access b on a.uid=b.uid')->field('a.area_id,a.teacher_id,b.group_id')->where($cod)->find();
        if($rs['group_id']==5){//校长
            $map['area_id']  = array('in',$rs['area_id']);
            $ret = D('Teacher')->field('teacher_id')->where($map)->select();
            foreach($ret as $k => $v){
                $teacher_id .= '"'.$v['teacher_id'].'",';
            }
            $teacher_id = trim($teacher_id,',');
            $where .= " AND a.teacher_id in ($teacher_id)";
        }
        if($rs['teacher_id']){//教师
           $where .= " AND a.teacher_id = ".$rs['teacher_id']."";
        }


        $list   = D('Pay')->query(" SELECT a.*,b.teacher_name,c.subject_name,d.class_name,e.nick_name,f.student_name FROM otk_pay a 
            LEFT JOIN  otk_teacher b ON a.teacher_id = b.teacher_id
            LEFT JOIN  otk_subject c on a.subject_id = c.subject_id
            LEFT JOIN  otk_class d on a.class_id = d.class_id
            LEFT JOIN  otk_wx_user e on a.wx_id = e.wx_id
            LEFT JOIN  otk_student f on a.stu_id = f.student_id
            WHERE $where ORDER BY  a.pay_time asc;");
        foreach($list as $k=>$v){
            $list[$k]['pay_status']    =$v['pay_status']==1?'已完成':'未支付';
            $list[$k]['class_status']  =$v['class_status']==1?'已完成':'未完成';
        }
        $data=array();
        foreach($list as $key=>$val){
            $data['all_income']  += $list[$key]['pay_money'];
        }
        //科目
        $subject  = M('Subject')->field('subject_id,subject_name')->order("add_time desc")->select();
        //教师
        $teacher  = M('Teacher')->field('teacher_id,teacher_name')->order("add_time desc")->select();
        //支付状态
        $status   = array(0=>'未支付',1=>'已完成');
        $this->assign('subject', $subject);
        $this->assign('teacher', $teacher);
        $this->assign('status', $status);
        $this->assign('subject_id', $subject_id);
        $this->assign('teacher_id', $teacher_id);
        $this->assign('pay_status', $pay_status);
        $this->assign('data', $data);
        $this->meta_title = '订单管理';
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('info', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();       
    }

    public function edit($id){
        $model = D('Pay');
        $where = " a.id = ".$id;
        $list  = D('Pay')->query(" SELECT a.*,b.teacher_name,c.subject_name,d.class_name,e.nick_name FROM otk_pay a 
            LEFT JOIN  otk_teacher b ON a.teacher_id = b.teacher_id
            LEFT JOIN  otk_subject c on a.subject_id = c.subject_id
            LEFT JOIN  otk_class d on a.class_id = d.class_id
            LEFT JOIN  otk_wx_user e on a.wx_id = e.wx_id
            WHERE $where ORDER BY  a.pay_time asc;");
        
        $data=$list[0];
        $current = time();
        if($data['class_stime']){
             $current = $data['class_stime'];
        }
        $this->assign('data',$data);
        $this->assign('current', $current);
        $this->meta_title = '编辑订单';
        $this->display();
    }

    public function update(){
        $db  =D('Pay');
        unset($_POST['parse']);
        $_POST['id'] = intval($_POST['id']);
        //$_POST['class_stime'] = strtotime($_POST['class_stime']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Trans/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function deleteInfos(){
        $id = array_unique((array)I('id',0));       
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$id);
        $model = D('Pay');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Trans/index'));
        }else{
            $this->error('删除失败');
        }
    }
}


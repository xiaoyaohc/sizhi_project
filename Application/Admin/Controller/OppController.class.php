<?php
namespace Admin\Controller;
/**
 * 预约
 */
class OppController extends AdminController {


    
    public function index(){
        $order_id     = I('order_id');
        $subject_id   = I('subject_id');
        $teacher_id   = I('teacher_id');
        $where = '1';
        if(!empty($order_id)){
           $where  .=  " AND a.order_id ='".$order_id."'";
        }
        if(!empty($subject_id)){
           $where  .=  " AND a.subject_id = '".$subject_id."'";
        }
        if(!empty($teacher_id)){
           $where  .=  " AND a.teacher_id = '".$teacher_id."'";
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
            $where .= " AND b.teacher_id in ($teacher_id)";
        }
        if($rs['teacher_id']){//教师
           $where .= " AND b.teacher_id = ".$rs['teacher_id']."";
        }
        $list   = D('Class_reserve')->query(" SELECT a.*,b.teacher_name,d.class_name,f.student_name FROM otk_class_reserve a 
            LEFT JOIN  otk_teacher b ON a.teacher_id = b.teacher_id
            LEFT JOIN  otk_class d on a.class_id = d.class_id
            LEFT JOIN  otk_wx_user e on a.wx_id = e.wx_id
            LEFT JOIN  otk_student f on a.stu_id = f.student_id
            WHERE $where ");
        //教师
        $teacher  = M('Teacher')->field('teacher_id,teacher_name')->order("add_time desc")->select();
        $this->assign('teacher', $teacher);
        $this->assign('teacher_id', $teacher_id);
        $this->meta_title = '上课信息管理';
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
        $model = D('Class_reserve');
        $where = " a.id = ".$id;
        $list  = D('Class_reserve')->query(" SELECT a.*,b.teacher_name,d.class_name,f.student_name FROM otk_class_reserve a 
            LEFT JOIN  otk_teacher b ON a.teacher_id = b.teacher_id
            LEFT JOIN  otk_class d on a.class_id = d.class_id
            LEFT JOIN  otk_wx_user e on a.wx_id = e.wx_id
            LEFT JOIN  otk_student f on a.stu_id = f.student_id
            WHERE $where ;");
        $data=$list[0];
        $this->assign('data',$data);
        $this->meta_title = '编辑上课信息';
        $this->display();
    }

    public function update(){
        $db  =D('Class_reserve');
        unset($_POST['parse']);
        $_POST['id'] = intval($_POST['id']);
        $_POST['class_time'] = strtotime($_POST['class_time']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Opp/index'));
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
        $model = D('Class_reserve');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Opp/index'));
        }else{
            $this->error('删除失败');
        }
    }
}


<?php
namespace Admin\Controller;
/**
 * 课程页面
 */
class ClassController extends AdminController {


    
    public function index(){
        $class_name   = I('class_name');
        $where = '1';
        if(!empty($class_name)){
           $where  .=  " AND a.class_name = '".$class_name."'";
        }

        //权限限制条件
        $cod['a.uid'] = UID;
        $cod['a.teacher_id']  = array('GT','0');
        /*$rs = D('Member')->table('otk_member a')->join('otk_auth_group_access b on a.uid=b.uid')->field('a.area_id,a.teacher_id,b.group_id')->where($cod)->find();
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
        }*/
        if(UID!=1){
            $temp=D()->table('otk_member a')->join('right join otk_area b on b.teacher_id=a.teacher_id')->field('b.id,a.teacher_id')->where($cod)->find();
            if($temp){
                $teacher=D()->table('otk_teacher')->field('teacher_id')->where('area_id='.$temp['id'])->select();
                $teacher_array=array();
                //$teacher_array[]=$temp['teacher_id'];
                foreach($teacher as $k => $v){
                    $teacher_array[]=$v['teacher_id'];
                }
                $teacher_string=implode(',',$teacher_array);
                $where .=" AND b.teacher_id in ($teacher_string)";

            }else{
                $teacher_1=D()->table('otk_member')->field('teacher_id')->where('uid='.UID)->find();
                if($teacher_1){
                    $where .= " AND b.teacher_id =".$teacher_1['teacher_id'];
                }else{
                    $where .= " AND b.teacher_id = 0";
                }
            }
        }

        $period = $this->period();
        $sale_status = $this->sale_status();
        $list   = D('Class')->query(" SELECT a.*,b.teacher_name,c.subject_name FROM otk_class a
            LEFT JOIN  otk_teacher b ON a.teacher_id = b.teacher_id
            LEFT JOIN  otk_subject c on a.subject_id = c.subject_id
            WHERE $where ORDER BY  a.add_time asc;");
        foreach($list as $k=>$v){
//            echo '<pre>';
//            var_dump($v);
            if($list[$k]['class_type']==1){
                $list[$k]['period_name']   =$period[$v['period']]; 
            }
            $list[$k]['class_type']    =$v['class_type']==1?'大':'小';
            
            
            $list[$k]['sale_status']   =$sale_status[$v['sale_status']];
        }
        $this->meta_title = '课程管理';
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

    public function add(){
            //课程
            $subject  = M('Subject')->field('subject_id,subject_name')->order("add_time desc")->select();
            //教师
            $teacher  = M('Teacher')->field('teacher_id,teacher_name')->order('convert(teacher_name using gbk) asc')->select();
            //周
            $week     = M('Week')->field('week_id,week_name')->order("week_sort asc")->select();
        
            $current  = time();
            $this->assign('subject', $subject);
            $this->assign('teacher', $teacher);
            $this->assign('current', $current);
            $this->assign('week', $week);
            $this->assign('period', $this->period());
            $this->assign('sale_status', $this->sale_status());
            $this->assign('week', $week);
            $this->meta_title = '新增课程';
            $this->display('add');
    }

    public function edit($class_id){
        $model = D('Class');
//        $db = D('Class_times');
        $where['class_id'] = $class_id;
        $data=$model->find($class_id);
//        $rs  =$db->where($where)->find();
        //课程
        $subject  = M('Subject')->field('subject_id,subject_name')->order("add_time desc")->select();
        //教师
        $teacher  = M('Teacher')->field('teacher_id,teacher_name')->select();
        //周
        $week     = M('Week')->field('week_id,week_name')->order("week_sort asc")->select();
        $current  = time();
        $this->assign('subject', $subject);
        $this->assign('teacher', $teacher);
        $this->assign('current', $current);
        $this->assign('period', $this->period());
        $this->assign('sale_status', $this->sale_status());
        $this->assign('week', $week);
        $this->assign('data',$data);
//        $this->assign('rs',$rs);
        $this->meta_title = '编辑课程';
        $this->display();
    }

    public function update(){
        $db  =D('Class');
        unset($_POST['parse']);
        $_POST['class_id'] = intval($_POST['class_id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            if($_POST['class_type']==2){
                $con['class_id']      = $_POST['class_id'];
                $add['week_id']       = $_POST['week_id'];
                $add['time_interval'] = $_POST['time_interval'];
//                D('Class_times')->where($con)->save($add);
            }
            $this->success('更新成功',U('Class/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Class');
            $_POST['add_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Class/index',array('info_type'=>$_POST['info_type'])));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfos(){
        $class_id = array_unique((array)I('class_id',0));       
        $class_id = is_array($class_id) ? implode(',',$class_id) : $class_id;
        if ( empty($class_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['class_id'] = array('in',$class_id);
        $model = D('Class');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Class/index'));
        }else{
            $this->error('删除失败');
        }
    }

    public function period(){
        return array(0=>'星期',1=>'周',2=>'月',3=>'年',4=>'学期');

    }


    public function sale_status(){
        return array(0=>'未审核',1=>'上架',2=>'下架');

    }
}


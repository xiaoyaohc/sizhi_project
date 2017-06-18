<?php
namespace Admin\Controller;
/**
 * 纪律之星
 */
class ScorenewController extends AdminController {


    
    public function index(){
        $cod['a.uid'] = UID;
        $rs = D('Member')->table('otk_member a')->join('otk_auth_group_access b on a.uid=b.uid')->field('a.area_id,a.teacher_id,b.group_id')->where($cod)->find();
        if($rs['group_id']==5){//校长
            $map['area_id']  = array('in',$rs['area_id']);
            $ret = D('Teacher')->field('teacher_id')->where($map)->select();
            foreach($ret as $k => $v){
                $teacher_id .= '"'.$v['teacher_id'].'",';
            }
            $teacher_id = trim($teacher_id,',');
        }
        if($rs['teacher_id']){//教师
           $teacher_id = $rs['teacher_id']; 
        }
        if($teacher_id){
            $student = D('Pay')->field("student_id")->where(" teacher_id in ($teacher_id) ")->group('student_id')->select();
            if($student){
                foreach($student as $kal => $val){
                    $student_id .= '"'.$val['student_id'].'",';
                }
                $student_id = trim($student_id,',');

            }
        }
        if($student_id){
            $where .=" AND stundent_id IN ($student_id) ";
        }
        $list   = D('Student')->field('student_id,student_name')->where("1 AND status=0 $where ")->select();
        /*
        $list   = D('Score')->query(" SELECT a.*,b.teacher_name,c.subject_name,e.student_name FROM otk_score a 
            LEFT JOIN  otk_teacher b ON a.teacher_id = b.teacher_id
            LEFT JOIN  otk_subject c on a.subject_id = c.subject_id
            LEFT JOIN  otk_student e on a.student_id = e.student_id
            WHERE 1 $where ORDER BY  a.add_time desc;");
            */
        $this->meta_title = '成绩管理';
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('info', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();       
    }

    

    public function edit($student_id){
        $model = D('Score_new');
        $where['student_id'] = $student_id;
        $sql = "select student_id,week,score from otk_score_new where 1 and student_id = ".$student_id." group by student_id,week;";
        $data = $model->query($sql);
        $this->assign('data',$data);
        $this->assign('student_id',$student_id);
        $this->meta_title = '录入成绩';
        $this->display();
    }

    
    public function rank(){
        $db  =D('Score_new');
        unset($_POST['parse']);
        $data['rank_status'] = 1;
        $where['rank_status']= 0;
        $list  = $db->where($where)->save($data);
        if ($list !== false) {
            $rs['code'] = 1;
            echo json_encode($rs);exit;
        } else {
            $rs['code'] = 0;
            echo json_encode($rs);exit;
        }
    }

    public function score(){
        $db  =D('Score_new');
        unset($_POST['parse']);
        $where['week']      = I('week');
        $where['student_id']= I('student_id');
        $score= I('score');
        $list  = $db->where($where)->find();
        if ($list) {
            $where['score'] = $score;
            $con['id']      = $list['id'];
            $db->where($con)->save($where);
            $rs['code'] = 1;
            echo json_encode($rs);exit;
        } else {
            $where['score']    =$score;
            $where['add_time'] =time();
            $db->add($where);
            $rs['code'] = 1;
            echo json_encode($rs);exit;
        }
    }  
}


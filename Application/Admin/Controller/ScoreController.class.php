<?php
namespace Admin\Controller;
/**
 * 学生成绩
 */
class ScoreController extends AdminController {


    
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
        $list   = D('Student')->field('student_id,student_name,contact')->where("1 AND status=0 $where ")->select();
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

    public function add(){
            //科目
            $subject  = M('Subject')->field('subject_id,subject_name')->order("add_time desc")->select();
            //教师
            $teacher  = M('Teacher')->field('teacher_id,teacher_name')->select();
            
            //学生
            $student  = M('Student')->field('student_id,student_name')->select();
            $this->assign('subject', $subject);
            $this->assign('teacher', $teacher);
            $this->assign('class', $class);
            $this->assign('student', $student);
            $this->meta_title = '新增成绩';
            $this->display('add');
    }

    public function edit($student_id){
        $model = D('Score');
        $where['student_id'] = $student_id;
        $sql = "select student_id,sum(case when subject_id=1 then score else 0 end) chinese,sum(case when subject_id=2 then score else 0 end) math,sum(case when subject_id=3 then score else 0 end) english,comment,YEAR(FROM_UNIXTIME(test_time))y,MONTH (FROM_UNIXTIME(test_time)) m from otk_score where 1 and student_id = ".$student_id." group by student_id,y,m;";
        $data = $model->query($sql);
        $this->assign('data',$data);
        $this->assign('student_id',$student_id);
        $this->meta_title = '录入成绩';
        $this->display();
    }

    public function update(){
        $db  =D('Score');
        unset($_POST['parse']);
        $_POST['id']       = intval($_POST['id']);
        $_POST['test_time']=strtotime(I('test_time'));
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Score/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Score');
            $_POST['add_time'] = time();
            $_POST['test_time'] =strtotime(I('test_time'));
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Score/index',array('info_type'=>$_POST['info_type'])));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfos(){
        $id = array_unique((array)I('id',0));       
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$id);
        $model = D('Score');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Score/index'));
        }else{
            $this->error('删除失败');
        }
    }



    public function rank(){
        $db  =D('Score');
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
        $db  =D('Score');
        unset($_POST['parse']);
        $where['test_time'] = strtotime(I('test_time'));
        $where['student_id']= I('student_id');
        $where['subject_id']= I('subject_id');
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



    public function comment(){
        $db  =D('Score');
        unset($_POST['parse']);
        $where['test_time'] = strtotime(I('test_time'));
        $where['student_id']= I('student_id');
        $list  = $db->where($where)->find();
        if ($list) {
            $con['comment'] = I('comment');
            $db->where($where)->save($con);
            $rs['code'] = 1;
            echo json_encode($rs);exit;
        } else {
            $rs['code'] = 0;
            echo json_encode($rs);exit;
        }
    }

    public function deleteSco($Uid=0){
        $return['status'] = 0;
        if(is_numeric($Uid) && $Uid>0){
            $db  =D('Student');
            $user = $db->where('student_id='.$Uid)->find();
            if(!empty($user)){
                $user['status'] = 1;
                $res = $db->save($user);
                if($res){
                    $return['status'] = 1;
                }
            }
        }
        $this->ajaxReturn($return);
    }

}


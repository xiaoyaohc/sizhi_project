<?php
namespace Admin\Controller;
/**
 * 教师信息
 */
class TeacherController extends AdminController {


    
    public function index(){
        $teacher_name = I('teacher_name');
        $where  = '1';
        if($teacher_name){
            $where .=" AND teacher_name  ='".$teacher_name."'";
        }

        $cod['a.uid'] = UID;
        $rs = D('Member')->table('otk_member a')->join('otk_auth_group_access b on a.uid=b.uid')->field('a.area_id,a.teacher_id,b.group_id')->where($cod)->find();
        if($rs['group_id']==5){//校长
           $where .= " AND a.area_id = ".$rs['area_id']."";
        }
        if($rs['teacher_id']){
           $where .= " AND a.teacher_id = ".$rs['teacher_id']."";
        }
        $list   = D('Teacher')->query(" SELECT a.*,c.area_name FROM otk_teacher a 
            LEFT JOIN  otk_area c on a.area_id = c.id
            WHERE $where ORDER BY  a.add_time asc;");
        $this->meta_title = '教师信息';
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
        
        //区域
        $area   = M('Area')->field('id,area_name')->order("add_time desc")->select();
        $this->assign('area', $area);
        $this->meta_title = '新增教师';
        $this->display();
    }

    public function edit($teacher_id){
        $model = D('Teacher');
        $where['teacher_id'] = $teacher_id;
        $data=$model->find($teacher_id);
        //区域
        $area   = M('Area')->field('id,area_name')->order("add_time desc")->select();
        $this->assign('area', $area);
        $this->assign('data',$data);
        $this->meta_title = '编辑教师';
        $this->display();
    }

    public function update(){
        $db  =D('Teacher');
        unset($_POST['parse']);
        $_POST['teacher_id'] = intval($_POST['teacher_id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Teacher/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Teacher');
            $_POST['add_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Teacher/index',array('info_type'=>$_POST['info_type'])));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfos(){
        $teacher_id = array_unique((array)I('id',0));       
        $teacher_id = is_array($teacher_id) ? implode(',',$teacher_id) : $teacher_id;
        if ( empty($teacher_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$teacher_id);
        $model = D('Teacher');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Teacher/index'));
        }else{
            $this->error('删除失败');
        }
    }
}


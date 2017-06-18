<?php
namespace Admin\Controller;
/**
 * 科目信息
 */
class SubjectController extends AdminController {   
    public function index(){
        $subject_name  = I('subject_name');
        $where = '';
        if(!empty($subject_name)){
           $where['subject_name']  = array('like', '%'.$subject_name.'%'); 
        }
        $rs   = get_site_cate();
        $list = D('Subject')->where($where)->order('add_time asc')->select();
        $this->meta_title = '科目信息管理';
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('rs', $rs);
        $this->assign('info', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();       
    }

    public function add(){
            $db  = D('Subject');
            $this->meta_title = '新增科目';
            $rs  = get_site_cate();
            $this->assign('rs', $rs);
            $this->display('add');
    }

    public function edit($subject_id){
        $model = D('Subject');
        $rs    = get_site_cate();
        $where['subject_id'] = $subject_id;
        $data  = $model->find($subject_id);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑科目';
        $this->display();
    }

    public function update(){
        $db  =D('Subject');
        unset($_POST['parse']);
        $_POST['subject_id'] = intval($_POST['subject_id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Subject/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Subject');
            $_POST['add_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Subject/index',array('Subject_type'=>$_POST['Subject_type'])));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfo(){
        $subject_id = array_unique((array)I('subject_id',0));       
        $subject_id = is_array($subject_id) ? implode(',',$subject_id) : $subject_id;
        if ( empty($subject_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['subject_id'] = array('in',$subject_id);
        $model = D('Subject');
        if($model->where($map)->delete()){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}


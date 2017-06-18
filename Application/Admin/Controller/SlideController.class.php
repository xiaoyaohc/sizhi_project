<?php
namespace Admin\Controller;
/**
 * 幻灯片
 */
class SlideController extends AdminController {


    
    public function index(){
        $slide_name   = I('slide_name');
        $where = '1';
        if(!empty($slide_name)){
           $where['slide_name']  =  $slide_name;
        }
        $list   = D('Slide')->where($where)->select();
        $this->meta_title = '幻灯片管理';
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
            $this->meta_title = '新增幻灯片';
            $this->display('add');
    }

    public function edit($slide_id){
        $model = D('Slide');
        $where['slide_id'] = $slide_id;
        $data=$model->find($slide_id);
        $this->assign('data', $data);
        $this->meta_title = '编辑幻灯片';
        $this->display();
    }

    public function update(){
        $db  =D('Slide');
        unset($_POST['parse']);
        $_POST['slide_id'] = intval($_POST['slide_id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Slide/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Slide');
            $_POST['add_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Slide/index',array('info_type'=>$_POST['info_type'])));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfos(){
        $slide_id = array_unique((array)I('slide_id',0));       
        $slide_id = is_array($slide_id) ? implode(',',$slide_id) : $slide_id;
        if ( empty($slide_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['slide_id'] = array('in',$slide_id);
        $model = D('Slide');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Slide/index'));
        }else{
            $this->error('删除失败');
        }
    }
}


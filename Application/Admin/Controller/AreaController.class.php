<?php
namespace Admin\Controller;
/**
 * 区域信息
 */
class AreaController extends AdminController {   
    public function index(){
        $area_name  = I('area_name');
        $where = '';
        if(!empty($area_name)){
           $where['area_name']  = array('like', '%'.$area_name.'%'); 
        }
        $rs   = get_site_cate();
        $list = D('Area')->where($where)->order('add_time asc')->select();
        $this->meta_title = '区域信息管理';
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
            $db  = D('Area');
            $this->meta_title = '新增区域';
            $rs  = get_site_cate();
            $this->assign('rs', $rs);
            $this->display('add');
    }

    public function edit($id){
        $model = D('Area');
        $rs    = get_site_cate();
        $this->assign('rs', $rs);
        $where['id'] = $id;
        $data  = $model->find($id);
        $this->type = I('type',1);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑区域';
        $this->display();
    }

    public function update(){
        $db  =D('Area');
        unset($_POST['parse']);
        $_POST['id'] = intval($_POST['id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Area/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Area');
            $_POST['add_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Area/index',array('Area_type'=>$_POST['Area_type'])));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfo(){
        $id = array_unique((array)I('id',0));       
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$id);
        $model = D('Area');
        if($model->where($map)->delete()){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}


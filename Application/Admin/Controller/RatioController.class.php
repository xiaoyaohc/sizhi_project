<?php
namespace Admin\Controller;
/**
 * 代理商信息
 */
class RatioController extends AdminController {   
    public function index(){
        $rs    = get_site_cate();
        $list = D('Ratio')->order('add_time asc')->select();
        $this->meta_title = '课程-代理商通用提成比例';
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
            $db  = D('Ratio');
            $this->meta_title = '新增';
            $rs  = get_site_cate();
            $this->assign('rs', $rs);
            $this->display('add');
    }

    public function edit($id){
        $model = D('Ratio');
        $rs    = get_site_cate();
        $this->assign('rs', $rs);
        $where['id'] = $id;
        $data  = $model->find($id);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑';
        $this->display();
    }

    public function update(){
        $db  =D('Ratio');
        unset($_POST['parse']);
        $_POST['id'] = intval($_POST['id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Ratio/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Ratio');
            $_POST['add_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Ratio/index',array('info_type'=>$_POST['info_type'])));
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
        $model = D('Ratio');
        if($model->where($map)->delete()){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}


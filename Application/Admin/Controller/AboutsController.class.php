<?php
namespace Admin\Controller;
/**
 *关于我们
 */
class AboutsController extends AdminController {   
    public function index(){
        $about_name  =  I('about_name');
        $where = '';
        if(!empty($about_name)){
           $where['about_name'] = array('like', '%'.$about_name.'%'); 
        }
        
        $rs  = get_site_cate();
        $list= D('About')->where($where)->order('about_time asc')->select();
        $this->meta_title = '关于我们';
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
            $db  = D('About');
            $this->meta_title = '增加';
            $rs  = get_site_cate();
            $this->assign('rs', $rs);
            $this->display('add');
    }

    public function edit($about_id){
        $model = D('About');
        $rs    = get_site_cate();
        $this->assign('rs', $rs);
        $where['about_id'] = $about_id;
        $data=$model->find($about_id);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑关于我们';
        $this->display();
    }

    public function update(){
        $db  =D('About');
        unset($_POST['parse']);
        $_POST['about_id'] = intval($_POST['about_id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Abouts/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('About');
            $_POST['about_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Abouts/index'));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfos(){
        $about_id = array_unique((array)I('about_id',0));       
        $about_id = is_array($about_id) ? implode(',',$about_id) : $about_id;
        if (empty($about_id)){
            $this->error('请选择要操作的数据!');
        }
        $map['about_id'] = array('in',$about_id);
        $model = D('About');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Abouts/index'));
        }else{
            $this->error('删除失败');
        }
    }
}


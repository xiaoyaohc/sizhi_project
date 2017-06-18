<?php
namespace Admin\Controller;
/**
 * 联系我们
 */
class ContactController extends AdminController {

    
    public function index(){
        $contact_name =  I('contact_name');
        $where = '';
        if(!empty($contact_name)){
           $where['contact_name']    =  array('like', '%'.$contact_name.'%'); 
        }
        $rs   = get_site_cate();
        $list = D('Contact')->where($where)->order('contact_time asc')->select();
        $this->meta_title = '联系我们';
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
            $db  = D('Contact');
            $this->meta_title = '新增联系我们';
            $rs  = get_site_cate();
            $this->assign('rs', $rs);
            $this->display('edit');
    }

    public function edit($contact_id){
        $model = D('Contact');
        $rs  = get_site_cate();
        $this->assign('rs', $rs);
        $where['contact_id'] = $contact_id;
        $data=$model->find($contact_id);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑联系我们';
        $this->display();
    }

    public function update(){
        $db  =D('Contact');
        unset($_POST['parse']);
        $_POST['contact_id'] = intval($_POST['contact_id']);
        if($_POST['contact_id']){
            $list  = $db->save($_POST);
            if ($list !== false) {
                $this->success('更新成功',U('Contact/index'));
            } else {
                $this->error('更新失败!');
            }
        }else{
            $this->insert();
        }

        
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Contact');
            $_POST['contact_time'] = time();
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Contact/index',array('contact_type'=>$_POST['contact_type'])));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }


    public function deleteInfos(){
        $contact_id = array_unique((array)I('contact_id',0));       
        $contact_id = is_array($contact_id) ? implode(',',$contact_id) : $contact_id;
        if ( empty($contact_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['contact_id'] = array('in',$contact_id);
        $model = D('Contact');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Contact/index',array('contact_type'=>$_POST['contact_type'])));
        }else{
            $this->error('删除失败');
        }
    }
}


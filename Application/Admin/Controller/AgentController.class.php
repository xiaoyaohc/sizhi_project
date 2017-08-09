<?php
namespace Admin\Controller;
/**
 * 代理商信息
 */
class AgentController extends AdminController {   
    public function index(){
        $name  = I('name');
        $where = '';
        if(!empty($name)){
           $where['name']  = array('like', '%'.$name.'%'); 
        }
        $rs   = get_site_cate();
        //权限限制条件
        if(UID!=1){
            $teacher_1=D()->table('otk_member a')->join('left join otk_teacher b on b.teacher_id=a.teacher_id')->field('b.teacher_name')->where('a.uid='.UID)->find();
            if($teacher_1){
                $where['name']= array('eq',"{$teacher_1['teacher_name']}");
            }else{
                $where['name']= array('eq',"0");
            }
        }
        $list = D('Agent')->where($where)->order('add_time asc')->select();
        $this->meta_title = '代理商信息管理';
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
            $db  = D('Agent');
            $this->meta_title = '新增代理商';
            $rs  = get_site_cate();
            $this->assign('rs', $rs);
            $this->display('add');
    }

    public function edit($id){
        $model = D('Agent');
        $rs    = get_site_cate();
        $this->assign('rs', $rs);
        $where['id'] = $id;
        $data  = $model->find($id);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑代理商信息';
        $this->display();
    }

    public function update(){
        $db  =D('Agent');
        unset($_POST['parse']);
        $_POST['id'] = intval($_POST['id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Agent/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Agent');
            $_POST['add_time'] = time();
            $_POST['code'] = $this->randStr(6,NUMBER);
            unset($_POST['parse']);
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Agent/index',array('info_type'=>$_POST['info_type'])));
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
        $model = D('Agent');
        if($model->where($map)->delete()){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
   Public function randStr($len=6,$format='NUMBER') {
        switch($format) {
            case 'ALL':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~'; break;
            case 'CHAR':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~'; break;
            case 'NUMBER':
                $chars='0123456789'; break;
            default :
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
        } 
        mt_srand((double)microtime()*1000000*getmypid());
        $password="";
        $codes = D('Agent')->table('otk_agent')->field('code')->select();
        $codelist=array();
        foreach ($codes as $k=>$v){
            $codelist[$k]=$v['code'];
        }
        while(1){
            while (strlen($password)<$len){
            $password.=substr($chars,(mt_rand()%strlen($chars)),1);
            }
            if(!in_array($password,$codes)){
              return  $password;
            }else{
              continue;
            }
        }
   }
}


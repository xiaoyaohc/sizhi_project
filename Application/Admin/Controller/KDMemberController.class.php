<?php
namespace Admin\Controller;
/**
 * 用户信息
 */
class KDMemberController extends AdminController {

    
    public function index(){
        $member_tel  =   I('member_tel');
        if($member_tel){
            $where['member_tel']  = array('like', '%'.(string)$member_tel.'%');
        }
        $list       =   D('Wgh_member_second')->where($where)->order('member_addtime asc')->select();
        $this->meta_title = '宽带用户信息';
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


    public function deleteInfos(){
        $member_id = array_unique((array)I('member_id',0));       
        $member_id = is_array($member_id) ? implode(',',$member_id) : $member_id;
        if ( empty($member_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['member_id'] = array('in',$member_id);
        $model = D('Wgh_member_second');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Member/index'));
        }else{
            $this->error('删除失败');
        }
    }
}

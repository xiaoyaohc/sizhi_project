<?php
namespace Admin\Controller;
/**
 * 信息控制器
 */
class QuestionController extends AdminController {

    /**
     * 会员卡用户信息
     * @return [type] [description]
     */
    public function index(){
        
        $this->meta_title = '会员卡用户信息';
        $list       =   D('Wgh_membercard_info')->select();
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
        $membercard_id = array_unique((array)I('membercard_id',0));       
        $membercard_id = is_array($membercard_id) ? implode(',',$membercard_id) : $membercard_id;
        if ( empty($membercard_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['membercard_id'] = array('in',$membercard_id);
        $model = D('Wgh_membercard_info');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Question/index'));
        }else{
            $this->error('删除失败');
        }
    }
}

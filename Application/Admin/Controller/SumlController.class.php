<?php
namespace Admin\Controller;
/**
 * 信息控制器
 */
class SumlController extends AdminController {
    /**
     * 奖品信息
     * @return [type] [description]
     */
    public function index(){
        $db=D('Jf_sumd');
        $where = '';
        $list=$db->where($where)->order('sumd_score desc')->select();
         
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->assign('info',$voList);
        $this->assign('_page', $p? $p: '');
        $this->meta_title = '用户集福统计信息';
        $this->display();       
    }
}

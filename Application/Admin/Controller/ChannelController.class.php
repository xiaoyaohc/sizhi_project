<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台频道控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */

class ChannelController extends AdminController {

    /**
     * 频道列表
     * @author Ben 修改
     */
    public function index(){
//        $pid = I('get.pid', 0);
//        /* 获取频道列表 */
//        $map  = array('status' => array('gt', -1), 'pid'=>$pid);
//        $list = M('Channel')->where($map)->order('sort asc,id asc')->select();
//
//        $this->assign('list', $list);
//        $this->assign('pid', $pid);
//        $this->meta_title = '导航管理';
//        $this->display();   //

        //修改导航列表为树列表
        $tree = D('Channel')->getTree(0);
        $this->assign('tree', $tree);
        C('_SYS_GET_CHANNEL_TREE_', true); //标记系统获取分类树模板
        $this->meta_title = '导航管理';
        $this->display('index_tree');
    }

    /**
     * 显示导航树，仅支持内部调
     * @param  array $tree 分类树
     * @author Ben 修改
     */
    public function tree($tree = null){
        C('_SYS_GET_CHANNEL_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }

    /**
     * 添加频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function add(){
        if(IS_POST){
            $Channel = D('Channel');
            $data = $Channel->create();
            if($data){
                $id = $Channel->add();
                if($id){
                    $this->success('新增成功', U('index'));
                    //记录行为
                    action_log('update_channel', 'channel', $id, UID);
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Channel->getError());
            }
        } else {
            $pid = I('get.pid', 0);
            //获取父导航
            if(!empty($pid)){
                $parent = M('Channel')->where(array('id'=>$pid))->field('title')->find();
                $this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('info',null);
            $this->meta_title = '新增导航';
            $this->display('edit');
        }
    }

    /**
     * 删除一个导航
     * @author Ben
     */
    public function remove(){
        $channel_id = I('id');
        if(empty($channel_id)){
            $this->error('参数错误!');
        }

        $Channel = M('Channel');
        //判断该分类下有没有子分类，有则不允许删除
        $child = $Channel->where(array('pid'=>$channel_id))->field('id')->select();
        if(!empty($child)){
            $this->error('请先删除该导航下的子导航');
        }

        //删除该分类信息
        $res = $Channel->delete($channel_id);
        if($res !== false){
            //记录行为
            action_log('update_channel', 'channel', $channel_id, UID);
            $this->success('删除导航成功！');
        }else{
            $this->error('删除导航失败！');
        }
    }

    /**
     * 操作导航初始化
     * @param string $type
     * @author Ben
     */
    public function operate($type = 'move'){
        //检查操作参数
        if(strcmp($type, 'move') == 0){
            $operate = '移动';
        }elseif(strcmp($type, 'merge') == 0){
            $operate = '合并';
        }else{
            $this->error('参数错误！');
        }
        $from = intval(I('get.from'));
        empty($from) && $this->error('参数错误！');

        $Channel = M('Channel');
        //获取分类
        $map = array('status'=>1, 'id'=>array('neq', $from));
        $list = $Channel->where($map)->field('id,pid,title')->select();


        //移动分类时增加移至根分类
        if(strcmp($type, 'move') == 0){
            //不允许移动至其子孙分类
            $list = tree_to_list(list_to_tree($list));

            $pid = $Channel->getFieldById($from, 'pid');
            $pid && array_unshift($list, array('id'=>0,'title'=>'根导航'));
        }

        $this->assign('type', $type);
        $this->assign('operate', $operate);
        $this->assign('from', $from);
        $this->assign('list', $list);
        $this->meta_title = $operate.'分类';
        $this->display();
    }

    /**
     * 移动导航
     * @author huajie <banhuajie@163.com>
     */
    public function move(){
        $to = I('post.to');
        $from = I('post.from');
        $res = M('Channel')->where(array('id'=>$from))->setField('pid', $to);
        if($res !== false){
            $this->success('分类移动成功！', U('index'));
        }else{
            $this->error('分类移动失败！');
        }
    }

    /**
     * 编辑频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $Channel = D('Channel');
            $data = $Channel->create();
            if($data){
                if($Channel->save()){
                    //记录行为
                    action_log('update_channel', 'channel', $data['id'], UID);
                    $this->success('编辑成功', U('index'));
                } else {
                    $this->error('编辑失败');
                }

            } else {
                $this->error($Channel->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Channel')->find($id);

            if(false === $info){
                $this->error('获取配置信息错误');
            }

            $pid = I('get.pid', 0);
            //获取父导航
            if(!empty($pid)){
            	$parent = M('Channel')->where(array('id'=>$pid))->field('title')->find();
            	$this->assign('parent', $parent);
            }

            $this->assign('pid', $pid);
            $this->assign('info', $info);
            $this->meta_title = '编辑导航';
            $this->display();
        }
    }

    /**
     * 删除频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('Channel')->where($map)->delete()){
            //记录行为
            action_log('update_channel', 'channel', $id, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 导航排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort(){
        if(IS_GET){
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status'=>array('gt',-1));
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = M('Channel')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = '导航排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = M('Channel')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if($res !== false){
                $this->success('排序成功！');
            }else{
                $this->error('排序失败！');
            }
        }else{
            $this->error('非法请求！');
        }
    }
}
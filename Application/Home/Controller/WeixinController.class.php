<?php

namespace Home\Controller;
/**
 * 前台控制器
 *
 */
class WeixinController extends HomeController {
    //Token值
    const TOKEN      = 'test20160202';
    const APPID      = 'wxa3fec0285ab83c09';
    const APPSECRET  = '7904ce4c83a8d89d0bfbdec1ff9f7ad4';
    /**
     * 初始化
     */
    protected function _initialize(){
    }

	/**
     * 连接微信
     */
    public function index(){
        $token  = self::TOKEN;
        $Wechat = new \Com\Wechat($token);
        $data   = $Wechat->request();
        $this->data = $Wechat->request();
        list($content, $type) = $this->reply($data);
        $Wechat->response($content, $type);
        
    }
    
    /**
    **desc:菜单设置
    */
    public function menuManager(){
        $WechatAuth = new \Com\WechatAuth(self::APPID,self::APPSECRET);
        $menuArr = array(
                array('type' =>"click",'name'=>"xxx",'key'=>"xxx"),
                array('type' =>"click",'name'=>"xxx2",'key'=>"xxx2"),
                array('type' =>"click",'name'=>"xxx3",'key'=>"xxx3"),
            );
        $WechatAuth->getAccessToken();
        $res = $WechatAuth->menuCreate($menuArr);
   }
    
    /**
    **desc:回复
    */
    private function reply($data)
    {
        if ($data['Event']=='CLICK'  ) {
             $explode = explode('|',$data['EventKey'] );
             $wx_id  = $this->data['FromUserName'];
             $data['Content'] = $data['EventKey'];
        }
        if ($data['MsgType']=='voice' ) {
            $data['Content'] = $data['Recognition'];
            $access_token = get_member();
            return array($data['Content'],'text');
        }
        if ($data['Event'] =='subscribe' && $data['EventKey']) {  //未关注直接扫描的 
            $this->concern();
        }
        if ($data['Event']== 'subscribe' ) {
            //收集用户信息
            $this->concern();
            $this->requestdata('follownum');
            $data = M('Areply')->field('home,keyword,content')->where(array('token' => $this->token))->find();
            if ($data['keyword'] == '首页' || $data['keyword'] == 'home') {
                return $this->shouye();
            }
            if ($data['home'] == 1) {
                $like['keyword'] = array('like', ('%' . $data['keyword']) . '%');
                $like['token'] = $this->token;
                $back = M('Img')->field('id,text,pic,url,title')->limit(9)->order('id desc')->where($like)->select();
                foreach ($back as $keya => $infot) {
                    if ($infot['url'] != false) {
                        $url = $this->getFuncLink($infot['url']);
                    } else {
                        $url = rtrim(C('site_url'), '/') . U('Wap/Index/content', array('token' => $this->token, 'id' => $infot['id'], 'wecha_id' => $this->data['FromUserName']));
                    }
                    $imgurl = (rtrim(C('site_url'), '/') . '/Public/Upload/').$infot['pic'];
                    $return[] = array($infot['title'], $infot['text'], $imgurl, $url);
                }
                return array($return, 'news');
            } else {
                return array($data['content'], 'text');
            }
        } elseif ('unsubscribe' == $data['Event']) {
            $this->requestdata('unfollownum');
        }
        $key = $data['Content']; //关键字开始地方
        if (!empty($return)) {
            if (is_array($return)) {
                return $return;
            } else {
                return array($return, 'text');
            }
        } else {
            switch ($key) {
            case '首页':
                return $this->home();
                break;                
            default:
                $res = $this->get_user_data();
                return array($res['nickname'],'text');
            }
        }
    }


    /*
    *desc:收集用户信息
    */
    function concern(){
        $data   = $this->get_user_data();
        $res    = M('Wx_user')->where("wx_id='{$data['openid']}'")->find();
        $nick_new=$this->sub_str($data['nickname'], 0,6);
        $_POST['wx_username']     = urlencode($nick_new);
        $_POST['token']           = self::TOKEN;
        $_POST['sex']             = $data['sex'];
        $_POST['city']            = $data['city'];
        $_POST['province']        = $data['province'];
        $_POST['country']         = $data['country'];
        $_POST['subscribe_time']  = $data['subscribe_time'];
        $_POST['language']        = $data['language'];
        $_POST['nick_name']       = $data['nickname'];
        if(empty($res['wx_id'])){
            $_POST['wx_id'] = $data['openid'];
            M('Wx_user')->add($_POST);
        }else{
            $_POST['id']    = $res['id'];
            M('Wx_user')->save($_POST);
        }       
    }

    
    //缓存
    public function get_user_data(){
        $WechatAuth = new \Com\WechatAuth(self::APPID,self::APPSECRET);
        $token = $WechatAuth->getAccessToken();
        $wx_id  = $this->data['FromUserName'];
        $res = $WechatAuth->userInfo($wx_id);
        return $res;
    }

    public function sub_str($str, $from, $len){
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'. '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s','$1',$str);
    }

}
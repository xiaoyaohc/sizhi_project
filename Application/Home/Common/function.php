<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}

function get_wx_id($url){
    $WechatAuth = new \Com\WechatAuth(C('WX_CONFIG.appid'),C('WX_CONFIG.secret'));
    $access_token = $WechatAuth->getAccessToken();
    $code = I('code');
    
    if($_SESSION['wx_id'] ==''){
        if($code==''){
            $requestCodeURL = $WechatAuth->getRequestCodeURL($url,$state=1,$scope="snsapi_userinfo");
            header("Location:{$requestCodeURL}");
        }else{
            $token = $WechatAuth->getAccessToken($type='code',$code=$code);
            $wx_info = $WechatAuth->getUserInfo($token);
            $_SESSION['wx_id']=$wx_info['openid'];
            

            $model  = D('Wx_user');
            $data_arr['wx_id'] = $_SESSION['wx_id'];
            $data=$model->where($data_arr)->find();
            if(!$data){
                $data_arr['nick_name']       = $wx_info['nickname'];
                $data_arr['sex']             = $wx_info['sex'];
                $data_arr['province']        = $wx_info['province'];
                $data_arr['pic']             = $wx_info['headimgurl'];
                $data_arr['subscribe_time']  = time();//备注，连接返回没有关注时间
                $list = $model->add($data_arr);
            }       
        }
    }
}

function reply_customer($touser,$content){
    if(!isset($_SESSION['qy_access_token']) || empty($_SESSION['qy_access_token'])){
        //更换成自己的APPID和APPSECRET
        $APPID=C('WX_CONFIG.qyAppid');
        $APPSECRET=C('WX_CONFIG.qySecret');

        $TOKEN_URL="https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$APPID."&corpsecret=".$APPSECRET;
//    $TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$APPID."&secret=".$APPSECRET;

        $json=file_get_contents($TOKEN_URL);
        $result=json_decode($json);

        $_SESSION['qy_access_token'] = $result->access_token;
    }

    $data = '{
        "touser":"'.$touser.'",
        "msgtype":"text",
        "agentid": 0,
        "text":
        {
             "content":"'.$content.'"
        }
    }';

    $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$_SESSION['qy_access_token'];
//    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$_SESSION['qy_access_token'];

    $result = https_post($url,$data);
    $final = json_decode($result);
    if($final->errcode==40014){
        unset($_SESSION['qy_access_token']);
        reply_customer($touser,$content);
    }

    return $final;
}

function https_post($url,$data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        return 'Errno'.curl_error($curl);
    }
    curl_close($curl);
    return $result;
}
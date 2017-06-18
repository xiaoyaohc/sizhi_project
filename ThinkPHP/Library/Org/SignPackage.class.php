<?php
	
	namespace Org;
	class SignPackage {

		private $appId;
     	private $appSecret;

		public function getSignPackage() {
			$this->appId = 'wx39969b2a53bc47f8';
			$jsapiTicket = $this->getJsApiTicket();
			$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$timestamp = time();
			$nonceStr = $this->createNonceStr();

			// 这里参数的顺序要按照 key 值 ASCII 码升序排序
			$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

			$signature = sha1($string);

			$signPackage = array(
				"appId"     => $this->appId,
				"nonceStr"  => $nonceStr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
				"rawString" => $string
			);
			return $signPackage; 
		}

		private function createNonceStr($length = 16) {
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$str = "";
			for ($i = 0; $i < $length; $i++) {
				$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			}
			return $str;
		}

		private function getJsApiTicket() {
			// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
			$time         =time();
			$time         =$this->getMillisecond();
			
			$token        ="wphmoon";
			$sign         =strtolower(md5($time.$token));
			$memcache_obj = memcache_connect('10.144.213.183', 11211); 
			$ticket       = memcache_get($memcache_obj, 'ticket');
			if($ticket==""){
			//获取access_token的接口
			      $url = "http://115.29.178.222:8687/api/ticket/getTicket.do?time=$time&sign=$sign";
			      //$url = "http://115.29.178.222:8687/api/ticket/getTicket.do?time=1421288600615&sign=b6025fee31334bdeb874e805f9cdd722";
			      $ticket = $this->fetchUrl($url);
			      
			      $ticket = json_decode($ticket,true);
			      $ticket = $ticket['ticket'];

			      memcache_set($memcache_obj, 'ticket', $ticket, 0, 300);
			      $ticket = memcache_get($memcache_obj, 'ticket');
			      $ticket = $ticket;  

			  }
			return $ticket;
		}


		private function getAccessToken() {
			// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
			$this->appSecret ="xx";
			$data = json_decode(file_get_contents("access_token.json"));
			if ($data->expire_time < time()) {
				$url          = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
				$res          = json_decode($this->httpGet($url));
				$access_token = $res->access_token;
				if ($access_token) {
					$data->expire_time  = time() + 7000;
					$data->access_token = $access_token;
					$fp                 = fopen("access_token.json", "w");
					fwrite($fp, json_encode($data));
					fclose($fp);
				}
			} else {
				$access_token = $data->access_token;
			}
			return $access_token;
		}

		private function httpGet($url) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_TIMEOUT, 500);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_URL, $url);

			$res = curl_exec($curl);
			curl_close($curl);

			return $res;
		}

		private function getMillisecond() {
			list($s1, $s2) = explode(' ', microtime());
			return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
		}

		private function fetchUrl($url, $time=120){
			$curl_opt = array(
			  CURLOPT_URL => $url,
			  CURLOPT_AUTOREFERER => TRUE,
			  CURLOPT_RETURNTRANSFER => TRUE,
			  CURLOPT_CONNECTTIMEOUT => 0,
			  CURLOPT_TIMEOUT => $time,
			);
			$ch = curl_init();
			curl_setopt_array($ch, $curl_opt);
			$contents = curl_exec($ch);
			curl_close($ch);

			return $contents;
		}
		
	}

?>
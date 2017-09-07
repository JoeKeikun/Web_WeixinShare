<?php
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

    /**
     * 辅助函数
     */
    function get_php_file($filename, $offset = 0) {
        if (file_exists ($filename )) {
            return trim(substr(file_get_contents($filename), $offset));
        } else {
            return "";
        }
    }
    function set_php_file($filename, $content) {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    //--1--getJsApiTicket
    function getJsApiTicket($appid, $appsecret){
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(get_php_file("jsapi_ticket.php", 15));

        if (is_null($data) || $data->expire_time < time()) {
            $access_token = getAccessToken($appid, $appsecret);

            // 如果是企业号用以下 URL 获取 ticket
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token={$access_token}";
            $res = json_decode(httpGet($url));

            if (!is_null($res)) {
                $ticket = $res->ticket;

                if ($ticket) {
                    $newRes = array();
                    $newRes['expire_time'] = time() + 7000;
                    $newRes['jsapi_ticket'] = $ticket;
                    set_php_file("jsapi_ticket.php", json_encode($newRes));
                }
            }
        }else {
          $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    };

    //--2--getAccessToken
    function getAccessToken($appid, $appsecret){
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(get_php_file("access_token.php", 15));

        if (is_null($data) || $data->expire_time < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
            $res = json_decode(httpGet($url));

            $access_token = $res->access_token;

            if ($access_token) {
                $newRes = array();
                $newRes['expire_time'] = time() + 7000;
                $newRes['access_token'] = $access_token;
                set_php_file("access_token.php", json_encode($newRes));
            }
        } else {
            $access_token = $data->access_token;
        }

        return $access_token;
    }

    //--3--httpGet($url)
    function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    //--4--
    function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
          $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //--5--生成签名
    function getSignPackage($appid, $appsecret, $url){
        //--1--getJsApiTicket
        $ticket = getJsApiTicket($appid, $appsecret);
        //----
        // $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        //----
        $timestamp = time();
        //--5--
        $nonceStr = createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket={$ticket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $appid,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "concat"=>$string,
        );

        return $signPackage;
    }



    /**
     * 读取配置
     */
    $config = json_decode(get_php_file('.env'));
    if (!is_null($config)) {
        // 生成code
        $signPackage = getSignPackage($config->appid, $config->appsecret, $_GET['url']);
        echo  json_encode($signPackage);
    } else {
        echo "{'error': '无法读取appid和appsecret'}";
    }
?>
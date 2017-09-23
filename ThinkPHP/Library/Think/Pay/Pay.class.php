<?php

namespace Think\Pay;

abstract class Pay {

    protected $config;
    protected $info;

    public function __construct($config) {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 配置检查
     * @return boolean
     */
    public function check() {
        return true;
    }

    /**
     * 验证通过后获取订单信息
     * @return type
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * 生成订单号
     * 可根据自身的业务需求更改
     */
    public function createOrderNo() {
        $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        return $year_code[intval(date('Y')) - 2010] .
                strtoupper(dechex(date('m'))) . date('d') .
                substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('d', rand(0, 99));
    }

    /**
     * 建立提交表单
     */
    abstract public function buildRequestForm(\Think\Pay\PayVo $vo);

    /**
     * 构造表单
     */
    protected function _buildForm($params, $gateway, $method = 'post', $charset = 'utf-8',$type='') {

        header("Content-type:text/html;charset={$charset}");
        $sHtml = "<form id='paysubmit' name='paysubmit' action='{$gateway}' method='{$method}'>";
        if($type=="alipay_wap"){
            foreach ($params as $k => $v) {
               $d[]=$k."=".$v;
            }
            header("Content-type: text/html; charset=utf-8"); 
            $pay_url=$gateway.'?'.implode("&",$d);
            return  $pay_url;
        }else{

            foreach ($params as $k => $v) {
                $sHtml.= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
            }

            $sHtml = $sHtml . "</form>Loading......";
            $sHtml = $sHtml . "<script>document.forms['paysubmit'].submit();</script>";
            return $sHtml;
        }
  
    }


//https://mapi.alipay.com/gateway.do&_input_charset=utf-8&body=22&it_b_pay=30m&notify_url=http://sy.cn/callback.php/Notify/notify&apitype/alipay/method/notify/out_trade_no/SP_20170425213720jZUR/partner/2088122686942163/payment_type/1/return_url/http://sy.cn/callback.php/Notify/notify/apitype/alipay/method/return/seller_email/xigumeidi@qq.com/seller_id/2088122686942163/service/alipay.wap.create.direct.pay.by.user/show_url/http://www.baidu.com/subject/11/total_fee/1/sign/8d578ccbe5543db60bb19b163b06bd1f/sign_type/MD5

 
    /**
     * 支付通知验证
     */
    abstract public function verifyNotify($notify);

    /**
     * 异步通知验证成功返回信息
     */
    public function notifySuccess() {
        echo "success";
    }

    final protected function fsockOpen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype = 'URLENCODE', $allowcurl = TRUE, $position = 0, $files = array()) {
        $return = '';
        $matches = parse_url($url);
        $scheme = $matches['scheme'];
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : ($scheme == 'http' ? '80' : '');
        $boundary = $encodetype == 'URLENCODE' ? '' : random(40);

        if ($post) {
            if (!is_array($post)) {
                parse_str($post, $post);
            }
            $this->formatPostkey($post, $postnew);
            $post = $postnew;
        }
        if (function_exists('curl_init') && function_exists('curl_exec') && $allowcurl) {
            $ch = curl_init();
            $httpheader = array();
            if ($ip) {
                $httpheader[] = "Host: " . $host;
            }
            if ($httpheader) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
            }
            curl_setopt($ch, CURLOPT_URL, $scheme . '://' . ($ip ? $ip : $host) . ($port ? ':' . $port : '') . $path);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            if ($post) {
                curl_setopt($ch, CURLOPT_POST, 1);
                if ($encodetype == 'URLENCODE') {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                } else {
                    foreach ($post as $k => $v) {
                        if (isset($files[$k])) {
                            $post[$k] = '@' . $files[$k];
                        }
                    }
                    foreach ($files as $k => $file) {
                        if (!isset($post[$k]) && file_exists($file)) {
                            $post[$k] = '@' . $file;
                        }
                    }
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                }
            }
            if ($cookie) {
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $data = curl_exec($ch);
            $status = curl_getinfo($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            if ($errno || $status['http_code'] != 200) {
                return;
            } else {
                $GLOBALS['filesockheader'] = substr($data, 0, $status['header_size']);
                $data = substr($data, $status['header_size']);
                return !$limit ? $data : substr($data, 0, $limit);
            }
        }

        if ($post) {
            if ($encodetype == 'URLENCODE') {
                $data = http_build_query($post);
            } else {
                $data = '';
                foreach ($post as $k => $v) {
                    $data .= "--$boundary\r\n";
                    $data .= 'Content-Disposition: form-data; name="' . $k . '"' . (isset($files[$k]) ? '; filename="' . basename($files[$k]) . '"; Content-Type: application/octet-stream' : '') . "\r\n\r\n";
                    $data .= $v . "\r\n";
                }
                foreach ($files as $k => $file) {
                    if (!isset($post[$k]) && file_exists($file)) {
                        if ($fp = @fopen($file, 'r')) {
                            $v = fread($fp, filesize($file));
                            fclose($fp);
                            $data .= "--$boundary\r\n";
                            $data .= 'Content-Disposition: form-data; name="' . $k . '"; filename="' . basename($file) . '"; Content-Type: application/octet-stream' . "\r\n\r\n";
                            $data .= $v . "\r\n";
                        }
                    }
                }
                $data .= "--$boundary\r\n";
            }
            $out = "POST $path HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data; boundary=$boundary\r\n";
            $header .= 'Content-Length: ' . strlen($data) . "\r\n";
            $header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $header .= "Host: $host:$port\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cache-Control: no-cache\r\n";
            $header .= "Cookie: $cookie\r\n\r\n";
            $out .= $header;
            $out .= $data;
        } else {
            $out = "GET $path HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $header .= "Host: $host:$port\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cookie: $cookie\r\n\r\n";
            $out .= $header;
        }

        $fpflag = 0;
        if (!$fp = @fsocketopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout)) {
            $context = array(
                'http' => array(
                    'method' => $post ? 'POST' : 'GET',
                    'header' => $header,
                    'content' => $post,
                    'timeout' => $timeout,
                ),
            );
            $context = stream_context_create($context);
            $fp = @fopen($scheme . '://' . ($ip ? $ip : $host) . ':' . $port . $path, 'b', false, $context);
            $fpflag = 1;
        }

        if (!$fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if (!$status['timed_out']) {
                while (!feof($fp) && !$fpflag) {
                    $header = @fgets($fp);
                    $headers .= $header;
                    if ($header && ($header == "\r\n" || $header == "\n")) {
                        break;
                    }
                }
                $GLOBALS['filesockheader'] = $headers;

                if ($position) {
                    for ($i = 0; $i < $position; $i++) {
                        $char = fgetc($fp);
                        if ($char == "\n" && $oldchar != "\r") {
                            $i++;
                        }
                        $oldchar = $char;
                    }
                }

                if ($limit) {
                    $return = stream_get_contents($fp, $limit);
                } else {
                    $return = stream_get_contents($fp);
                }
            }
            @fclose($fp);
            return $return;
        }
    }

    final protected function formatPostkey($post, &$result, $key = '') {
        foreach ($post as $k => $v) {
            $_k = $key ? $key . '[' . $k . ']' : $k;
            if (is_array($v)) {
                $this->formatPostkey($v, $result, $_k);
            } else {
                $result[$_k] = $v;
            }
        }
    }

}

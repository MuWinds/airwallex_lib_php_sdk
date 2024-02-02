<?php
//AirWallex依赖库
class Airwallex
{
    private $gatewayUrl = 'https://api.airwallex.com/api/v1';
    //获取验证token
    public function getToken($clientid, $apiKey)
    {
        //初始化参数
        $headerArray = array(
            "Content-type:application/json",
            "x-client-id:$clientid",
            "x-api-key:$apiKey"
        );
        $submit_url = $this->gatewayUrl . '/authentication/login';
        $output = self::postCurl($submit_url, $headerArray, null);
        $tokenArray = json_decode($output, true);
        $token = $tokenArray['token'];
        return $token;
    }

    //支付订单初始化，返回付款ID
    public function initializePayment($token, $currency_apiKey, $amount, $currency, $merchant_order_id, $return_url)
    {
        //初始化参数
        $headerArray = array(
            "Content-type: application/json",
            "Authorization: Bearer $token"
        );
        $submit_url = $this->gatewayUrl . '/pa/payment_intents/create';
        $data = array(
            'request_id' => uniqid(),
            'amount' => $amount,
            'currency' => $currency,
            'merchant_order_id' => $merchant_order_id,
            'return_url' => $return_url
        );
        $output = self::postCurl($submit_url, $headerArray, json_encode($data));
        $result_array = json_decode($output, true);
        $payment_id = $result_array['id'];
        return $payment_id;
    }

    //发起支付宝支付，返回跳转链接
    public function obtainAlipayBrowserUrl($token, $payment_id)
    {
        //初始化参数和请求地址
        $headerArray = array(
            "Content-type: application/json",
            "Authorization: Bearer $token"
        );
        $submit_url = $this->gatewayUrl . '/pa/payment_intents/' . $payment_id . '/confirm';
        //检测是否为手机端构建函数
        if (self::checkMobile()) {
            $data = array(
                'request_id' => uniqid(),
                'payment_method' => array(
                    'type' => 'alipaycn',
                    'alipaycn' => array(
                        'flow' => 'mweb',
                        'os_type' => 'android'
                    )
                )
            );
        } else {
            $data = array(
                'request_id' => uniqid(),
                'payment_method' => array(
                    'type' => 'alipaycn',
                    'alipaycn' => array(
                        'flow' => 'webqr'
                    )
                )
            );
        }
        $output = self::postCurl($submit_url, $headerArray, json_encode($data));
        $result_array = json_decode($output, true);
        $action_array = $result_array['next_action'];
        $url = $action_array['url'];
        return $url;
    }

    //查询订单状态
    public function queryOrder($token, $id)
    {
        $headerArray = array(
            "Content-type: application/json",
            "Authorization: Bearer $token"
        );
        $query_url = $this->gatewayUrl . '/pa/payment_intents/' . $id;
        $response = self::getCurl($query_url, $headerArray);
        $result_array = json_decode($response, true);
        return $result_array;
    }

    //webhook验证签名
    public function verifySignature($timestamp, $signature, $sign_key, $body)
    {
        $value_to_digest = $timestamp + $body;
        $generate_signature = hash_hmac('sha256', $value_to_digest, $sign_key);
        if ($generate_signature == $signature) {
            return true;
        } else {
            return false;
        }
    }

    //发起post请求
    function postCurl($url, $header, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //发起get请求
    function getCurl($url, $header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    //判断是否为手机端，是返回true，否则返回false
    function checkMobile()
    {
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $ualist = array('android', 'midp', 'nokia', 'mobile', 'iphone', 'ipod', 'blackberry', 'windows phone');
        if ((dstrpos($useragent, $ualist) || strexists($_SERVER['HTTP_ACCEPT'], "VND.WAP") || strexists($_SERVER['HTTP_VIA'], "wap")))
            return true;
        else
            return false;
    }
}

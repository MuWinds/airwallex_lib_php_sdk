# airwallex_lib_php
Airwallex空中云汇，PHP单文件依赖  
目前仅支持支付宝，其他的后续更新  
发起支付，获取支付宝支付链接：  
```php
$airwallex = new Airwallex();
$return_url = $siteurl . 'pay/return/' . TRADE_NO . '/';
$amount = (float)$order['realmoney'];
$token = $airwallex->getToken($channel['appid'], $channel['appkey']);
$request_id = time() . rand();
$payment_id = $airwallex->initializePayment($token, $channel['appsecret'], $request_id, $amount, 'CNY', TRADE_NO, $return_url);
$url = $airwallex->obtainAlipayBrowserUrl($token, $request_id + 1, $payment_id);
```  
验证签名：
```
$x_timestamp = $_SERVER['HTTP-X-TIMESTAMP'];
$x_signature = $_SERVER['HTTP-X-SIGNATURE'];
$webhook_key = ''; //在此设置webhook的签名密钥
$body = file_get_contents("php://input");
$airwallex = new Airwallex();
$verify_result = $airwallex->verifySignature($x_timestamp, $x_signature, $webhook_key, $body);
if ($verify_result) { //验证成功
  Yourself's Logic
    } else {
        echo '';
    }
} else {
    header("HTTP/1.0 502 Fatal Error"); //返回502错误
}
```

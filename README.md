# airwallex_lib_php_sdk
Airwallex空中云汇，PHP单文件SDK和依赖  
目前仅支持支付宝，其他的后续更新  
获取Token：  
```php
require(./airwallex_lib.php);
$airwallex = new Airwallex();
$token = $airwallex->getToken($clientid, $apikey);//自行替换
echo ($token);
```  
发起支付，获取支付宝支付链接：  
```php
require(./airwallex_lib.php);
$airwallex = new Airwallex();
$return_url = 'https://fuck-airwallex.com';
$order_no = '1145141919810';
$currency = 'CNY';
$amount = 11.4;
$token = $airwallex->getToken($clientid, $apikey);//自行替换
$payment_id = $airwallex->initializePayment($token, $apikey, $amount, $currency, $order_no, $return_url);
$url = $airwallex->obtainAlipayBrowserUrl($token, $payment_id);
```  
验证webhook签名：
```php
require(./airwallex_lib.php);
$x_timestamp = $_SERVER['HTTP_X_TIMESTAMP'];
$x_signature = $_SERVER['HTTP_X_SIGNATURE'];
$webhook_key = ''; //在此设置webhook的签名密钥
$body = file_get_contents("php://input");//获取body
$airwallex = new Airwallex();
$verify_result = $airwallex->verifySignature($x_timestamp, $x_signature, $webhook_key, $body);
if ($verify_result) { //验证成功
  ......
  ......
  ......
  自行更新逻辑
} else {
    header("HTTP/1.0 502 Fatal Error"); //返回502错误，使其重传
}
```

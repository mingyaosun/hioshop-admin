<?php
header('Access-Control-Allow-Origin: *'); //*表示允许所有IP访问，如果怕被攻击，这里可以改成后端管理所在服务器的ip地址，即仅限该ip地址能存储图片；
header('Content-Type:application/json');
header('Access-Control-Allow-Methods:POST'); //支持的http动作
if($_POST['token']){
    if($_POST['token']=='MIIBUwIBADANBgkqhkiG9w0BAQEFAASCAT'){//token123456替换成自己设置的token
	    $result=0;
	    if(unlink($_POST['key']))$result=1;
	    $data=array("result"=>$result);
        print_r(json_encode($data));
    }
}else{echo '测试通过';}?>


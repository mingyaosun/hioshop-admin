
<?php
header('Access-Control-Allow-Origin: *');
if($_POST['token']){
    if($_POST['token']=='MIIBUwIBADANBgkqhkiG9w0BAQEFAASCAT'){//token123456替换成自己设置的token
	    $result=0;
	    if(unlink($_POST['key']))$result=1;
	    $data=array("result"=>$result);
        print_r(json_encode($data));
    }
}else{echo '测试通过';}?>


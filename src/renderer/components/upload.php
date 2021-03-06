<?php
header('Access-Control-Allow-Origin: *'); //*表示允许所有IP访问，如果怕被攻击，这里可以改成后端管理所在服务器的ip地址，即仅限该ip地址能存储图片；
header('Content-Type:application/json');
header('Access-Control-Allow-Methods:POST'); //支持的http动作
if ($_POST['token']) {
    if ($_POST['token'] == 'MIIBUwIBADANBgkqhkiG9w0BAQEFAASCAT') {
        function uuid()
        {
            $chars = md5(uniqid(mt_rand(), true));
            $uuid = substr($chars, 0, 8) . '-' . substr($chars, 8, 4) . '-' . substr($chars, 12, 4) . '-' . substr($chars, 16, 4) . '-' . substr($chars, 20, 12);
            return $uuid;
        }
        // 允许上传的图片后缀
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["file"]["name"]);
        // echo $_FILES["file"]["size"];
        $extension = end($temp);     // 获取文件后缀名
        if ((($_FILES["file"]["type"] == "image/gif")
                || ($_FILES["file"]["type"] == "image/jpeg")
                || ($_FILES["file"]["type"] == "image/jpg")
                || ($_FILES["file"]["type"] == "image/pjpeg")
                || ($_FILES["file"]["type"] == "image/x-png")
                || ($_FILES["file"]["type"] == "image/png"))
            && ($_FILES["file"]["size"] < 20480000)   // 小于 20M
            && in_array($extension, $allowedExts)
        ) {
            if ($_FILES["file"]["error"] > 0) {
                echo "错误：: " . $_FILES["file"]["error"] . "<br>";
            } else {
                // echo "上传文件名: " . $_FILES["file"]["name"] . "<br>";
                // echo "文件类型: " . $_FILES["file"]["type"] . "<br>";
                // echo "文件大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
                // echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"] . "<br>";

                // 判断当前目录下的 upload 目录是否存在该文件
                // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
                if (file_exists("upload/" . $_FILES["file"]["name"])) {
                    unlink("upload/" . $_FILES["file"]["name"]);
                    // echo $_FILES["file"]["name"] . " 文件已经存在。 ";
                }
                // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
                $dir = "upload/" . uuid() . $_FILES["file"]["name"];
                move_uploaded_file($_FILES["file"]["tmp_name"], $dir);
                // echo "文件存储在: " . "upload/" . $_FILES["file"]["name"];
                $data = array("key" => $dir);
                print_r(json_encode($data));
            }
        } else {
            echo "非法的文件格式";
        }
    }
} else {
    echo '文件服务器';
}

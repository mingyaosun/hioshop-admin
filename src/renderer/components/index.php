<?php
header('Access-Control-Allow-Origin: *'); //*表示允许所有IP访问，如果怕被攻击，这里可以改成后端管理所在服务器的ip地址，即仅限该ip地址能存储图片；
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); //支持的http动作
echo 'zheshitoken'+$_POST['token'] ;
if ($_POST['token']) {
    if ($_POST['token'] == 'token123456') {
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
            && ($_FILES["file"]["size"] < 204800)   // 小于 200 kb
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
                    echo $_FILES["file"]["name"] . " 文件已经存在。 ";
                } else {
                    // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
                    move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $_FILES["file"]["name"]);
                    // echo "文件存储在: " . "upload/" . $_FILES["file"]["name"];
                    print_r("upload/" . $_FILES["file"]["name"]);
                }
            }
        } else {
            echo "非法的文件格式";
        }
    }
} else {
    echo '文件服务器';
}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 10:21
 */


class ActionApi extends Base
{
    public function upload()
    {
        $base64 = $this->input('base64');
        $rs     = $this->saveImgByBase64($base64, __DIR__ . '/' . date('Ymd') . '/');
        return $rs;
    }
}

class Base
{
    public function input($field)
    {
        return isset($_REQUEST[$field]) ? trim($_REQUEST[$field]) : '';
    }

    public function saveImgByBase64($base64, $savePath)
    {
        $maxSize = 5 * pow(1024, 2);//允许5m大小上传
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }
        $suffixs = array('jpg', 'gif', 'png', 'jpeg');
        if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $matches)) {
            return self::setRs(100, '解析Base64错误');
        }
        //判断后缀
        $suffix = $matches[2];
        if (!in_array($suffix, $suffixs)) {
            return self::setRs(100, '不支持' . $suffix . '后缀上传,只允许' . join(',', $suffixs) . '格式的上传');
        }
        $imgBase64Data = str_replace($matches[1], '', $base64);
        //判断大小
        $size = strlen($imgBase64Data);
        if ($size > $maxSize) {
            return self::setRs(102, '上传的图片太大,允许' . $maxSize / pow(1024, 2) . 'M');
        }
        $imgData   = base64_decode($imgBase64Data);
        $img_name  = uniqid() . '.' . $suffix;
        $full_path = $savePath . $img_name;
        $bool      = file_put_contents($full_path, $imgData);
        if ($bool) {
            $path = str_replace(__DIR__, '', $savePath);
            $url  = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $path . $img_name;
            self::log([$url, $savePath, $img_name, __DIR__, $_SERVER], 'ss');
            return self::setRs(0, '上传成功', $url);
        }
        return self::setRs(400, '上传失败');
    }

    public static function load()
    {
        return new self();
    }

    public function log($content, $flag)
    {
        ob_start();
        echo date('Y-m-d H:i:s') . '---------------------' . $flag . '-----------------------------' . "\n";
        var_dump($content);
        echo "\n";
        $content = ob_get_contents();
        ob_end_clean();
        return file_put_contents('debug.txt', $content, 8);
    }

    /**
     * 设置返回json
     * @param $status
     * @param $msg
     * @param null $data
     * @return string
     */
    public static function setJson($status, $msg, $data = null)
    {
        return json_encode(self::setRs($status, $msg, $data));
    }

    /**
     * 设置返回结果
     * @param $status
     * @param $msg
     * @param null $data
     * @return array
     */
    public static function setRs($status, $msg, $data = null)
    {
        $content = array(
            'status' => $status,
            'msg'    => $msg,
        );
        if (!is_null($data)) {
            $content['data'] = $data;
        }
        return $content;
    }
}

$act  = isset($_GET['act']) ? trim($_GET['act']) : '';
$obj  = new ActionApi();
$bool = method_exists($obj, $act);
Base::load()->log([$act, $obj], 'check_method2');
if (!method_exists($obj, $act)) {
    exit(Base::setJson(404, $act . '方法不存在'));
}
$result = call_user_func(array($obj, $act));
if (is_array($result)) {
    exit(json_encode($result));
} else {
    exit($result);
}
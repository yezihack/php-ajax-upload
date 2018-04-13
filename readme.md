# php-ajax上传图片

- 将图片转换成base64进行上传
- 采用FileReader获取base64数据

## js核心代码
```html
<input class="form-control" type="file" id="imgId">
```
```javascript
var fileObj = $("#imgId")[0].files[0];
var reader = new FileReader();
reader.readAsDataURL(fileObj);
reader.onload = function () {
    var base64 = reader.result;
    console.log(base64);
};
```


## php核心代码
```php
if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $matches)) {
    $suffix = $matches[2];//获取后缀名称
    $imgBase64Data = str_replace($matches[1], '', $base64);//获取base64原始数据    
    $size = strlen($imgBase64Data);//获取大小,byte单位    
    $imgData   = base64_decode($imgBase64Data);//解析base64
    $img_name  = uniqid() . '.' . $suffix;
    $full_path = __DIR__. '/' . $img_name;
    $bool      = file_put_contents($full_path, $imgData);
}
```
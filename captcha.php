<?php
session_start();

$code = '';
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // 避免易混淆字符
for ($i = 0; $i < 5; $i++) {
    $code .= $chars[random_int(0, strlen($chars) - 1)];
}

$_SESSION['captcha_code'] = $code;

$width = 120;
$height = 40;
$image = imagecreate($width, $height);

$bgColor = imagecolorallocate($image, 30, 30, 47);
$textColor = imagecolorallocate($image, 0, 180, 216);
$noiseColor = imagecolorallocate($image, 100, 100, 100);

// 填充背景
imagefill($image, 0, 0, $bgColor);

// 添加噪点
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noiseColor);
}

// 添加验证码文本
$fontSize = 20;
$fontFile = __DIR__ . '/fonts/arial.ttf';  // 需准备字体文件，或使用系统字体路径
imagettftext($image, $fontSize, 0, 15, 30, $textColor, $fontFile, $code);

// 输出图片
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>
<!--
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
-->

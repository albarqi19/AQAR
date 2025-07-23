<?php

// إنشاء شعار بديل بسيط
if (extension_loaded('gd')) {
    // إنشاء شعار بسيط بدون Alpha channel
    $img = imagecreate(200, 200);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $blue = imagecolorallocate($img, 0, 123, 255);
    $gray = imagecolorallocate($img, 100, 100, 100);
    
    // رسم إطار دائري
    imageellipse($img, 100, 100, 180, 180, $blue);
    imageellipse($img, 100, 100, 160, 160, $blue);
    
    // كتابة النص
    imagestring($img, 5, 70, 80, 'AQRI', $blue);
    imagestring($img, 3, 65, 110, 'Property', $gray);
    imagestring($img, 3, 60, 125, 'Management', $gray);
    
    imagejpeg($img, 'public/logo_simple.jpg', 90);
    imagedestroy($img);
    echo "تم إنشاء شعار بديل بنجاح!\n";
} else {
    echo "GD غير متاح - لا يمكن إنشاء الشعار\n";
    
    // إنشاء ملف نصي بديل
    file_put_contents('public/logo_simple.txt', 'AQRI - Property Management System');
    echo "تم إنشاء ملف نصي بديل\n";
}

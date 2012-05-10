<?php
ini_set('max_execution_time', 60);

session_start();

if (!empty($_SERVER["DOCUMENT_ROOT"])) $DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"].'/';
//else $DOCUMENT_ROOT = '/home/vshosts/hosts/u1011/greenfish.org.ru/www/';

define('__DOCUMENT_ROOT', $DOCUMENT_ROOT);

if (is_dir('Z:/home/shop/www/')) define('__CORE_DOCUMENT_ROOT', 'Z:/home/shop/www/');
else define('__CORE_DOCUMENT_ROOT', $DOCUMENT_ROOT);

require_once(__CORE_DOCUMENT_ROOT."Core/Core.php");

$Core= new Core;

echo '<table>';

//Category listing
$res = getSqlResult('SELECT * FROM '.__MYSQL_PRE.'product_category
    ORDER BY list_order', '$Cart: Проверка на наличие подкатегорий');
$num = mysql_num_rows($res);

for($i=0;$i<$num;$i++){
    $row=mysql_fetch_array($res);

    echo '<tr><td>'.$row['category_name'].'</td>
        <td>
            <img src="uploads/Shop/img/full/'.$row['category_thumb_image'].'" width="80px">
            <img src="uploads/Shop/img/preview/'.$row['category_thumb_image'].'" width="80px">
            <img src="uploads/Shop/img/sourse/'.$row['category_thumb_image'].'" width="80px">
        </td>
        <td>
            <img src="uploads/Shop/Category/'.$row['category_id'].'/Sourse/'.$row['category_thumb_image'].'" width="80px">
        </td></tr>';

    if (is_file('uploads/Shop/img/sourse/'.$row['category_thumb_image']) AND !is_file('uploads/Shop/Category/'.$row['category_id'].'/Sourse/'.$row['category_thumb_image'])) {
@       mkdir('uploads/Shop/Category/');
@       mkdir('uploads/Shop/Category/'.$row['category_id'].'/');
@       mkdir('uploads/Shop/Category/'.$row['category_id'].'/Sourse/'); 

        copy('uploads/Shop/img/sourse/'.$row['category_thumb_image'].'', 'uploads/Shop/Category/'.$row['category_id'].'/Sourse/'.$row['category_thumb_image']);
    };

};

echo '</table>';

echo '<hr>';

echo '<table>';

//Goods listing
$res = getSqlResult('SELECT * FROM '.__MYSQL_PRE.'product
    WHERE  product_publish =\'Y\' ORDER BY list_order', '$Cart: Проверка на наличие товаров');
$num=mysql_num_rows($res);
for($i=0;$i<$num;$i++){
    $row = mysql_fetch_array($res);

    /*
    echo '<tr><td>'.$row['product_name'].'</td>
        <td>
            <img src="uploads/Shop/img/full/'.$row['product_full_image'].'" width="80px">
            <img src="uploads/Shop/img/preview/'.$row['product_thumb_image'].'" width="80px">
            <img src="uploads/Shop/img/sourse/'.$row['product_full_image'].'" width="80px">
        </td>
        <td>
            <img src="uploads/Shop/Goods/'.$row['product_id'].'/Sourse/'.$row['product_thumb_image'].'" width="80px">
            <a href="uploads/Shop/Goods/'.$row['product_id'].'/Full/'.$row['product_thumb_image'].'" target="_blank">
                <img src="uploads/Shop/Goods/'.$row['product_id'].'/Full/'.$row['product_thumb_image'].'" width="80px">
            </a>
            <a href="uploads/Shop/Goods/'.$row['product_id'].'/Preview/'.$row['product_thumb_image'].'" target="_blank">
                <img src="uploads/Shop/Goods/'.$row['product_id'].'/Preview/'.$row['product_thumb_image'].'" width="80px">
            </a>
        </td></tr>';

*/

    if (is_file('uploads/Shop/img/sourse/'.$row['product_full_image']) AND !is_file('uploads/Shop/Goods/'.$row['product_id'].'/Sourse/'.$row['product_full_image'])) {

@       mkdir('uploads/Shop/Goods/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/Sourse/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/Full/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/Preview/');

        copy('uploads/Shop/img/sourse/'.$row['product_full_image'], 'uploads/Shop/Goods/'.$row['product_id'].'/Sourse/'.$row['product_full_image']);
        copy('uploads/Shop/img/sourse/'.$row['product_full_image'], 'uploads/Shop/Goods/'.$row['product_id'].'/Full/'.$row['product_full_image']);
        copy('uploads/Shop/img/sourse/'.$row['product_full_image'], 'uploads/Shop/Goods/'.$row['product_id'].'/Preview/'.$row['product_full_image']);

        $filePath = 'uploads/Shop/Goods/'.$row['product_id'].'/Full/'.$row['product_full_image'];

        $imageinfo = getimagesize ($filePath);

        if ($imageinfo[0]>400 OR $imageinfo[1]>400) {
            if (__IMAGE_MAGICK_TYPE == 'class') {
                $im = new Imagick($filePath);
                //$im->trimImage(0);
                $im->scaleImage($width, $height, true);
                $im->writeImage($filePath);
                $im->destroy();
            } else if (__IMAGE_MAGICK_TYPE == 'system') {
                passthru(''.__IMAGE_MAGICK.'convert '.$filePath.'  -quality 0  -compress None  -resize 400x400 	'.$filePath);
            };
        };

        $filePath = 'uploads/Shop/Goods/'.$row['product_id'].'/Preview/'.$row['product_full_image'];

        $imageinfo = getimagesize ($filePath);

        if ($imageinfo[0]>80 OR $imageinfo[1]>80) {
            if (__IMAGE_MAGICK_TYPE == 'class') {
                $im = new Imagick($filePath);
                //$im->trimImage(0);
                $im->scaleImage($width, $height, true);
                $im->writeImage($filePath);
                $im->destroy();
            } else if (__IMAGE_MAGICK_TYPE == 'system') {
                passthru(''.__IMAGE_MAGICK.'convert '.$filePath.'  -quality 0  -compress None  -resize 80x80 	'.$filePath);
            };
        };
    };


    if (is_file('uploads/Shop/img/sourse/'.$row['product_thumb_image']) AND !is_file('uploads/Shop/Goods/'.$row['product_id'].'/Sourse/'.$row['product_thumb_image'])) {

@       mkdir('uploads/Shop/Goods/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/Sourse/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/Full/');
@       mkdir('uploads/Shop/Goods/'.$row['product_id'].'/Preview/');

        copy('uploads/Shop/img/sourse/'.$row['product_thumb_image'], 'uploads/Shop/Goods/'.$row['product_id'].'/Sourse/'.$row['product_thumb_image']);
        copy('uploads/Shop/img/sourse/'.$row['product_thumb_image'], 'uploads/Shop/Goods/'.$row['product_id'].'/Full/'.$row['product_thumb_image']);
        copy('uploads/Shop/img/sourse/'.$row['product_thumb_image'], 'uploads/Shop/Goods/'.$row['product_id'].'/Preview/'.$row['product_thumb_image']);

        $filePath = 'uploads/Shop/Goods/'.$row['product_id'].'/Full/'.$row['product_thumb_image'];

        $imageinfo = getimagesize ($filePath);

        if ($imageinfo[0]>400 OR $imageinfo[1]>400) {
            if (__IMAGE_MAGICK_TYPE == 'class') {
                $im = new Imagick($filePath);
                //$im->trimImage(0);
                $im->scaleImage($width, $height, true);
                $im->writeImage($filePath);
                $im->destroy();
            } else if (__IMAGE_MAGICK_TYPE == 'system') {
                passthru(''.__IMAGE_MAGICK.'convert '.$filePath.'  -quality 0  -compress None  -resize 400x400 	'.$filePath);
            };
        };

        $filePath = 'uploads/Shop/Goods/'.$row['product_id'].'/Preview/'.$row['product_thumb_image'];

        $imageinfo = getimagesize ($filePath);

        if ($imageinfo[0]>80 OR $imageinfo[1]>80) {
            if (__IMAGE_MAGICK_TYPE == 'class') {
                $im = new Imagick($filePath);
                //$im->trimImage(0);
                $im->scaleImage($width, $height, true);
                $im->writeImage($filePath);
                $im->destroy();
            } else if (__IMAGE_MAGICK_TYPE == 'system') {
                passthru(''.__IMAGE_MAGICK.'convert '.$filePath.'  -quality 0  -compress None  -resize 80x80 	'.$filePath);
            };
        };
    };
};

echo '</table>';

?>

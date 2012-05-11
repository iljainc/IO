<?php

define('__MYSQL_HOST',      'localhost');
define('__MYSQL_USER',      '');
define('__MYSQL_PASS',      '');
define('__MYSQL_DB',        '');
define('__MYSQL_PRE',       '');

define('__PRE_INI',         'ini/');
define('__PRE_LOG',         'log/');
define('__DATE',            date('U'));

define('__IMAGE_MAGICK_TYPE', 'class'); // class | system
define('__IMAGE_MAGICK', 'class'); // class | system


// check for develop branch
// check 0.2

/*
define('__IMAGE_MAGICK_TYPE', 'system');
define('__IMAGE_MAGICK',    '/usr/bin/');
 *
define('__ADMIN_MAIL',      'superseo@yandex.ru');
define('__SITE_URL',        'greenfish.net.ru');

define('__USE_TPL',         'index.html');
define('__USE_ADM_TPL',     'adm.html');
define('__USE_LOGIN_TPL',   'login.html');

define('__PRE_TPL',         'templates/');

define('__DEFAULT_MODULE',  'Page');
define('__DEFAULT_ADM_MODULE',  'YandexDirectJSON');

define('__USE_SYSTEM_ADM_TPL',      true);
define('__SYSTEM_TPL',              'templates/system/');

define('__IMAGE_MAGICK',    '/usr/bin/');

define('__ON_PAGE',         '20');
*/

/*
 * -----------------------------------------------------------------------------
 *
 * $_MODULES['module']['access']['use']['der'] = true - подрозумевает доступ без спец указания в настройках пользователя
 *
 */

$_MODULES['User']['name']                                   = 'Пользователи';
$_MODULES['User']['access']['use']['der']                   = false;
$_MODULES['User']['access']['use']['name']                  = 'управление';


/*
 * -----------------------------------------------------------------------------
 */

$_GLOBAL['monthArr'][1]='янв';
$_GLOBAL['monthArr'][2]='фев';
$_GLOBAL['monthArr'][3]='мар';
$_GLOBAL['monthArr'][4]='апр';
$_GLOBAL['monthArr'][5]='май';
$_GLOBAL['monthArr'][6]='июн';
$_GLOBAL['monthArr'][7]='июл';
$_GLOBAL['monthArr'][8]='авг';
$_GLOBAL['monthArr'][9]='сен';
$_GLOBAL['monthArr'][10]='окт';
$_GLOBAL['monthArr'][11]='ноя';
$_GLOBAL['monthArr'][12]='дек';


$_GLOBAL['imgSize']['preview']['width']     = 60;
$_GLOBAL['imgSize']['preview']['height']    = 60;
$_GLOBAL['imgSize']['full']['width']        = 400;
$_GLOBAL['imgSize']['full']['height']       = 400;

?>

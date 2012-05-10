<?php

// print_r
// file_get_contents

/*
adminMail           = "superseo@yandex.ru"
siteName            = "DirectManager.ru"

useTpl              = "index.html"
useAdmTpl           = "adm.html"
useLoginTpl         = "login.html"
preTpl              = "dm/"
preAdmTpl           = "system/"

defaultModule       = "Page"
defaultAdmModule    = "YandexDirectJSON"

ImagrMagicPath      = "/usr/bin/"
onPage              = "20"

; Logs data
logMaxLines         = 500

; Logs data
repair24Init        = true
repairDate          = 0


 */

define ('__START_MT', getmicrotime());

$_MODULES['System']['access']['adm']['der']             = false;
$_MODULES['System']['access']['adm']['name']            = 'Доступ в панель управления';

require_once(__DOCUMENT_ROOT."vars.php");

class Core{
    function __construct($db_connect = false, $cron = false){
        global $_SERVER;
        global $_REQUEST;
        global $_GET;
        global $_POST;
        global $_SESSION;

        global $Core;

        global $_MODULES_MR;

        //Load system vars from file
        $this->iniFileName  = 'systemVars.ini';
        $this->logFileName  = 'sysLog.log';

        if (!empty($_REQUEST["PHPSESSID"])) $this->PHPSESSID = $_REQUEST["PHPSESSID"];
        
        $this->systemVars   = parse_ini_file(__DOCUMENT_ROOT.__PRE_INI.$this->iniFileName);

        $this->debuggingStatus    = false;
        $this->debugging_inf      = '';
        $this->debuggingInfArr    = Array();

        $Core->CURLTimeUsage      = 0;
        $Core->MYSQLTimeUsage      = 0;

        $this->getAction        = false;
        $this->getObj           = false;
        $this->getDerection     = false;
        
        $this->cron             = false;
        $this->adm              = false;
        $this->sitemap          = false;
        $this->robots           = false;
        $this->systemStatus     = false;
        $this->systemRepair     = false;

        //Ошибка доступа
        $this->loginError       = false;

        if (!empty($_GET['der1']) AND $_GET['der1']=='cron')                    $this->cron         = true;
        else if ($cron)                                                         $this->cron         = true;//Derect load cron
        else if (!empty($_GET['der1']) AND $_GET['der1']=='robots')             $this->robots       = true;
        else if (!empty($_GET['der1']) AND $_GET['der1']=='sitemap')            $this->sitemap      = true;
        else if (!empty($_GET['der1']) AND $_GET['der1']=='systemStatus')       $this->systemStatus = true;
        else if (!empty($_GET['der1']) AND $_GET['der1']=='systemRepair')       $this->systemRepair = true;
        else if (!empty($_GET['der1']) AND $_GET['der1']=='rotareLogs')         $this->rotareLogs = true;

        // AJAX ----------------------------------------------------------------
        if (isset($_GET['ajax']) OR isset($_POST['ajax'])) $this->ajax = true;
        else $this->ajax = false;
        // /AJAX ---------------------------------------------------------------
        
        //Standart HTML mode
        if ($this->robots OR $this->cron OR $this->sitemap OR $this->systemStatus OR $this->systemRepair)
            $this->frontHTML = false;
        else $this->frontHTML = true;

        //Debugging HTML mode
        if ($this->systemStatus OR $this->ajax) $this->debuggingStatus = false;

        if (isset($_GET['debug'])) $this->debuggingStatus = true;

        // Массив переменных состояния системы
        $this->systemStatusData = array();
        
        if (!empty($_GET['der1']) AND $_GET['der1']=='adm') {
            $this->adm = true;
            if (!empty($_GET['der2']))  $this->getAction        = $_GET['der2'];
            if (!empty($_GET['der3']))  $this->getObj           = $_GET['der3'];
            if (!empty($_GET['der4']))  $this->getDerection     = $_GET['der4'];
        } else {
            if (!empty($_GET['der1']))  $this->getAction        = $_GET['der1'];
            if (!empty($_GET['der2']))  $this->getObj           = $_GET['der2'];
            if (!empty($_GET['der3']))  $this->getDerection     = $_GET['der3'];
        };
        
        $this->user['u_id'] = 0;

        if ($this->ajax)    $this->cashStatus = false;
        if ($this->adm)     $this->cashStatus = false;

        $this->total_sql_queris_num = 0;
        $this->total_sql_queris     = '';

        $this->URL              = 'http://'.$_SERVER["SERVER_NAME"].'/';
        $this->domain           = str_replace('www.', '', $_SERVER["SERVER_NAME"]);
        
        $this->title            = '';
        $this->H1               = '';
        $this->content          = '';
        $this->error            = '';
        $this->message          = '';
        $this->css              = '';
        $this->js               = '';
        $this->contentTpl       = '';
        $this->navigation       = '';
        $this->menu             = array(); // url, name, level
        $this->setTplPath       = ''; //Set special tpl path
        $this->moduleTpl        = ''; // Переназначем базовый шаблон
        
        $this->date             = __DATE;
        
        //Path to system path
        $this->sysTplPath       = 'templates/'.$this->systemVars['preAdmTpl'];
        $this->tplPath          = 'templates/'.$this->systemVars['preTpl'];

        //Admin left menu
        //$this->HTMLLeftMenu     = '';
        
        $this->moduleData       = Array();

        if ($this->adm) $this->tpl = $this->systemVars['useAdmTpl'];
        else            $this->tpl = $this->systemVars['useTpl'];

        $this->URI = '';

        if (!empty($this->adm))             $this->URI .= 'adm/';
        if (!empty($this->getAction))       $this->URI .= $this->getAction.'/';
        if (!empty($this->getObj))          $this->URI .= $this->getObj.'/';
        if (!empty($this->getDerection))    $this->URI .= $this->getDerection.'/';

        $this->PHP_SELF = $this->URL.$this->URI;

        if (empty($this->getAction)) {
            $this->index = true;

            if ($this->adm)     $this->getAction = $this->systemVars['defaultAdmAction'];
            else {
                if (!empty($this->systemVars['defaultAction']))     $this->getAction = $this->systemVars['defaultAction'];
                if (!empty($this->systemVars['defaultObj']))        $this->getObj       = $this->systemVars['defaultObj'];
                if (!empty($this->systemVars['defaultDerection']))  $this->getDerection = $this->systemVars['defaultDerection'];
            };
        };

        if (isset($_MODULES_MR[$this->getAction])) $this->action = $_MODULES_MR[$this->getAction];
        else $this->action = $this->getAction;
        
        $this->cashFileName     =__DOCUMENT_ROOT.'cash/'.$this->getAction.'_'.$this->getObj.'_'.$this->getDerection.'.html';
        
        //Cash data
        if (defined("__CASH") == true)  $this->cashStatus = __CASH;
        else                            $this->cashStatus = false;

        if (isset($_GET['page']))       $this->page = intval($_GET['page']);
        else                            $this->page = 1;
        
        if ($this->page < 1) $this->page = 1; // Страница

        $this->pageNum  = 1;    // Количество страниц
        // На странице
        if (!empty($this->systemVars['onPage']))    $this->onPage = $this->systemVars['onPage'];
        else                                        $this->onPage = 10;

        if (isset($_GET['onPage'])) {
            $_GET['onPage']     = intval($_GET['onPage']);
            $_SESSION['onPage'] = $_GET['onPage'];
        };

        if (!empty($_SESSION['onPage'])) $this->onPage = $_SESSION['onPage'];
        
        
        return true;
    }

    /*
    function setSysVar($key, $value) {
        $this->DBSystemVars[$key]=$value;

        reset($this->DBSystemVars);

        $content='';

        while ($e=each($this->DBSystemVars)) $content.=''.$e['key'].' = '.$e['value'].'
';

        $fp = fopen(__DOCUMENT_ROOT.'vars.ini', "w");
        fwrite($fp, $content);
        fclose($fp);

        return true;
    }
     *
     */

    function saveIniFile($file, $arr) {
        reset($arr);

        $content = '';
        
        while($e=each($arr)) $content .= $this->iniStr($e['key'], $e['value']);

        //echo $content.'<br><br><br>';

        if (defined("__PRE_INI")==true) $dir = __PRE_INI;
        else $dir = 'ini/';
        
        $this->createDirPath($dir.$file);
        
        $dir = __DOCUMENT_ROOT.$dir;
        
        $fp = fopen($dir.$file, "w");
        fwrite($fp, $content);
        fclose($fp);
    }


    function login(){
        global $_GET;
        global $_POST;
        global $_SESSION;
        global $_COOKIE;

        //Exit
        if (isset($_GET['exit']) AND empty($_POST)){
            $_SESSION['login']          = false;
            $_SESSION['loginData']      = false;

            if (isset($_COOKIE['live_time'])){
                setcookie("login", '', $_COOKIE['live_time'], '/');
                setcookie("pass", '', $_COOKIE['live_time'], '/');
                setcookie("live_time", '', $_COOKIE['live_time'], '/');
            };

            session_destroy();
            $_COOKIE=false;

            $this->__construct();

            return true;
        }
        
        //No adm login
        /*
        if (!empty($_SESSION['loginData']) AND !$this->adm) {
            $this->user = $_SESSION['loginData'];
            return true;
        }
        */

        if (isset($_POST["login"]) && isset($_POST["pass"])){
            $login	=$_POST["login"];
            $pass	=$_POST["pass"];
        } else if(!empty($_SESSION['login'])){
            $login	=$this->uncript($_SESSION['login']);
            $login_arr=explode('___', $login);
            if (count($login_arr)!=2) {
                $this->error('Неверный код сесии');
                return true;
            }

            $login      =$login_arr[0];
            $pass_md5   =$login_arr[1];
        } else if(isset($_COOKIE["login"]) && isset($_COOKIE["pass"])){
            $login		= $this->uncript($_COOKIE["login"]);
            $pass_md5		= $this->uncript($_COOKIE["pass"]);

            // echo $_COOKIE["login"].' - '.$_COOKIE["pass"].'<br>';
            // echo $login.' - '.$pass_md5.'<br>';

            $live_time          =$_COOKIE["live_time"];
        } else return true;

        //If isset POST data
        if (isset($login) AND empty($login)){
            $this->loginError('Логин должен быть заполнен.');
            return false;
        }

        //If isset POST data
        if (isset($pass) AND empty($pass) AND empty($pass_md5)){
            $this->loginError('Пароль должен быть заполнен.');
            return false;
        }

        if (empty($login) OR (empty($pass) AND empty($pass_md5))) return false;

        $login = optimizeForDb($login);
        if (!empty($pass)) {
            $pass_md5 = md5(optimizeForDb($pass));
            $truePass   = $pass;
        }

        $res=getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users WHERE u_mail = \''.$login.'\' AND u_pass = ( \''.$pass_md5.'\' ) LIMIT 0 , 1', 'Core->login');

        $pass       = $pass_md5;

        //Login error
        if (!$row=mysql_fetch_array($res)){
            if (isset($_COOKIE['live_time'])){
                setcookie("login", '', $_COOKIE['live_time'], '/');
                setcookie("pass", '', $_COOKIE['live_time'], '/');
                setcookie("live_time", '', $_COOKIE['live_time'], '/');
            };

@           session_unregister("login");

            $str = date('H:i:s d.m.y', __DATE).' REMOTE_ADDR = '.$_SERVER["REMOTE_ADDR"].' $login = '.$login.' $pass = '.$pass_md5.'
';
            // Log
            $this->saveLogFile('SystemLoginError.log', $str);
            
            $this->loginError('Неверный логин и пароль.');
            
            return false;
        };
        
        getSqlResult('UPDATE '.__MYSQL_PRE.'users SET u_lastvisitDate = \''.__DATE.'\' WHERE u_id = \''.$row['u_id'].'\'', 'Core->login');

        $this->user = $row;
        if (!empty($row['u_data'])) $this->user['data'] = json_decode($row['u_data']);
        
        if (!empty($_POST['remember'])) {
            // echo 'Init REMEMBER';

            $cookie_login = $this->cript($login);
            $cookie_pass  = $this->cript($pass);

            setcookie("login", $cookie_login, time()+31536000, '/');
            setcookie("pass", $cookie_pass, time()+31536000, '/');
            setcookie("live_time", time()+31536000, time()+31536000, '/');
        };

        // echo $_COOKIE['login'].' ! '.$_COOKIE['pass'].' ! '.$_COOKIE['live_time'];

        $sLogin=$this->cript($login.'___'.$pass);

        $_SESSION['login'] = $sLogin;

        $this->loadAccess();

        if ($this->user['u_id'] == 1) $this->debuggingStatus = true;

        if ($this->user['u_id'] == 1 OR !empty($this->user['access']['User']['logAsUser'])) {
            if (isset($_GET['loginAsUser'])) {
                $_SESSION['loginAsUser'] = $_GET['loginAsUser'];
            };
            
            if (isset($_GET['stopLoginAsUser']) AND isset($_SESSION['loginAsUser'])) unset($_SESSION['loginAsUser']);

            if (!empty($_SESSION['loginAsUser'])) {
                $res=getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users WHERE u_id = \''.optimizeForDb($_SESSION['loginAsUser']).'\' LIMIT 0 , 1', 'Core->login');
                
                //Login error
                if ($row=mysql_fetch_array($res)){
                    $this->user = $row;
                    if (!empty($row['u_data'])) $this->user['data'] = json_decode($row['u_data']);

                    $this->loadAccess();
                    
                    if ($this->adm AND empty($this->user['access']['System']['adm'])) {
                        Header( "HTTP/1.1 301 Moved Permanently" );
                        Header( 'Location: '.$this->URL.'YandexDirectJSONCompanys/' );
                    };
                };
            };
        };
        
        $this->loadUserSystemVars();
        
        return true;
    }

    function loadAccess() {
        unset($this->user['access']);
        
        // Load access
        if (!empty($this->user['u_id'])) {
            $res=getSqlResult('SELECT * FROM user_access WHERE ua_u_id='.$this->user['u_id'].'', '$Core->checkAccess(): load');
            $num=mysql_num_rows($res);

            for($i=0;$i<$num;$i++){
                $row=mysql_fetch_array($res);

                $this->user['access'][$row['ua_module']][$row['ua_access']] = true;
            };
        };

        return true;
    }
    
    function saveUserSystemVars() {
        $this->saveIniFile($this->userIniFile, $this->userSystemVars);
        
        return true;
    }
    
    function loadUserSystemVars() {
        global $_GET;
        
        $this->userSystemVars = array();
        
        // User ini File
        $this->userIniFile = 'User/'.$this->user['u_id'].'.ini';
        
        if (is_file(__DOCUMENT_ROOT.__PRE_INI.$this->userIniFile)) $this->userSystemVars = parse_ini_file(__DOCUMENT_ROOT.__PRE_INI.$this->userIniFile);
        
        return true;
    }
    
    function iniStr($key, $value) {
        /*
        if (is_int($value))    echo $key.' = '.$value.' = int!!!<br>';
        if (is_string($value)) echo $key.' = '.$value.' = str!!!<br>';
        if (is_bool($value))   echo $key.' = '.$value.' = boo!!!<br>';
        
        if ($value == true) {
            if ($value == true)         $value = 'true';
            else                        $value = 'false';
        } else                          $value = '"'.$value.'"';
        */
        $value = str_replace('"', '&quot;', $value);
        return $key.' = "'.$value.'"
';
    }

    function saveLogFile($file, $str) {
        $str = trim($str);

        $fp = fopen(__DOCUMENT_ROOT.__PRE_LOG.$file, 'a+'); // Открываем файл в режиме записи
        $test = fwrite($fp, $str.'
'); // Запись в файл
        fclose($fp); //Закрытие файла

@       chmod (__DOCUMENT_ROOT.__PRE_LOG.$file, 0777);

        return true;
    }

    function handler(){
        global $_MODULES;
        global $_GET;

        if (defined('__301_MODULE')==true) {
            require_once(__CORE_DOCUMENT_ROOT.'Core/modules/'.__301_MODULE.'/'.__301_MODULE.'.php');
        };

        //Autorization
        $this->login();

        if (empty($_GET['reload'])) $rel = 20;
        else $rel = $_GET['reload'];

        if (isset($_GET['reload'])) echo '<header><meta http-equiv="REFRESH" content="'.$rel.'" /></header>
            <body><code>';
        
        if (!empty($this->cron))            $this->cron();
        if (!empty($this->sitemap))         $this->sitemap();
        if (!empty($this->robots))          $this->robots();
        if (!empty($this->systemStatus))    $this->systemStatus();
        if (!empty($this->systemRepair))    $this->systemRepair();
        if (!empty($this->rotareLogs))      $this->rotareLogs();
        
        if (!$this->checkAccess()) return true;
                
        //Cash
        if ($this->cashStatus) {
            if ($this->loadCash()) return true;
        };

        //No load module
        if (!empty($this->action) AND empty($_MODULES[$this->action])) $this->error404();
        
        //Start modules
        reset($_MODULES);
        //Autoloader loading modules
        While ($e=each($_MODULES)){
            if (!empty($_MODULES[$e['key']]['autoloader']) AND $this->action != $e['key']) $this->loadModule($e['key']);
        };
        
        //Derect loading modules
        if (is_file(__CORE_DOCUMENT_ROOT.'Core/modules/'.$this->action.'/'.$this->action.'.php')) $this->loadModule($this->action);
        
        $this->renderHTML();
        
        //Close MYSQL
        mysql_close();
        
        return true;
    }

    function loadModule($module) {
        $startMt = getmicrotime();
        
        if (is_file(__CORE_DOCUMENT_ROOT.'Core/modules/'.$module.'/'.$module.'.php')) {
            $this->debuggingInf('Start load module "'.$module.'"');
            require_once(__CORE_DOCUMENT_ROOT.'Core/modules/'.$module.'/'.$module.'.php');
            $this->debuggingInf('End load module "'.$module.'" - '.sprintf ("%01.4f", (getmicrotime()-$startMt)).' sec.');
        } else $this->debuggingInf('Load module "'.$module.'" false');

        return true;
    }
    
    // -------------------------------------------------------------------------
    // Cron
    // -------------------------------------------------------------------------
    function cron() {
        set_time_limit(600); 

        $this->cronId = $this->makePassword(5);

        global $_MODULES;
        // Start modules

        $this->debuggingInf('CRON INIT '.date('H:i:s d.m.Y').'');

        echo '<style>.debugArr{font-family: monospace}</style>';

        if (is_file(__CORE_DOCUMENT_ROOT.'Core/modules/'.$this->action.'/'.$this->action.'.php')) $this->loadModule($this->action);
        
        // Check for system repair ---------------------------------------------
        if (!empty($this->systemVars['repair24Init'])) {
            if (date('d.m.Y', $this->systemVars['repairDate']) != date('d.m.Y', __DATE) AND date('G', __DATE) >= 3) {
                $this->cron             = false;
                $this->systemRepair     = true;
                $this->systemRepair();
            };
        };
        // END check for system repair -----------------------------------------

        reset($_MODULES);
        While ($e=each($_MODULES)){
            $modul=$e['key'];

            //Autoloader and derect loading modules
            if (!empty($_MODULES[$modul]['cron']) AND $this->action != $modul) $this->loadModule($modul);
        };

        $this->debuggingInf('CRON END');
        
        echo $this->HTMLDebuging();
        
        exit;
    }

    function sitemap() {
        global $_MODULES;
        //Start modules

        reset($_MODULES);
        While ($e=each($_MODULES)){
            $modul=$e['key'];
            if (is_file(__CORE_DOCUMENT_ROOT.'Core/modules/'.$modul.'/'.$modul.'.php')) require_once(__CORE_DOCUMENT_ROOT.'Core/modules/'.$modul.'/'.$modul.'.php');
        };

        echo '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        '.$this->content.'
        </urlset>';

        echo $this->HTMLDebuging();

        exit;
    }

    function robots() {
         global $_MODULES;

        reset($_MODULES);
        While ($e=each($_MODULES)){
            $modul=$e['key'];
            if (is_file(__CORE_DOCUMENT_ROOT.'Core/modules/'.$modul.'/'.$modul.'.php')) require_once(__CORE_DOCUMENT_ROOT.'Core/modules/'.$modul.'/'.$modul.'.php');
        };

        $domain = str_replace('http://', '', $this->URL);
        $domain = str_replace('www.', '', $domain);

        echo 'User-Agent: *
Host: '.$this->systemVars['siteURL'].'
'.$this->content.'';

        echo $this->HTMLDebuging();

        exit;
    }

    function addSystemStatus($module, $status, $level, $sub, $msg) {

        $this->systemStatusData[] = array('module' => $module, 'status' => $status, 'level' => $level, 'sub' => $sub, 'msg' => $msg);

        //systemStatusData - статус системы - отдает JSON массив статуса модулей. Все что отлично от OK - ошибка
        //$this->systemStatusData['Core']['status'] = 'OK';
        /*
        $Core->systemStatusData[module]['status']  = 'Error';  // OK, Error
        $Core->systemStatusData[module]['level']   = 'normal'; // normal, warning, crit
        $Core->systemStatusData['Cron2']['sub']
        $Core->systemStatusData[module]['msg']
        */
        return true;
    }

    function systemStatus() {
        global $_MODULES;

        global $_GET;
        //Start modules
        
        reset($_MODULES);
        While ($e = each($_MODULES)){
            $module = $e['key'];
            
            if ($this->action != $module) $this->loadModule($module);
        };

        $this->loadModule($this->action);
        
        // Repair status
        if ($this->systemVars['repairDate'] < (__DATE - 3*24*3600) )
                $this->addSystemStatus('Core', 'Error', 'normal', 'Repair '.$this->systemVars['siteName'].' inactive', 'Repair inactive from '.date('d.m.Y H:i:s', $this->systemVars['repairDate']).'');
        else    $this->addSystemStatus('Core', 'OK', '', '', '');

        $systemStatus = $this->systemStatusData;
        
        echo json_encode($systemStatus);

        if (isset($_GET['reload'])) echo $this->HTMLDebuging();

        exit;
    }

    function systemRepair() {
        global $_MODULES;
        global $_SERVER;
        //Start modules

        $this->debuggingInf('SystemRepair -> init');

        // Save ini info
        // Обработка может быть много много минут - за это время еще пару кронов могут подхватить 
        $this->systemVars['repairDate'] = __DATE;
        $this->saveIniFile($this->iniFileName, $this->systemVars);

        reset($_MODULES);
        While ($e = each($_MODULES)){
            $module = $e['key'];

            $this->loadModule($module);
        };
        
        //print_r($this);

        // Reset logs
        // $this->resetLogsFiles();
        
        //Save log
        $str = date('H:i:s d.m.y', __DATE).' Runtime: '.sprintf ("%01.4f", (getmicrotime()-__START_MT)).' sec. REMOTE_ADDR='.$_SERVER["REMOTE_ADDR"].'
';
        $this->saveLogFile('systemRepair.log', $str);
        
        echo $this->HTMLDebuging();
        
        exit;
    }

    function rotareLogs() {
        global $_MODULES;
        global $_SERVER;
        //Start modules

        $this->debuggingInf('rotareLogs -> init');

        $this->resetLogsFiles();
        
        echo $this->HTMLDebuging();

        exit;
    }

    function resetLogsFiles() {
        global $Core;
        global $__LOG_CONFIG;

        if (is_file(__DOCUMENT_ROOT.__PRE_LOG.'logsVars.php')) require_once(__DOCUMENT_ROOT.__PRE_LOG.'logsVars.php');

        $handle = opendir (__DOCUMENT_ROOT.__PRE_LOG);
        while($file = readdir($handle)) {
            if ($file == '.' OR $file == '..') {}
            else if (is_file(__DOCUMENT_ROOT.__PRE_LOG.$file)) $this->resetLogsFile(__DOCUMENT_ROOT.__PRE_LOG.$file);
            else if (is_dir(__DOCUMENT_ROOT.__PRE_LOG.$file)){
                $handleIns = opendir (__DOCUMENT_ROOT.__PRE_LOG.$file);
                while($fileIns = readdir($handleIns)) {
                    if (is_file(__DOCUMENT_ROOT.__PRE_LOG.$file.'/'.$fileIns)) $this->resetLogsFile(__DOCUMENT_ROOT.__PRE_LOG.$file.'/'.$fileIns);
                }
            };
        };
    }

    function resetLogsFile($file) {
        global $Core;
        global $__LOG_CONFIG;

        $startMt = getmicrotime();

        // echo $file.'<br>';
        return true;

        $fileArr = file(__DOCUMENT_ROOT.__PRE_LOG.$file);

        $c = count($fileArr);

        for ($i=0; $i < $c - $this->systemVars['logMaxLines']; $i++) {
            unset ($fileArr[$i]);
        };

        $str = '';
        reset($fileArr);

        while($e = each($fileArr)) $str .= $e['value'];

        $fp = fopen(__DOCUMENT_ROOT.__PRE_LOG.$file, 'w+'); // Открываем файл в режиме записи
        $test = fwrite($fp, $str);                          // Запись в файл
        fclose($fp);                                        //Закрытие файла

        $Core->debuggingInf( 'SysLogRotation :: file "'.$file.'", lines '.$c.' -> '.count($fileArr).', time '.sprintf ("%01.4f", (getmicrotime()-$startMt)).' sec.');

        return true;
    }
    
    function error($error){
        if (!$this->frontHTML) echo '<div style="color:red">'.$error.'</div>';
        else $this->error .= $error;
        
        //echo '!!!!!!! ERROR'.$error.'<br>';

        return true;
    }

    function debuggingInf($msg) {
        if (!$this->debuggingStatus)    return true;
        if ($this->ajax)                return true;
        if ($this->systemStatus)        return true;

        if (empty($this->prewDebugGetmicrotime)) $this->prewDebugGetmicrotime = getmicrotime();

        $ramStr = round((memory_get_usage()/1048576), 2);
        // if (strlen($ramStr) == 3) $ramStr .= '0';
        // if (strlen($ramStr) == 1) $ramStr .= '.00';

        for ($i = strlen($ramStr); $i < 5; $i++) {
            $ramStr .= '&nbsp;';
        };

        $RAM = 'Debug: '.$ramStr.' Mb, '.date('H:i:s').'('.sprintf ("%01.1f", (getmicrotime()-$this->prewDebugGetmicrotime)).') ';

        if (!$this->frontHTML) echo '<div class="debugArr">'.$RAM.''.$msg.'</div>';
        else $this->debuggingInfArr[] = $RAM.$msg;

        $this->prewDebugGetmicrotime = getmicrotime();

        return true;
    }

    function message($message){
        $this->message .= '<p style="color: red">'.$message.'</p>';

        return true;
    }
    
    
    function loadCash() {
        global $_GET;
        
        if (!is_file($this->cashFileName)) return false;

        if (empty($this->DBSystemVars['dataUpdate']) OR isset($_GET['upCash'])) {
            $this->DBSystemVars['dataUpdate'] = date('U');
            $this->saveIniFile('vars.ini', $this->DBSystemVars);
        }

        if (filemtime($this->cashFileName) < $this->DBSystemVars['dataUpdate']) return false;

        $this->debugging_inf .= 'Load from cash<br>';
        
        echo file_get_contents($this->cashFileName);
        echo $this->HTMLDebuging();

        return true;
    }

    function saveCash($echo) {
        $fp = fopen($this->cashFileName, "w");
        fwrite($fp, $echo);
        fclose($fp);

        return true;
    }
    
    function error404() {
        header("HTTP/1.0 404 Not Found");
        
        $this->cashStatus   = false;
        $this->setTplPath   = $this->systemVars['preTpl'];
        // $this->setTplPath   = $this->systemVars['404'];
        
        $this->H1       = 'ERROR 404';
        $this->title    = 'ERROR 404';
        
        $this->content  = '<p>К сожалению эта страница не существует или была удалена.</P>';
        
        $this->renderHTML();
        
        exit;
    }
    
    function loginError($error = '') {
        if (!empty($error)) $this->error($error);

        $this->loginError = true;

        if (!empty($this->systemVars['useLoginModule'])) {
            $this->loadModule($this->systemVars['useLoginModule']);
        } else {
            $this->tpl          = $this->systemVars['useLoginTpl'];
            $this->setTplPath   = $this->systemVars['preTpl'];
        };
        
        $this->renderHTML();
        
        exit;
    }
    
    function checkAccess(){
        global $_MODULES;
        global $_SERVER;
        
        //ROOT
        if ($this->user['u_id'] == 1) return true;
        
        if ($this->adm) {
            if (!empty($this->user['u_id'])) {
                if (!empty($_MODULES['System']['access']['adm']['der']) //Check acess to ADM
                        OR !empty($this->user['access']['System']['adm'])) {
                //Need login
                    if (empty($this->getAction))                                       return true;
                    if (!empty($_MODULES[$this->action]['access']['use']['der'])    //Open for everybody
                        OR !empty($this->user['access'][$this->action]['use']))     return true;
                };
            };
        } else return true;

        $this->error('Доступ к странице http://'.$_SERVER["SERVER_NAME"].''.$_SERVER["REQUEST_URI"].' ограничен. Необходимо авторизоваться под именем пользователя имеющего доступ.');
        $this->loginError();
        
        $this->renderHTML();
        
        return false;
    }

    function renderHTML(){
        GLOBAL $_MODULES;
        GLOBAL $_GET;
        GLOBAL $_POST;
        GLOBAL $_SESSION;
        
        $echo='';
        
        $startMt = getmicrotime();

        if (!$this->ajax) {
            require_once __CORE_DOCUMENT_ROOT.'Twig/Autoloader.php';
            Twig_Autoloader::register();

            if ($this->moduleTpl) $tplPath = $this->moduleTpl;
            else {
                if (!empty($this->setTplPath))  $Filesystem = $this->setTplPath;
                else if ($this->adm)            $Filesystem = $this->systemVars['preAdmTpl'];
                else                            $Filesystem = $this->systemVars['preTpl'];
                
                $tplPath = $Filesystem.$this->tpl;
            };
            
            $loader = new Twig_Loader_Filesystem(__DOCUMENT_ROOT.'templates/');
            $twig = new Twig_Environment($loader, array(  'cache' => __DOCUMENT_ROOT.'templates/compilation_cache/', 'debug' => true, ));
            require_once __CORE_DOCUMENT_ROOT.'Twig/JDExtension.php';
            $twig->addExtension(new Project_Twig_Extension());

            $template = $twig->loadTemplate($tplPath);
            
            $this->content = $template->render(array('Core' => $this, '_POST' => $_POST, '_GET' => $_GET, '_SESSION' => $_SESSION , '_MODULES' => $_MODULES ));

            $this->debugging_inf .= 'Render '.sprintf ("%01.4f", (getmicrotime()-$startMt)).' sec.<br>';
        } else { //Ajax contentTpl
            $this->content .= strip_tags($this->error);
            
            if (!empty($this->contentTpl) OR !empty($this->moduleTpl)) {
                require_once __CORE_DOCUMENT_ROOT.'Twig/Autoloader.php';
                Twig_Autoloader::register();

                $loader = new Twig_Loader_Filesystem(__DOCUMENT_ROOT.'templates/');
                $twig = new Twig_Environment($loader, array(  'cache' => __DOCUMENT_ROOT.'templates/compilation_cache/', 'debug' => true, ));
                require_once __CORE_DOCUMENT_ROOT.'Twig/JDExtension.php';
                $twig->addExtension(new Project_Twig_Extension());

                if ($this->moduleTpl) $tplPath = $this->moduleTpl;
                else                  $tplPath = $this->contentTpl;

                $template = $twig->loadTemplate($tplPath);

                $this->content = $template->render(array('Core' => $this, '_POST' => $_POST, '_GET' => $_GET, '_SESSION' => $_SESSION , '_MODULES' => $_MODULES));

                $this->debugging_inf .= 'Render '.sprintf ("%01.4f", (getmicrotime()-$startMt)).' sec.<br>';
            };
        }

        //Save cash
        if (!$this->adm AND !$this->ajax AND $this->cashStatus) $this->saveCash($this->content);

        //Debug
        $this->content .= $this->HTMLDebuging();

        echo $this->content;
        
        return true;
    }
    
    function HTMLDebuging(){
        global $_GET;

        if (!$this->debuggingStatus)                                return '';
        // if ($this->ajax)                                            return '';
        //if (empty($this->user['u_id']))       return '';
        //if ($this->user['u_id'] !=1 )         return '';

        $echo='';

        $echo .= '<style>
             .debuging td {border-bottom:1px solid gray};
            </style>
            <div class="debuging"><p>Time for script:
            '.sprintf ("%01.4f", (getmicrotime()-__START_MT)).' sec.
            RAM: '.round((memory_get_usage()/1048576), 2).' Mb.';
        if (!empty($this->CURLTimeUsage)) $echo .= ', CURL: '.$this->CURLTimeUsage.' sec.';
        if (!empty($this->MYSQLTimeUsage)) $echo .= ', MYSQL: '.$this->MYSQLTimeUsage.' sec.';
        
        $echo .= '<br>'.$this->debugging_inf;

        while($e = each($this->debuggingInfArr)) $echo .= $e['value'].'<br>';
        
        //Only Super Administrator can edit users
        //if (!empty($this->user['usertype']) AND $this->user['usertype']==1)
        if (!isset($_GET['debugNoSql'])) $echo .= '<table style="margin-top:10px">'.$this->total_sql_queris.'</table>';
        
        $echo .= '</div>';

        $this->debugging_inf    = '';
        $this->debuggingInfArr  = Array();
        
        return $echo;
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------
    //ENCRIPTER
    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------
    function loadEncipher($fileName = false){
        global $g_code_list;

        if (empty($fileName)) $fileName = 'encipher.php';
        
        if (!empty($this->code_list))return true;

        if (!is_file(__DOCUMENT_ROOT.'uploads/system/encipher/'.$fileName))$this->createEncipherFile();

        require_once(__DOCUMENT_ROOT.'uploads/system/encipher/'.$fileName);

        if (empty($g_code_list))$this->createEncipherFile($fileName);

        $this->code_list = $g_code_list;
        
        return true;
    }

    function cript($text){
        $this->loadEncipher();

        $new_text = '';

        // echo $text.'<br>';

        $len = strlen($text);
        for($i=0;$i<$len;$i++){
            $ancr = substr($text, $i, 1);
            if (isset($this->code_list['cript'][$ancr])) $new_text .= $this->code_list['cript'][$ancr];
            else $new_text .= $ancr;

            // echo $ancr.' - '.$this->code_list['cript'][$ancr].'<br>';
        };
        
        return $new_text;
    }

    function unCript($text){
        $this->loadEncipher();

        // reset($this->code_list['uncript']);

        /*
        echo $text;

        $newText = '';

        $strlen = strlen($text);
        for ($i = 0;$i < $strlen;$i = $i+3) {
            $code = substr($text, $i, 3);

            echo $code.' - '.$this->code_list['uncript'][$code].'<br>';

            $newText .= $this->code_list['uncript'][$code];
        };


        echo $newText;

        exit;
         * 
         */

        reset($this->code_list['uncript']);

        while($e=each($this->code_list['uncript'])){
            // substr($string, $start, $length);
            $text=str_replace($e['key'], $this->code_list['uncript'][$e['key']], $text);
        };

        return $text;
    }

    function createEncipherFile($fileName = false){
        if (empty($fileName)) $fileName = 'encipher.php';

        if (!is_dir(__DOCUMENT_ROOT.'uploads/system/'))             mkdir(__DOCUMENT_ROOT.'uploads/system/');
        if (!is_dir(__DOCUMENT_ROOT.'uploads/system/encipher/'))    mkdir(__DOCUMENT_ROOT.'uploads/system/encipher/');

        $file='<?
';

        $arr[]='0';
        $arr[]='1';
        $arr[]='2';
        $arr[]='3';
        $arr[]='4';
        $arr[]='5';
        $arr[]='6';
        $arr[]='7';
        $arr[]='8';
        $arr[]='9';
        $arr[]='a';
        $arr[]='u';
        $arr[]='b';
        $arr[]='s';
        $arr[]='d';
        $arr[]='e';
        $arr[]='f';
        $arr[]='g';
        $arr[]='h';
        $arr[]='i';
        $arr[]='k';
        $arr[]='l';
        $arr[]='m';
        $arr[]='n';
        $arr[]='o';
        $arr[]='p';
        $arr[]='q';
        $arr[]='r';
        $arr[]='s';
        $arr[]='t';
        $arr[]='v';
        $arr[]='x';
        $arr[]='y';
        $arr[]='z';
        $arr[]='A';
        $arr[]='U';
        $arr[]='B';
        $arr[]='C';
        $arr[]='D';
        $arr[]='E';
        $arr[]='F';
        $arr[]='G';
        $arr[]='H';
        $arr[]='I';
        $arr[]='K';
        $arr[]='L';
        $arr[]='M';
        $arr[]='N';
        $arr[]='O';
        $arr[]='P';
        $arr[]='Q';
        $arr[]='R';
        $arr[]='S';
        $arr[]='T';
        $arr[]='V';
        $arr[]='X';
        $arr[]='Y';
        $arr[]='Z';
        $arr[]='.';
        $arr[]='@';
        $arr[]='/';
        $arr[]='_';

        $c=count($arr);

        for($i=0;$i<$c;$i++){
            $code=$arr[mt_rand(0, $c-1)].$arr[mt_rand(0, $c-1)].$arr[mt_rand(0, $c-1)].$arr[mt_rand(0, $c-1)].$arr[mt_rand(0, $c-1)].$arr[mt_rand(0, $c-1)];
            $file.='$g_code_list[\'cript\'][\''.$arr[$i].'\']=\''.$code.'\';
';
            $file.='$g_code_list[\'uncript\'][\''.$code.'\']=\''.$arr[$i].'\';
';
        };


        $file.='?>';

        $fh = fopen(__DOCUMENT_ROOT.'uploads/system/encipher/'.$fileName, "w+");
        fwrite($fh, $file);
        fclose($fh);

        return true;
    }

    function makeImgTranslit($imgName){
        preg_match('/(.*)(\.[a-z]{2,4})$/i', $imgName, $matches);

        if (empty($matches[2])) return $this->make_translit($imgName);
        else return $this->make_translit($matches[1]).$matches[2];
    }

    function make_translit($st){
        // Сначала заменяем "односимвольные" фонемы.

        $st = trim($st);

        $st = strip_tags($st);

        $st=str_replace("а", "a", $st);
        $st=str_replace("б", "b", $st);
        $st=str_replace("в", "v", $st);
        $st=str_replace("г", "g", $st);
        $st=str_replace("д", "d", $st);
        $st=str_replace("е", "e", $st);
        $st=str_replace("ё", "e", $st);
        $st=str_replace("з", "z", $st);
        $st=str_replace("и", "i", $st);
        $st=str_replace("й", "j", $st);
        $st=str_replace("к", "k", $st);
        $st=str_replace("л", "l", $st);
        $st=str_replace("м", "m", $st);
        $st=str_replace("н", "n", $st);
        $st=str_replace("о", "o", $st);
        $st=str_replace("п", "p", $st);
        $st=str_replace("р", "r", $st);
        $st=str_replace("с", "s", $st);
        $st=str_replace("т", "t", $st);
        $st=str_replace("у", "u", $st);
        $st=str_replace("ф", "f", $st);
        $st=str_replace("х", "h", $st);
        $st=str_replace("ы", "y", $st);
        $st=str_replace("э", "e", $st);
        $st=str_replace("ж", "zh", $st);
        $st=str_replace("ц", "ts", $st);
        $st=str_replace("ч", "ch", $st);
        $st=str_replace("ш", "sh", $st);
        $st=str_replace("щ", "shch", $st);
        $st=str_replace("ь", "", $st);
        $st=str_replace("ю", "yu", $st);
        $st=str_replace("я", "ya", $st);

        $st=str_replace("А", "A", $st);
        $st=str_replace("Б", "B", $st);
        $st=str_replace("В", "V", $st);
        $st=str_replace("Г", "G", $st);
        $st=str_replace("Д", "D", $st);
        $st=str_replace("Е", "E", $st);
        $st=str_replace("Ё", "E", $st);
        $st=str_replace("З", "Z", $st);
        $st=str_replace("И", "I", $st);
        $st=str_replace("К", "K", $st);
        $st=str_replace("Л", "L", $st);
        $st=str_replace("М", "M", $st);
        $st=str_replace("Н", "N", $st);
        $st=str_replace("О", "O", $st);
        $st=str_replace("П", "P", $st);
        $st=str_replace("Р", "R", $st);
        $st=str_replace("С", "S", $st);
        $st=str_replace("Т", "T", $st);
        $st=str_replace("У", "U", $st);
        $st=str_replace("Ф", "F", $st);
        $st=str_replace("Х", "H", $st);
        $st=str_replace("Ы", "I", $st);
        $st=str_replace("Э", "E", $st);
        $st=str_replace("Ж", "SH", $st);
        $st=str_replace("Ц", "TS", $st);
        $st=str_replace("Ч", "CH", $st);
        $st=str_replace("Ш", "SH", $st);
        $st=str_replace("Щ", "SHCH", $st);
        $st=str_replace("Ю", "YU", $st);
        $st=str_replace("Я", "YA", $st);
        $st=str_replace("Ь", "", $st);
        $st=str_replace("Ъ", "", $st);

        $st=strtolower($st);

        $st=str_replace(',', '_', $st);
        $st=str_replace('-', '_', $st);
        $st=str_replace(' ', '_', $st);
        $st=str_replace('(', '_', $st);
        $st=str_replace(')', '_', $st);
        $st=str_replace('.', '_', $st);
        $st=str_replace(',', '_', $st);

        $st=str_replace('__', '_', $st);
        $st=str_replace('__', '_', $st);
        $st=str_replace('__', '_', $st);
        $st=str_replace('__', '_', $st);
        $st=str_replace('__', '_', $st);
        $st=str_replace('__', '_', $st);
        $st=str_replace('__', '_', $st);
        $st=str_replace('__', '_', $st);

        $st=str_replace('
', '', $st);

        $res='';

        for ($i=0;$i<strlen($st);$i++){
            $str=substr($st, $i, 1);
            if (preg_match('/^[a-z0-9_]?$/', $str))$res.=$str;
        };
        
        return $res;
    }

    function saveImgUrl($file, $path, $width = 0, $height = 0, $fileName='') {
        if (empty($this->action)) return false;
        
        if (empty($fileName)) {
            $fileName = explode('/', $file);
            $c = count($fileName);
            $fileName = $fileName[$c-1];
        }

        $path='uploads/'.$this->action.'/'.$path;

        $pathArr = explode('/', $path);
        
        $newPath = '';

        for ($i=0;$i<count($pathArr);$i++) {
            $newPath .= $pathArr[$i].'/';
            if (!is_dir($newPath)) {
                mkdir($newPath);
                $this->debugging_inf .= 'IMG: create directory '.$newPath.'<br>';
            }
        };

        $filePath=$path.$fileName;

        //Copy to sourse directory
        copy ($file, $filePath);
        $this->debugging_inf .= 'IMG: copy file '.$filePath.'<br>';

        $imageinfo = getimagesize ($filePath);

        if ((!empty($width) AND $imageinfo[0]>$width) OR (!empty($height) AND $imageinfo[1]>$height)) {
            if (__IMAGE_MAGICK_TYPE == 'class') {
                $im = new Imagick($filePath);
                //$im->trimImage(0);
                $im->scaleImage($width, $height, true);
                $im->writeImage($filePath);
                $im->destroy();
            } else if (__IMAGE_MAGICK_TYPE == 'system') {
                passthru(''.__IMAGE_MAGICK.'convert '.$filePath.'  -fuzz 2% -trim -quality 0 -compress None  -resize '.$width.'x'.$height.' 	'.$filePath, $res);
            };

            $this->debugging_inf .= 'IMG: resize file '.$filePath.'<br>';
        };

        return true;
    }

    function saveImg($file, $path, $fileName = '', $width = 0, $height = 0, $action = false, $quality = 0, $size = 0) {
        if (!is_file($file))        return false;
        if (empty($this->action))   return false;
        if (empty($fileName))       return false;
        
        $fileName = $this->makeImgTranslit($fileName);

        if (empty($action)) $action = $this->action;
        
        $this->createDirPath('uploads/'.$action.'/'.$path);
        
        $path=__DOCUMENT_ROOT.'uploads/'.$action.'/'.$path;
        
        $filePath = $path.$fileName;
        
        //Copy to sourse directory
        copy ($file, $filePath);
        $this->debuggingInf('IMG: copy file '.$filePath);
        
        $imageinfo = getimagesize ($filePath);

        if (__IMAGE_MAGICK_TYPE == 'class') {
            // Work whith GIF
            if (preg_match('/\.gif$/', $filePath)) {
                //большая анимашка big.gif
                $images = new Imagick($filePath);

                //вот этой фишки не хватает в примере из мануала на php.net
                $images = $images->coalesceImages();

                //и ресайзим каждый кадр в цикле
                if ((!empty($width) AND $imageinfo[0]>$width) OR (!empty($height) AND $imageinfo[1]>$height)) {
                    do {
                        // Вычисляем превышения
                        $widthOverdraft     = $width / $imageinfo[0];
                        $heightOverdraft    = $height / $imageinfo[1];

                        if ($widthOverdraft < 1 OR $heightOverdraft < 1) {
                            if ($widthOverdraft < $heightOverdraft) $factor = $widthOverdraft;
                            else $factor = $heightOverdraft;

                            $images->scaleImage($imageinfo[0] * $factor, $imageinfo[1] * $factor);
                        };
                    } while ($images->nextImage());
                };
                
                //оптимизируем слои
                $images->optimizeImageLayers();
                // $images->stripImage();

                //освобождаем память
                $images = $images->deconstructImages();

                //сохраняем анимацию в small.gif
                $images->writeImages($filePath, true);
            // Work whith JPEG, PNG
            } else {
                $im = new Imagick($filePath);
                //$im->trimImage(0);
                //$im = $im->coalesceImages();
                if (!empty($quality)) $im->setImageCompressionQuality($quality);
                if ((!empty($width) AND $imageinfo[0]>$width) OR (!empty($height) AND $imageinfo[1]>$height)) {
                    $im->scaleImage($width, $height, true);
                    $this->debuggingInf('IMG: resize file '.$filePath);
                };
                
                $im->trimImage(0);
                $im->stripImage();
                $im->writeImage($filePath);
                $im->destroy();

                if (!empty($size)) $this->doImgCompress($filePath, $size, 0);
            };
        } else if (__IMAGE_MAGICK_TYPE == 'system') {
            // Недоработано!!! ---------------------------------------------
            passthru(''.__IMAGE_MAGICK.'convert '.$filePath.' -coalesce -fuzz 2% -trim -quality '.$quality.'  -compress None  -resize '.$width.'x'.$height.' 	'.$filePath);
            // / Недоработано!!! -------------------------------------------
        };

        return true;
    }

    function doImgCompress($file, $size, $currentProcent) {
        // Прерывание
        if ($currentProcent >= 100) return true;

        // Создаем исходний
        if (strstr($file, '.jpg')) $tmpFile = str_replace('.jpg', '_TMP.jpg', $file);
        else if (strstr($file, '.jpeg')) $tmpFile = str_replace('.jpeg', '_TMP.jpeg', $file);
        else return true;

        copy ($file, $tmpFile);

        // Получаем размер файла
        $filesize = round(filesize($file)/1024);

        $fileDebugPath = str_replace(__DOCUMENT_ROOT, '', $file);

        // Компресс
        while ($filesize > $size AND $currentProcent >= 0) {
            if ($currentProcent == 0) $currentProcent = 90;
            $im = new Imagick($tmpFile);

            $im->setImageCompressionQuality($currentProcent);
            $im->stripImage();
            
            $im->writeImage($file);
            $im->destroy();
            
            clearstatcache();
            $newFilesize = round(filesize($file)/1024);

            $this->debuggingInf('IMG: compress('.$currentProcent.'%) file '.$fileDebugPath.' '.$filesize.'Kb -> '.$size.'Kb res '.$newFilesize.'Kb');

            $currentProcent -= 10;
            $filesize = $newFilesize;
        };

        // Проверка и если нать перезапуск

        unlink($tmpFile);

        return true;
    }

    function saveFile($file, $path, $fileName = '', $action = false) {
        if (!is_file($file))        return false;
        if (empty($this->action))   return false;
        if (empty($fileName))       return false;

        $fileName = $this->makeImgTranslit($fileName);

        if (empty($action)) $action = $this->action;

        $this->createDirPath('uploads/'.$action.'/'.$path);
        
        $path=__DOCUMENT_ROOT.'uploads/'.$action.'/'.$path;

        $filePath = $path.$fileName;

        //Copy to sourse directory
        copy ($file, $filePath);
        $this->debuggingInf('FILE: copy file '.$filePath);
        
        return true;
    }

    function createDirPath($dir) {
        $pathArr = explode('/', $dir);

        $newPath = '';

        for ($i=0;$i<count($pathArr)-1;$i++) {
            if (!empty($pathArr[$i])) {
                $newPath .= $pathArr[$i].'/';

                if (!is_dir($newPath)) {
                    mkdir($newPath);
                    $this->debuggingInf('CORE: create directory '.$newPath);
                };
            };
        };

        return true;
    }

    function makePassword($num) {
        $password = '';
        $accepted_chars = 'ABCDEFGHIJKLMNOPKUSTYWVXZabcdefghijklmnopqrstuvwxyzl234567890'; // Seed the generator if necessary. srand(((int)((double)microtime()*1000003))  );
        for  ($i=0; $i<$num; $i++)   {
            $random_number = rand(0, (strlen($accepted_chars)-1));
            $password .= $accepted_chars[$random_number];
        };

        return $password;
    }

    function sendMail($mail, $sub, $content) {
        if (strstr(__MYSQL_DB, 'test')) {
            $this->debuggingInf( 'MAIL: send mail to "'.$mail.'", sub: "'.$sub.'"' );
            return true;
        }
        
        if (empty($mail)) $mail = $this->systemVars['adminMail'];
        
        $content = 'Добрый день.<br><br>'.$content.'<br><br>С уважением служба технической поддержки <a href="'.$this->systemVars['siteURL'].'">'.$this->systemVars['siteName'].'</a>';

        $header = "Content-type:text/html ; charset = UTF-8\n"
						."from:".$this->systemVars['adminMail'];

        mail( $mail , $sub , $content , $header );
        if ($this->systemVars['adminMail'] != $mail) mail( $this->systemVars['adminMail'] , $sub , $content , $header );
        
        $this->debuggingInf( 'MAIL: send mail to "'.$mail.'", sub: "'.$sub.'"' );
        
        return true;
    }
}

//------------------------------------------------------------------------------
//FUNCTIONS
//------------------------------------------------------------------------------
function dbConnect(){
    global $_GLOBAL;
    global $Core;

    mysql_connect(__MYSQL_HOST, __MYSQL_USER, __MYSQL_PASS);
    mysql_select_db(__MYSQL_DB);

    $_GLOBAL['mysqlConnect'] = true;

    getSqlResult("SET NAMES utf8", 'SYSTEM');

    $Core->debugging_inf.='DB '.__MYSQL_DB.' connect<br>';

    return true;
};

function getSqlResult($q, $comm='Unknown'){
    global $_GLOBAL;
    global $_SESSION;
    global $Core;
    
    if (empty($_GLOBAL['mysqlConnect'])) dbConnect();

    $di["file"]='';
    $di["line"]='';
    list ($di) = debug_backtrace();
    $file=$di["file"];
    $line=$di["line"];

    $time=getmicrotime();

    // if (!empty($Core->cron)) echo $q.'<br>';

    if (isset($_GET['sqlDebug'])) {
        echo '<div>'.str_replace('<', '&lt;', $q).'<br>';
    };
    
    $res=mysql_query($q);
    
    if (!$res AND mysql_error()=='MySQL server has gone away'){
        dbConnect();
        $res = mysql_query($q);
    };
    
//    $num = mysql_num_rows($res);
//    if (empty($num)) $num= 0;
    
    // Лимит до которого наполняеться массив
    if ((!empty($Core->user['u_id']) AND $Core->user['u_id'] == 1) OR !empty($_SESSION['loginAsUser'])) {
        $Core->total_sql_queris_num++;
        $Core->total_sql_queris .= '<tr>
            <td>'.$Core->total_sql_queris_num.'. </td>
            <td>'.str_replace('<', '&lt;', $q).'</td>';
        $Core->total_sql_queris .= '<td>'.sprintf ("%01.6f", (getmicrotime()-$time)).'&nbsp;sec. </td>
            <td>'.round((memory_get_usage()/1048576), 2).'&nbsp;Mb.</td><td style="white-space: nowrap;">';
        // $Core->total_sql_queris .= ''.str_replace(__DOCUMENT_ROOT, '', $file).' ('.$line.')<br>';
        $Core->total_sql_queris .= ''.$comm.'
            </td>
        </tr>';
    } else {
        /*
        $RAM = 'Core debug: RAM: '.$ramStr.' Mb.: Time: '.sprintf ("%01.4f", (getmicrotime()-__START_MT)).' sec." ';
        echo '<div class="debugArr">'.$RAM.' '.sprintf ("%01.6f", (getmicrotime()-$time)).'&nbsp;sec. '.str_replace('<', '&lt;', $q).' </div>';
         * 
         */
    }
    
    if (isset($_GET['sqlDebug'])) {
        echo ''.sprintf ("%01.6f", (getmicrotime()-$time)).'&nbsp;sec. '.round((memory_get_usage()/1048576), 2).'&nbsp;Mb. '.$comm.'</div>';
    };
    /*
    if ((memory_get_usage()/1048576) > 100) $Core->sendMail(false, 'Out of memory', 'Query: '.str_replace('<', '&lt;', $q).' <BR><BR>
            Line:'.str_replace(__DOCUMENT_ROOT, '', $file).' ('.$line.') <BR><BR>
            Time: '.sprintf ("%01.6f", (getmicrotime()-$time)).'&nbsp;sec.  <BR><BR>
            RAM: '.round((memory_get_usage()/1048576), 2).'&nbsp;Mb. <BR><BR>
            Comm: '.$comm.'');
     * 
     */


    //echo $Core->total_sql_queris;

    if (!$res) systemError($q.'<br><br>'.mysql_error().'<br><br>Файл: '.$file.'<br><br>Строка: '.$line.';');

    $Core->MYSQLTimeUsage += sprintf ("%01.6f", (getmicrotime()-$time));
    
    return $res;
};

function getSqlResultArray ($q, $comm='Unknown', $idField = false) {
    $arr = array();

    $res = getSqlResult($q, $comm);

    $num = mysql_num_rows($res);
    
    for($i=0; $i<$num; $i++){
        $row = mysql_fetch_array($res);

        if (!empty($idField))   $id = $row[$idField];
        else                    $id = $i;

        while(list($key, $value) = each($row)) {
            if (!is_int($key)) $arr[$id][$key] = $value;
        };     
    };

    return $arr;
}

function clearRow($row) {
    $newRow = array();

    while(list($key, $value) = each($row)) {
        if (!is_int($key)) $newRow[$key] = $value;
    };

    return $newRow;
}

function getSqlLoad($id, $idName, $DB) {
    global $Core;

    if (empty($id))   return false;

    $res = getSqlResult('SELECT * FROM '.__MYSQL_PRE.$DB.' WHERE `'.optimizeForDb($idName).'`=\''.optimizeForDb($id).'\'', 'getSqlLoad: Load');
    
    if (!$row=mysql_fetch_array($res))  return false;
    
    return $row;
}

function getSqlSave($row, $newRow, $idName, $DB) {
    if (empty($newRow)) return false;

    reset($newRow);
    $i = 0;

    if (!empty($row[$idName])) { // Up
        $sql = '';

        while ($e = each($newRow)) {
            $key = $e['key'];

            if ( empty($row[$key]) OR $row[$key] != $newRow[$key] ) {
                if ($i != 0) $sql .= ', ';
                $sql .= '`'.optimizeForDb($e['key']).'`=\''.optimizeForDb($e['value']).'\'';
                $i++;
            };
        };
        
        if (!empty($sql)) getSqlResult('UPDATE '.__MYSQL_PRE.$DB.' SET '.$sql.' WHERE `'.optimizeForDb($idName).'` = \''.optimizeForDb($row[$idName]).'\'', 'getSqlSave: Save');
    } else { //Add
        $sql = '';
        $sqlValues = '';
        while ($e = each($newRow)) {
            if ($i != 0) $sql .= ', ';
            $sql .= '`'.optimizeForDb($e['key']).'`';

            if ($i != 0) $sqlValues .= ', ';
            $sqlValues .= '\''.optimizeForDb($e['value']).'\'';
            $i++;
        };
        
        getSqlResult('INSERT INTO '.__MYSQL_PRE.$DB.' ('.$sql.') VALUES ('.$sqlValues.')', 'getSqlSave: Save');

        $newRow[$idName] = mysql_insert_id();
    };

    return $newRow;
}


function systemError($error) {
    global $Core;
    
    $content = $error.$Core->HTMLDebuging();
    
    $Core->sendMail(false, $Core->URL.' systemError', $content);
    
    echo 'Возникла критическая ошибка. Вся информация отправлена администратору.<br>';
    // if (!$Core->debuggingStatus) exit;

    if ($Core->user['u_id'] == 1) echo $error.$Core->HTMLDebuging();

    exit;
}

function optimizeForDb($obgect){
    $obgect = trim($obgect);
    $obgect = addslashes($obgect);

    return $obgect;
}

function optimizeForIni($obgect){
    $obgect = trim($obgect);
    $obgect = str_replace('"', '&quot;', $obgect);

    return $obgect;
}

function optimizeForInput($obgect){
    $obgect = trim($obgect);
    $obgect = str_replace('"', '&quot;', $obgect);

    return $obgect;
}

function optimizeDomain($obgect){
    $obgect = trim($obgect);
    $obgect = str_replace('www.', '', $obgect);
    $obgect = str_replace('http://', '', $obgect);
    $obgect = str_replace('https//', '', $obgect);

    $obgect = explode('/', $obgect);

    $obgect = $obgect[0];

    return $obgect;
}

function getMicroTime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

function fullStrip($text){
    $text=strip_tags($text);
    $text=trim($text);

    return $text;
}

function adminMail($text) {
    mail(__ADMIN_MAIL, 'Системное сообщение '.__SITE_URL.'', $text);
}

function setRightCharset($str){
    $charset = mb_detect_encoding($str, "windows-1251, utf-8");

    /*
    $charsetArr['utf-8']                = true;
    $charsetArr['UTF-8']                = true;
    $charsetArr['windows-1251']         = true;
    */
    
    if ($charset != 'utf-8' AND $charset != 'UTF-8') $str = iconv($charset, 'utf-8', $str);
    
    return $str;
}

function localNl2br($str) {
    $str = nl2br($str);
    $str = preg_replace('|<br \/>(\s)*<br \/>|', '<p>', $str);

    return $str;
}

function printData($data) {
    echo '<br><br>----------------------------------<pre>';
    print_r($data);
    echo '</pre>----------------------------------<br><br>';

    return true;
}

function _strtolower($string)
{
    $small = array('а','б','в','г','д','е','ё','ж','з','и','й',
                   'к','л','м','н','о','п','р','с','т','у','ф',
                   'х','ч','ц','ш','щ','э','ю','я','ы','ъ','ь',
                   'э', 'ю', 'я');
    $large = array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й',
                   'К','Л','М','Н','О','П','Р','С','Т','У','Ф',
                   'Х','Ч','Ц','Ш','Щ','Э','Ю','Я','Ы','Ъ','Ь',
                   'Э', 'Ю', 'Я');
    return str_replace($large, $small, $string);
}

function _strtoupper($string)
{
    $small = array('а','б','в','г','д','е','ё','ж','з','и','й',
                   'к','л','м','н','о','п','р','с','т','у','ф',
                   'х','ч','ц','ш','щ','э','ю','я','ы','ъ','ь',
                   'э', 'ю', 'я');
    $large = array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й',
                   'К','Л','М','Н','О','П','Р','С','Т','У','Ф',
                   'Х','Ч','Ц','Ш','Щ','Э','Ю','Я','Ы','Ъ','Ь',
                   'Э', 'Ю', 'Я');
    return str_replace($small, $large, $string);
}

function postCurlPage($pageSpec, $data = '', $header = array(), $headers = false, $debugData = '') {
    global $Core;
    
    $time = getmicrotime();

    //Check for simple GET

    if (empty($data) AND empty($headers) AND empty($header)) {
@       $res = file_get_contents($pageSpec);
        
        $Core->debuggingInf('System function file_get_contents('.$pageSpec.') время выполнения '.sprintf ("%01.6f", (getmicrotime()-$time)).' sec.<br>');

        return $res;
    };

    // Запись в лог
    $logData = $substring = substr($data, 0, 100);
    // $Core->saveLogFile('postCurlPage.log', date('H:i:s d.m').' '.$pageSpec.' '.$logData.'');
    // /
    
    $pageSpec = "".$pageSpec;
    $agent = "Firefox";
    $header = array("Accept: text/vnd.wap.wml,*.*");
    
    if (!function_exists('curl_init')) {
        $Core->error('CURL off.');
        return false;
    }

    $ch = curl_init($pageSpec);
    //if ($GLOBALS['proxy']) curl_setopt($ch, CURLOPT_PROXY, $GLOBALS['proxy']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    //if ($FOLLOWLOCATION) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    if (!empty($data)){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    };
    if ($headers) curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "sschecker");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "sschecker");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    //curl_setopt($ch, CURLOPT_PROXY, $IP);

    $tmp = curl_exec ($ch);
    
    curl_close ($ch);

    if ($headers) {
        //charset=windows-1251" />
        if (preg_match('/charset=("|\'|)([a-zA-Z\-0-9]*)/i', $tmp, $matches)) $charset = trim($matches[2]);
        else $charset = 'utf-8';

        if ($charset != 'utf-8' AND $charset != 'UTF-8') $tmp = iconv($charset, 'utf-8', $tmp);
        
        //Strip headers
        $tmp = explode('<', $tmp, 2);

        if (isset($tmp[1]))$tmp = '<'.$tmp[1];
        else $tmp = $tmp[0];
    };

    $Core->debuggingInf('SYS time '.sprintf ("%01.6f", (getmicrotime()-$time)).' sec. Curl('.$pageSpec.') (str len '.  strlen($tmp).')');
    
    $Core->CURLTimeUsage += sprintf ("%01.6f", (getmicrotime()-$time));
    //echo $charset;

    //echo $pageSpec.'<br>';
    //systemError('Прерыватель');
    
    return $tmp;
};

//phpinfo();

/*
 * function docookie($setuid, $setusername, $setpass, $setstorynum, $setumode, $setuorder, $setthold, $setnoscore, $setublockon, $settheme, $setcommentmax, $rememberme=0) {
 global $CONFIG;

 echo 'TEST LINE 1368 <br>';

 if (defined('_FLAG_NOHASHPASS')) {
  $setpass=md5($setpass);
 }

 echo 'TEST LINE 1374 <br>';

 $info = base64_encode("$setuid:$setusername:$setpass:$setstorynum:$setumode:$setuorder:$setthold:$setnoscore:$setublockon:$settheme:$setcommentmax");
 if (!isset($CONFIG['cookie_user'])) {
  $CONFIG['cookie_user']=2592000;
 }

 echo 'TEST LINE 1381 <br>';

 $CONFIG['cookie_user']=intval($CONFIG['cookie_user']);
 $CONFIG['use_rememberme']=intval($CONFIG['use_rememberme']);
 if ($CONFIG['cookie_user']>0) {
  if ((isset($rememberme)) AND ($CONFIG['use_rememberme']==1) AND (!empty($rememberme))) {
   $cookie_user_time=$CONFIG['cookie_user']+time();
   setcookie("user", $info, $cookie_user_time);
   return;
  }
  else {
   setcookie("user", $info, 0);
   return;
  }
 }
 else {
  setcookie("user", $info, 0);
  return;
 }
}
 */

?>

<?

$User = new User;

$User->handler();

class User{
    function __construct(){
        global $Core;

        $this->u_id                 = false;
	$this->u_name               = false;
	$this->u_mail               = false;
	$this->u_pass               = false;
	$this->u_comm               = false;
	$this->u_data               = false;
	$this->u_usertype           = false;
	$this->u_block              = false;
	$this->u_sendEmail          = false;
	$this->u_registerDate       = false;
	$this->u_lastvisitDate      = false;
	$this->u_activation         = false;
	$this->u_recoverPassword    = false;
        
        $this->UserData             = array();
        
        $this->className            = 'User';

        $this->iniUserData          = array();
        if (is_file(__DOCUMENT_ROOT.__PRE_INI.'UserData.ini'))
            $this->iniUserData = parse_ini_file(__DOCUMENT_ROOT.__PRE_INI.'UserData.ini');
        
        return true;
    }

    function load($id) {
        global $Core;
        
        $res=getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users WHERE u_id=\''.optimizeForDb($id).'\'', '$User: Загрузка юзера');

        if (!$row=mysql_fetch_array($res)){
            $Core->error('Нет такого пользователя.');
            return false;
        };

        $this->u_id                   = $row['u_id'];
	$this->u_name                 = $row['u_name'];
	$this->u_mail                 = $row['u_mail'];
	$this->u_pass                 = $row['u_pass'];
        $this->u_comm                 = $row['u_comm'];
	$this->u_data                 = $row['u_data'];
	$this->u_usertype             = $row['u_usertype'];
	$this->u_block                = $row['u_block'];
	$this->u_sendEmail            = $row['u_sendEmail'];
	$this->u_registerDate         = $row['u_registerDate'];
	$this->u_lastvisitDate        = $row['u_lastvisitDate'];
	$this->u_activation           = $row['u_activation'];
	$this->u_recoverPassword      = $row['u_recoverPassword'];
        
        $this->UserData = json_decode($this->u_data, true);

        $res=getSqlResult('SELECT * FROM '.__MYSQL_PRE.'user_access WHERE ua_u_id='.$this->u_id.'', '$Core->checkAccess(): load');
        $num=mysql_num_rows($res);

        for($i=0;$i<$num;$i++){
            $row=mysql_fetch_array($res);

            $this->access[$row['ua_module']][$row['ua_access']]=$row;
        };

        return true;
    }

    function save() {
        global $Core;

        $name                 = optimizeForDb($this->u_name);
	$email                = optimizeForDb($this->u_mail);
	$password             = optimizeForDb($this->u_pass);
	$u_comm               = optimizeForDb($this->u_comm);
	$data                 = optimizeForDb(json_encode($this->UserData));
	$usertype             = optimizeForDb($this->u_usertype);
	$block                = optimizeForDb($this->u_block);
	$sendEmail            = optimizeForDb($this->u_sendEmail);
	$registerDate         = optimizeForDb($this->u_registerDate);
	$lastvisitDate        = optimizeForDb($this->u_lastvisitDate);
	$activation           = optimizeForDb($this->u_activation);
	$u_recoverPassword    = optimizeForDb($this->u_recoverPassword);

        if (empty($this->u_id)) {
            getSqlResult('INSERT INTO '.__MYSQL_PRE.'users
            (`u_id`, `u_name`, `u_mail`, `u_pass`, `u_data`, `u_usertype`, `u_block`, `u_sendEmail`, `u_registerDate`, `u_lastvisitDate`,  `u_activation`)
     VALUES (NULL, \''.$name.'\', \''.$email.'\', \''.$password.'\', \''.$data.'\', \''.$usertype.'\', \''.$block.'\', \''.$sendEmail.'\', \''.date('U').'\', \''.$lastvisitDate.'\', \''.$activation.'\');', '$User->save()');

            $this->u_id = mysql_insert_id();
        } else getSqlResult('UPDATE '.__MYSQL_PRE.'users
            SET
                `u_name`=\''.$name.'\',
                `u_mail`=\''.$email.'\',
                `u_pass`=\''.$password.'\',
                `u_comm`=\''.$u_comm.'\',
                `u_data`=\''.$data.'\',
                `u_usertype`=\''.$usertype.'\',
                `u_block`=\''.$block.'\',
                `u_sendEmail`=\''.$sendEmail.'\',
                `u_activation`=\''.$activation.'\',
                `u_recoverPassword`=\''.$u_recoverPassword.'\'
            WHERE u_id='.$this->u_id.'', '$User->save()');

        //Start modules
        if (!empty($this->access)) {
            reset($this->access);
            While ($e=each($this->access)){
                $module=$e['key'];

                if (isset($this->access[$module])) {
                    reset($this->access[$module]);
                    While ($k=each($this->access[$module])){
                        $access=$k['key'];

                        if (!empty($this->access[$module][$access]['der'])) {
                            if ($this->access[$module][$access]['der']=='add') {
                                getSqlResult('INSERT INTO '.__MYSQL_PRE.'user_access (`ua_id` ,`ua_u_id` , `ua_module` ,`ua_access`)
                                VALUES (NULL , \''.$this->u_id.'\', \''.optimizeForDb($module).'\', \''.optimizeForDb($access).'\');', '$User->save()');
                                $this->access[$module][$access]['oi_id'] = mysql_insert_id();
                            } else if ($this->access[$module][$access]['der']=='del') {
                                getSqlResult('DELETE FROM '.__MYSQL_PRE.'user_access WHERE ua_id='.$this->access[$module][$access]['ua_id'].'', '$User->save()');
                                unset($this->access[$module][$access]);
                            }
                        };
                    };

                }
            };
        };
        
        return true;
    }

    function handler() {
        global $Core;
        
        if ($Core->loginError) {
            $this->handlerloginError();
            return true;
        };

        if ($Core->action != 'User') return true;

        //Register
        if ($Core->getAction == 'Register') {
            $this->handlerRegister();
            return true;
        };

        if ($Core->getAction == 'Personal') {
            $this->handlerPersonal();
            return true;
        };

        if ($Core->getAction == 'RecoverPassword') {
            $this->handlerRecoverPassword();
            return true;
        };

        //Check access to ADM
        if (empty($Core->adm) OR $Core->action!='User') return true;
        
        $this->HTMLPath();

        if (empty($Core->getObj)) $this->HTMLListing();
        else {
            if ($Core->getObj != 'add') $this->load($Core->getObj);

            if (isset($_GET['delete'])) {
                $this->delete();
                $this->HTMLListing();
            } else {
                $this->up();
                $this->HTMLEdit();
            };
        };

        return true;
    }

    function handlerRegister() {
        global $Core;
        global $_SESSION;

        $this->register();

        $Core->title            = 'Регистрация';
        $Core->H1               = 'Регистрация';

        $Core->contentTpl = $Core->systemVars['preTpl'].'modules/'.$this->className.'/Register.html';

        if (empty($_SESSION['UserCheckCode'])) $_SESSION['UserCheckCode'] = rand(1000000, 999999999999);
        $this->UserCheckCode = $_SESSION['UserCheckCode'];
        
        $Core->moduleData[$this->className] = $this;

        return true;
    }

    function register() {
        global $_POST;
        global $_SESSION;
        global $Core;
        
        if (empty($_POST['newMail']) OR empty($_POST['newPass'])) return false;

        if ($_SESSION['UserCheckCode'] != $_POST['sKey']) {
            $Core->error('Для регистрации у вас должна быть включена поддержка JavaScript.');
            return false;
        };
        
        //newPassCheck

        //Check for added user
        $res=getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users WHERE u_mail=\''.optimizeForDb($_POST['newMail']).'\'', '$User->register()');

        if ($row=mysql_fetch_array($res)){
            $Core->error('Пользователь с такой почтой уже зарегистрирован в системе.');
            return false;
        };

	$this->u_name               = optimizeForDb($_POST['newMail']);
	$this->u_mail               = optimizeForDb($_POST['newMail']);
	$this->u_pass               = optimizeForDb(md5($_POST['newPass']));
	$this->u_registerDate       = __DATE;
	$this->u_lastvisitDate      = __DATE;

        // u_data ----------------------------------------------------------
        reset($this->iniUserData);
        while($e = each($this->iniUserData)) $this->UserData[$e['key']] = $_POST[$e['key']];

        $this->u_data       = json_encode($this->UserData);
        // /u_data ---------------------------------------------------------

        $this->save();

        $_POST["login"]     = $_POST['newMail'];
        $_POST["pass"]      = $_POST['newPass'];

        $Core->login();

        //$Core->message('Вы успешно зарегистрированы.');
        
        $Core->title            = 'Регистрация';
        $Core->H1               = 'Регистрация';
        
        if (is_file(__DOCUMENT_ROOT.__PRE_INI.'UserAfterRegisterLoadModules.ini')) {
            $load = parse_ini_file(__DOCUMENT_ROOT.__PRE_INI.'UserAfterRegisterLoadModules.ini');
            while($e = each($load)) $Core->loadModule($e['key']);
        };

        return true;
    }

    function handlerPersonal() {
        global $Core;
        global $_SESSION;
        global $_GET;
        
        if (empty($Core->user['u_id'])) {
            $Core->loginError();
            return false;
        }

        if (isset($_GET['changePass'])) $Core->message('Сейчас вам необходимо сменить пароль на новый.');
        
        $this->load($Core->user['u_id']);
        
        $Core->moduleData[$this->className] = $this;

        $this->personal();

        $Core->title            = 'Персональные настройки';
        $Core->H1               = 'Персональные настройки';

        $Core->contentTpl = $Core->systemVars['preTpl'].'modules/'.$this->className.'/Personal.html';
        
        return true;
    }

    function personal() {
        global $_SESSION;
        global $Core;
        
        if (empty($_POST['newMail'])) return false;
        
	if (isset($_POST['newName'])) $this->u_name = $_POST['newName'];
	$this->u_mail               = $_POST['newMail'];
	if (!empty($_POST['newPass']))
            $this->u_pass           = md5($_POST['newPass']);
        
        //$_SESSION['loginData'] = false;

        // u_data ----------------------------------------------------------
        reset($this->iniUserData);
        while($e = each($this->iniUserData)) $this->UserData[$e['key']] = $_POST[$e['key']];

        $this->u_data       = json_encode($this->UserData);
        // /u_data ---------------------------------------------------------

        $this->save();
        
        $_SESSION['login'] = $Core->cript($this->u_mail.'___'.$this->u_pass);

        $Core->login();
        $Core->checkAccess();
        
        $Core->title            = 'Профиль';
        $Core->H1               = 'Профиль';
        
        return true;
    }

    function handlerRecoverPassword() {
        global $Core;
        global $_SESSION;
        
        //$Core->moduleData['RecoverPassword'] = $this;

        $this->recoverPassword();
        if ($this->doRecoverPassword()) {
            Header( "HTTP/1.1 301 Moved Permanently" );
            Header( 'Location: '.$Core->URL.'Personal/?changePass' );
        };

        $Core->title            = 'Восстановление пароля';
        $Core->H1               = 'Восстановление пароля';

        $Core->contentTpl = $Core->systemVars['preAdmTpl'].'modules/'.$this->className.'/RecoverPassword.html';

        return true;
    }

    function recoverPassword() {
        global $_POST;
        global $Core;
        
        if (empty($_POST['recoverPasswordMail'])) return false;

        //Check mail
        $res=getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users WHERE u_mail=\''.optimizeForDb($_POST['recoverPasswordMail']).'\'', '$User: Check mail');

        if (!$row=mysql_fetch_array($res)){
            $Core->error('Нет пользователя с такой почтой.');
            return false;
        };
        
        //Set u_recoverPassword
        $this->load($row['u_id']);
        $this->u_recoverPassword = $Core->makePassword(50);
        $this->save();
        
        //Send mail
        $Core->sendMail($this->u_mail, ''.$Core->systemVars['siteName'].' - восстановление пароля', 'Для восстановления пароля перейдите по ссылке <a href="'.$Core->URL.'RecoverPassword/?a='.$this->u_recoverPassword.'">'.$Core->URL.'RecoverPassword/?a='.$this->u_recoverPassword.'</a>');
        $Core->message('На вашу почту было выслано письмо с дальнейшими инструкциями.');

        return true;
    }

    function handlerloginError() {
        global $Core;
        global $_SESSION;
        
        $Core->moduleTpl = $Core->systemVars['preTpl'].'modules/'.$this->className.'/LoginError.html';

        return true;
    }

    function doRecoverPassword() {
        global $_GET;
        global $Core;
        global $_POST;

        if (empty($_GET['a'])) return false;
        
        $res=getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users WHERE u_recoverPassword=\''.optimizeForDb($_GET['a']).'\'', '$User: Check mail');

        if (!$row=mysql_fetch_array($res)){
            $Core->error('Нет такого пользователя.');
            return false;
        };
        
        $mail = $row['u_mail'];
        $pass = $Core->makePassword(10);

        //Set u_recoverPassword
        $this->load($row['u_id']);
        $this->u_pass = md5($pass);
        $this->save();

        $_POST["login"]     = $mail;
        $_POST["pass"]      = $pass;

        $Core->login();

        return true;
    }

    function up(){
        global $Core;
        global $_POST;

        global $_MODULES;

        if (!empty($_POST)) {
            $this->u_name                   = $_POST['newName'];
            $this->u_mail                   = $_POST['newEmail'];
            $this->u_data                   = $_POST['newData'];
            $this->u_comm                   = $_POST['newComm'];

            if (!empty($_POST['newPass'])) $this->u_pass = md5($_POST['newPass']);
            
            //Start modules
            reset($_MODULES);
            While ($e=each($_MODULES)){
                $module=$e['key'];

                if (isset($_MODULES[$module]['access'])) {
                    reset($_MODULES[$module]['access']);
                    While ($k=each($_MODULES[$module]['access'])){
                        $access=$k['key'];

                        if (!empty($_POST[$module.'_'.$access]) AND empty($this->access[$module][$access])) {
                            $this->access[$module][$access]['der']='add';
                        } else if (empty($_POST[$module.'_'.$access]) AND !empty($this->access[$module][$access])) {
                            $this->access[$module][$access]['der']='del';
                        };
                    };

                }
            };
            
            // u_data ----------------------------------------------------------
            if (isset($this->iniUserData)) {
                reset($this->iniUserData);
                while($e = each($this->iniUserData)) {
                    $this->UserData[$e['key']] = $_POST[$e['key']];
                };
            };
            
            $this->u_data       = json_encode($this->UserData);
            // /u_data ---------------------------------------------------------

            $this->save();
        };

        return true;
    }

    function delete() {
        global $Core;

        getSqlResult('DELETE FROM '.__MYSQL_PRE.'users WHERE u_id=\''.$this->u_id.'\'', '$User: delete', true);

        $this->__construct();

        $Core->message('Пользователь успешно удален.');

        return true;
    }
    
    function HTMLPath() {
        global $Core;

        //$Core->HTMLLeftMenu .= '<h2><a href="adm/'.$this->className.'/add/">Добавить пользователя</a></h2>';
        $Core->navigation   .= '<a href="adm/'.$this->className.'/">Пользователи</a> <a href="adm/'.$this->className.'/add/"><img src="'.$Core->sysTplPath.'img/add.png"></a>';
        
        $Core->searchForm = true;
        
        return true;
    }
    
    function HTMLListing(){
        global $Core;
        global $_GET;
        global $_MODULES;

        $echo='';

        if (!empty($_GET['search']) AND strlen($_GET['search']) > 2){
            $res = getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users WHERE u_name LIKE \'%'.optimizeForDb($_GET['search']).'%\' OR
                u_mail LIKE \'%'.optimizeForDb($_GET['search']).'%\' OR
                u_id LIKE \'%'.optimizeForDb($_GET['search']).'%\'
                ORDER BY u_registerDate DESC', '$'.$this->className.': HTMLAdmSearch');
        } else $res = getSqlResult('SELECT * FROM '.__MYSQL_PRE.'users ORDER BY u_registerDate DESC', '$User: Листинг юзера');

        if (is_file(__CORE_DOCUMENT_ROOT.'Core/modules/YandexDirectJSON/YandexDirectJSON.php')) {
            require_once(__CORE_DOCUMENT_ROOT.'Core/modules/YandexDirectJSON/YandexDirectJSON.php');
            $YandexDirectJSON = new YandexDirectJSON();

            // Создание массива фраз у каждого юзера -------------------------------
            if (isset($_MODULES['YandexDirectJSON'])) {
                $resYDJSON = getSqlResult('SELECT ydp_u_ud, ydp_phrasesInWork, ydp_phrasesTotal, ydp_phrasesTopInit, ydp_paidUpRate, ydp_paidUpRateDate FROM yandexDirectPayments', '$'.$this->className.': HTMLListing');
                while ($row = mysql_fetch_array($resYDJSON)) $paymentsArr[$row['ydp_u_ud']] = $row;

                /*
                $resYDJSON = getSqlResult('SELECT count(PhraseID) AS count, yl_u_id FROM yandex_logins, yandex_direct_campaigns, yandex_direct_phrases
                WHERE   yl_id = yandex_direct_campaigns.LoginID AND
                        yandex_direct_campaigns.CampaignID = yandex_direct_phrases.CampaignID
                        GROUP BY yl_u_id', '$'.$this->className.': Cron');
                while ($row = mysql_fetch_array($resYDJSON)) $paymentsArr[$row['yl_u_id']]['totalPhrases'] = $row['count'];
                */
                
                $YDJSON = true;
            } else $YDJSON = false;
        } else $YDJSON = false;
        // ---------------------------------------------------------------------
        
        $num = mysql_num_rows($res);
        for($i=0;$i<$num;$i++){
            $row=mysql_fetch_array($res);

            $echo .= '<tr valign="top"';
            if ($YDJSON) {
                if ($paymentsArr[$row['u_id']]['ydp_phrasesInWork'] > 50000)
                    $echo .= ' style="background: #FF6666"';
                else if ($paymentsArr[$row['u_id']]['ydp_paidUpRate'] == 1 AND $paymentsArr[$row['u_id']]['ydp_phrasesInWork'] > 10000)
                    $echo .= ' style="background: #FFCC66"';
            };             
            $echo .= '>
                <td>'.($i+1).'</td>
                <td>'.$row['u_id'].'</td>
                <td><a href="adm/User/'.$row['u_id'].'/">'.$row['u_mail'].'</a></td>';

            if (!empty($Core->user['access']['User']['logAsUser']) OR $Core->user['u_id'] == 1)
                $echo .= '<td><a href="adm/'.$this->className.'/?loginAsUser='.$row['u_id'].'">Войти</a></td>';

            if ($YDJSON) {
                $echo .= '<td style="text-align:center">';
                if (!empty($paymentsArr[$row['u_id']]['ydp_phrasesInWork']) AND
                    $paymentsArr[$row['u_id']]['ydp_paidUpRateDate'] > __DATE) $echo .= '<span style="color: green">'.$paymentsArr[$row['u_id']]['ydp_phrasesInWork'].'</span>&nbsp;/&nbsp;';
                if (!empty($paymentsArr[$row['u_id']]['ydp_phrasesTotal'])) $echo .= $paymentsArr[$row['u_id']]['ydp_phrasesTotal'];
                $echo .= '</td>';
                $echo .= '<td style="text-align:center">';
                if (!empty($paymentsArr[$row['u_id']])) {
                    if ($paymentsArr[$row['u_id']]['ydp_paidUpRateDate'] < __DATE) $echo .= '<span style="color:grey">';
                    $tariff = $paymentsArr[$row['u_id']]['ydp_paidUpRate'];
                    $echo .= $YandexDirectJSON->tariffs[$tariff]['name'];
                };
                $echo .= '</td>';
                $echo .= '<td>';
                if (!empty($paymentsArr[$row['u_id']]['ydp_paidUpRateDate'])) {
                    if ($paymentsArr[$row['u_id']]['ydp_paidUpRateDate'] < __DATE) $echo .= '<span style="color:grey">';
                    else {
                        if (!isset($activeTarrifs[$tariff])) $activeTarrifs[$tariff] = 0;
                        $activeTarrifs[$tariff]++;
                    };
                    
                    $echo .= date('d.m.Y', $paymentsArr[$row['u_id']]['ydp_paidUpRateDate']);
                }
                $echo .= '</span></td>';
            };

            $echo .= '
                <td>'.$row['u_comm'].'</td>
                <td>'.date('H:i d.m.y', $row['u_registerDate']).'</td>
                <td>'.date('H:i d.m.y', $row['u_lastvisitDate']).'</td></tr>';
        };

        $newEcho = '';

        // Фраз без компаний
        /*
        if ($YDJSON) {
            $resYDJSON = getSqlResult('SELECT PhraseID
                FROM yandex_direct_phrases LEFT JOIN yandex_direct_campaigns ON yandex_direct_campaigns.CampaignID = yandex_direct_phrases.CampaignID
                        WHERE yandex_direct_campaigns.CampaignID IS NULL', '$'.$this->className.': Cron');
             $num = mysql_num_rows($resYDJSON);
        };
         * 
         */
        
        $total = 0;

        if (isset($activeTarrifs)) {
            $newEcho .= 'Статистика активных тарифов:';
            while(list($key, $value) = each($activeTarrifs)) {
                $newEcho .= ' «'.$YandexDirectJSON->tariffs[$key]['name'].'»: '.$value.'';

                $total += $value * $YandexDirectJSON->tariffs[$key]['prise'];
            };
            
            $newEcho .= ' Итого: '.($total*30).' руб./мес.';
        };

        $newEcho .= '<table cellpadding="0" cellspacing="0" class="listingTable">
            <tr>
                <th></th>
                <th>#</th>
                <th>Почта</th>';

        if (!empty($Core->user['access']['User']['logAsUser']) OR $Core->user['u_id'] == 1) $newEcho .= '<th>Войти</th>';
        if ($YDJSON) {
            $newEcho .= '<th style="text-align:center">Фраз</th>';
            $newEcho .= '<th style="text-align:center">Тариф</th>';
            $newEcho .= '<th>Оплачен</th>';
        };

        $newEcho .= '
                <th>Комментарии</th>
                <th>Регистрация</th>
                <th>Активность</th>
            </tr>'.$echo.'</table>';

        $Core->content .= $newEcho;

        $Core->title            = 'Пользователи';
        $Core->H1               = 'Пользователи';
        
        return true;
    }
    
    function HTMLEdit(){
        global $Core;

        global $_MODULES;

        $echo = '';

        if (!empty($this->u_id)) {
            $Core->title            = 'Пользователь "'.$this->u_mail.'"';
            $Core->H1               = 'Пользователь "'.$this->u_mail.'" <a class="del"
                href="javascript:confirmRedirect(\'Удалить?\', \''.$Core->URL.'adm/'.$this->className.'/'.$this->u_id.'/?delete\')"><img src="'.$Core->sysTplPath.'img/del.png"></a>';
            $Core->navigation       .= '<a href="adm/'.$this->className.'/'.$this->u_id.'/">"'.$this->u_mail.'"</a>';
        } else {
            $Core->title            = 'Добавить пользователя';
            $Core->H1               = 'Добавить пользователя';
            $Core->navigation       .= '<a href="adm/'.$this->className.'/add/">Добавить пользователя</a>';
        };
        
        $echo .= '<FORM method="post" action="adm/'.$this->className.'/';

        if (!empty($this->u_id)) $echo .= $this->u_id;
        else $echo .= 'add';

        $echo .= '/">';
        
        $echo .= '<table cellpadding="0" cellspacing="0" class="table">
            <tr><th style="width:50px">Имя: </th>      <td><input type="text" value="'.$this->u_name.'" name="newName"></td>';
        $echo .= '</tr>
            <tr><th>Почта: </th>    <td><input type="text" value="'.$this->u_mail.'" name="newEmail"></td></tr>
            <tr><th>Пароль: </th>   <td><input type="text" value="" name="newPass"></td>
            <tr><th>Комментарии: </th>    <td><textarea name="newComm">'.$this->u_comm.'</textarea></td></tr>
            <tr><th>Данные: </th>   <td><textarea name="newData">'.$this->u_data.'</textarea></td></tr>
            </tr>';

        if (!empty($this->iniUserData)) {
            reset($this->iniUserData);
            while($e = each($this->iniUserData)) {
                $echo .= '<tr>
                    <th>'.$e['value'].': </th>
                    <td><input type="text" value="';
                if (!empty($this->UserData[$e['key']])) $echo .= $this->UserData[$e['key']];
                $echo .= '" name="'.$e['key'].'"></td>
                </tr>';
            };
        };
        
        $echo .= '<tr><th>Доступы: </th>   <td>';
        
        //Start modules
        reset($_MODULES);
        While ($e=each($_MODULES)){
            $module=$e['key'];
            $echo .= '<div style="padding:0px 0px 5px 0px">';

            if (isset($_MODULES[$module]['access'])) {
                reset($_MODULES[$module]['access']);
                While ($k=each($_MODULES[$module]['access'])){
                    $access=$k['key'];

                    $echo .= '<div><input type="checkbox" name="'.$module.'_'.$access.'"';
                    if (isset($this->access[$module][$access]))$echo .= ' checked';
                    $echo .= '> ';
                    if (!empty($_MODULES[$module]['name'])) $echo .= ''.$_MODULES[$module]['name'].': ';
                    if ($_MODULES[$module]['access'][$access]['name']!='') $echo .=''.$_MODULES[$module]['access'][$access]['name'];
                    $echo .= '</div>';
                };
            };

            $echo .= '</div>';
        };

        $echo.='</td></tr>
            <tr><td></td><td><input type="submit" value="Сохранить"></td></tr>
            </table></form>';

        $Core->content.=$echo;

        return true;
    }
};
?>
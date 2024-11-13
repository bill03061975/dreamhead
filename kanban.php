<?php /* dreamhead | kanban.php */
{   defined('_BEXEC') or die;
    $GLOBALS['ses_num']=0; $GLOBALS['dhu_id']=0; $GLOBALS['dhu_id']=""; $GLOBALS['dhu_name']=""; $GLOBALS['dhu_dhj_id']=0;
    if (isset($_COOKIE['dh_user_token'])) {
        $res=DoSql("SELECT * FROM dh_sessions JOIN dh_users ON ses_user_id=dhu_id WHERE ses_close_ts=0 AND ses_token='".stringForQuery($_COOKIE['dh_user_token'])."'");
        if ($row=$res->fetch_assoc()) {
            $GLOBALS['ses_num']=$row['ses_num'];$GLOBALS['dhu_id']=$row['dhu_id']; $GLOBALS['dhu_name']=$row['dhu_name']; $GLOBALS['dhu_dhj_id']=$row['dhu_dhj_id'];
            if($GLOBALS['dhu_dhj_id']==0) {
                $res1=DoSql("SELECT dhj_id FROM dh_jobs WHERE dhj_dhu_id='{$GLOBALS['dhu_id']}' AND dhj_status=1 ORDER BY dhj_date DESC, dhj_name LIMIT 1");
                if($row1=$res1->fetch_assoc()) { $GLOBALS['dhu_dhj_id']=$row1['dhj_id'];}
            }
        }
    }
}
class kanban {
static function selector() {
    switch ($GLOBALS['unit']) {
        case "main": self::kanban_main(); break;
        case "load": self::kanban_load($GLOBALS['page']); break;
        case "ajax": self::kanban_ajax($GLOBALS['page']); break;
        default: echoError404();  break;
    }
}
static function kanban_main() {
    echo "<!DOCTYPE HTML>\n<HTML>\n<HEAD>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <!-- No Cache -->
    <meta HTTP-EQUIV='Expires' CONTENT='0' />
    <meta HTTP-EQUIV='Pragma' CONTENT='no-cache' />
    <meta HTTP-EQUIV='Cache-Control' CONTENT='no-cache' />
    <meta http-equiv='Cache-Control' content='no-cache, must-revalidate' />
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <!-- Page Title -->
    <title>dreamhead</title>
    <!-- Favicon -->
    <link rel='icon' type='image/png' href='/files/css/favicon.png' />
    <link rel='shortcut icon' type='image/png' href='/files/css/favicon.png' />
    <!-- jQuerys -->
    <script type='text/javascript' src='/files/js/jquery-2.1.3.min.js'></script>
    <link rel='stylesheet' type='text/css' href='/files/js/jquery-ui-1.9.2.custom.min.css' />
    <script type='text/javascript' src='/files/js/jquery-ui-1.9.2.custom.min.js'></script>
    <script type='text/javascript' src='/files/js/jquery.ui.datepicker-ru.js'></script>
    <!-- Styles -->
    <link rel='stylesheet' type='text/css' href='/files/css/dreamhead.css' />
    </HEAD>\n<BODY class='theme-light'>\n";
    if ($GLOBALS['dhu_id']!=0) {self::kanban_canban();} else {self::kanban_login();}
    echo "\n</BODY>\n</HTML>";
    return;
}
static function kanban_login() {
    if ($GLOBALS['dhu_id']!=0) { return; }
    echo
    "<div style='display:flex;'>
        <div><img id=img_login src=/files/css/login.jpg style='height:80%;width:auto;'></div>
        <div style='flex-grow:2;display:flex;align-items:center;'>
            <div style='margin:25px;'>
                <div class=login-title>Областной командный чемпионат кейсов «Учитех» в сфере ИТ-направления для студентов образовательных организаций Белгородской области</div>
                <div class=login-title>Кейс: Статус-трекер для управления резюме кандидатов</div><br>
                <center><img id=img_login src=/files/css/dreamhead.png style='height:200px;width:auto;'></center>
                <center><img id=img_login src=/files/css/dreamname.png style='height:50px;width:auto;'></center>
                <div id=answer_wrp style='display:none;'></div>
                <div id=login_wrp style='margin:auto; width:250px; display:none;'>
                    <div class='dialog-title'>Вход в трекер</div>
                    <div class='dialog-label'>Логин</div><input id=cab_login_login class='dialog-input'>
                    <div class='dialog-label'>Пароль</div><input type=password id=cab_login_pass class='dialog-input'>
                    <div id=btn_login class='btn-green' style='margin-top:10px;width:85%;'>Войти</div>
                </div>
                <br><div class=login-title>Команда Монтажник ОГАПОУ Алексеевский колледж</div>
            </div>
        </div>
    </div>
    <script type='text/javascript'>
        var login_layots = function() {
            jQuery('#img_login').height(jQuery(window).height()-5);
        }
    jQuery(function(){
        login_layots();jQuery(window).bind('resize', function(){login_layots();});

        jQuery('#login_wrp').fadeIn(1000);

        jQuery('#btn_login').on('click',function(){
            jQuery('#login_wrp').hide();jQuery('#answer_wrp').html('".WAITING."').fadeIn(700);
            jQuery.ajax({url:'/kanban/ajax/login/',type:'POST', data:{login:jQuery('#cab_login_login').val(),pass:jQuery('#cab_login_pass').val(),},
                success: function(answer){jQuery('#answer_wrp').html(answer);} });
        });

    });</script>";
}
static function kanban_canban() {
    self::kanban_load("dhj_init");
    self::kanban_load("dhr_init");
    /*активные вакансии*/$job_items="<hidden><input id=cur_dhj_id></hidden>"; $res=DoSql("SELECT * FROM dh_jobs WHERE dhj_dhu_id='{$GLOBALS['dhu_id']}' AND dhj_status=1 ORDER BY dhj_date DESC,dhj_name");
    while ($row=$res->fetch_assoc()) {
        if($row['dhj_id']==$GLOBALS['dhu_dhj_id']) { $cl="job-item-active"; } else { $cl=""; }
        $job_items.="<div dhj_id='{$row['dhj_id']}' class='job-item {$cl}'>{$row['dhj_name']}
            <div><b>Зарплата:</b> {$row['dhj_salary_min']}-{$row['dhj_salary_max']} <b>{$row['dhj_whours']}</b><br><b>Опыт:</b> {$row['dhj_experience']}
            <br><b>Обязанности:</b> {$row['dhj_duties']}<br><b>Требования:</b> {$row['dhj_requirements']}<br><b>Условия:</b> {$row['dhj_conditions']}</div></div>";
    } $job_items.="<div dhj_id='0' class='job-item'>Ещё</div>";
    echo "<div class='kanban-menu'>
        <a href=/><img src=/files/css/dreamhead.png style='margin:6px 3px 0 10px;height:40px;width:auto;'><img src=/files/css/dreamname.png style='margin-bottom:3px;height:30px;width:auto;'></a>
        <div id='dhr_add_btn' class='btn-green' style='margin-top:6px;'>Добавить резюме</div>
        <df>{$job_items}</df>
        <df><div class=kanban-menu-dropitem>{$GLOBALS['dhu_name']}
            <div> <aa id='user_exit_btn'><aa>Выйти</aa>
            </div>
        </div></df>
    </div>
    <div id='kanban-flex-1' class='kanban-flex'>
        <div id='kanban-column-1' dhs_id='1' class='kanban-column' style='background-color:#FFFFBA;'></div>
        <div id='kanban-column-2' dhs_id='2' class='kanban-column' style='background-color:#F8FFBA;'></div>
        <div id='kanban-column-3' dhs_id='3' class='kanban-column' style='background-color:#F0FFBA;'></div>
        <div id='kanban-column-4' dhs_id='4' class='kanban-column' style='background-color:#E8FFBA;'></div>
        <div id='kanban-column-5' dhs_id='5' class='kanban-column' style='background-color:#E0FFBA;'></div>
        <div id='kanban-column-6' dhs_id='6' class='kanban-column' style='background-color:#D8FFBA;'></div>
    </div>
    <div class=kanban-footer><div>Областной командный чемпионат кейсов «Учитех» в сфере ИТ-направления для студентов Белгородской области</div>
        <div>Кейс: Статус-трекер для управления резюме кандидатов</div><div>Команда <b>Монтажник</b> ОГАПОУ Алексеевский колледж</div></div>

    <script type='text/javascript'>
        var kanban_column_layots = function() {
            jQuery('.kanban-flex').width(jQuery(window).width()-16);
            jQuery('.kanban-column').height(jQuery(window).height()-82);
        }
        var kanban_column_reload = function(dhs_id) {
            jQuery('#kanban-column-'+dhs_id).load('/kanban/load/dhr_list',{  dhr_dhs_id:dhs_id, dhj_id:jQuery('#cur_dhj_id').val() });
        }
        var kanban_reload = function() {
            jQuery.ajax({url: '/kanban/ajax/save_cur_dhj_id/', type: 'POST', data: {cur_dhj_id:jQuery('#cur_dhj_id').val() } });
            jQuery('.kanban-column').each(function(){ kanban_column_reload(jQuery(this).attr('dhs_id')); });
        }
    jQuery(function(){
        jQuery('#cur_dhj_id').val('{$GLOBALS['dhu_dhj_id']}'); kanban_reload();

        kanban_column_layots();jQuery(window).bind('resize', function(){kanban_column_layots();});

        jQuery('.kanban-column').droppable({
            hoverClass: 'kanban-column-hover',
            drop: function( event, ui ) {
                jQuery.ajax({url: '/kanban/ajax/save_dhr_dhs_id/', type: 'POST', data: { dhr_id: ui.draggable.attr('dhr_id'), dhs_id: jQuery(this).attr('dhs_id') },
                    success: function(answer){ if (answer.substr(0,2)=='OK') { kanban_reload(); jQuery('#dhr_dia').dialog('close'); } else { alert(answer); } }  });
            }
        });

        jQuery('#dhr_add_btn').on('click',function(){
            jQuery('#dhr_dia').html('".WAITING."').dialog('open'); jQuery('#dhr_edit,#dhr_del').hide(); jQuery('#dhr_add').show(); jQuery('#dhr_dia').load('/kanban/load/dhr_add',{ dhj_id:jQuery('#cur_dhj_id').val()}); });

        jQuery('.job-item').on('click',function(){
            if(jQuery(this).attr('dhj_id')=='0') { jQuery('#dhj_list').html('".WAITING."').dialog('open'); jQuery('#dhj_list').load('/kanban/load/dhj_list',{ }); return;}
            jQuery('.job-item').removeClass('job-item-active'); jQuery(this).addClass('job-item-active'); jQuery('#cur_dhj_id').val(jQuery(this).attr('dhj_id')); kanban_reload(); });

        jQuery('#user_exit_btn').on('click',function(){
            jQuery.ajax({url:'/kanban/ajax/exit/',type:'POST', data:{}, success: function(answer){jQuery('#user_exit_btn').html(answer);} });  });

    });</script>";
}
static function kanban_load($page) {
    if ($page=="dhr_list") {
        /*ини*/if(1==1) {
            $dhr_dhs_id=(int)$_POST['dhr_dhs_id'];
            $resDhs=DoSql("SELECT * FROM dh_statuses WHERE dhs_id='{$dhr_dhs_id}'"); if ($rowDhs=$resDhs->fetch_assoc()) {  } else { return; }
            $dhj_id=(int)$_POST['dhj_id'];
            $resDhj=DoSql("SELECT * FROM dh_jobs WHERE dhj_id='{$dhj_id}'"); if ($rowDhj=$resDhj->fetch_assoc()) {  } else { return; }
        }
        /*заголовки*/if(1==1) {
            echo "<div class='kanban-list-title'>{$rowDhs['dhs_name']}</div>";
        }
        echo "<div id='kanban-list-{$dhr_dhs_id}' class='kanban-list'>";
        /*прогон*/if(1==1) {
            $res=DoSql("SELECT * FROM dh_resumes WHERE dhr_dhu_id='{$GLOBALS['dhu_id']}' AND dhr_dhj_id='{$dhj_id}' AND dhr_dhs_id='{$dhr_dhs_id}' ORDER BY dhr_name1,dhr_name2,dhr_name3");
            while ($row=$res->fetch_assoc()) {
                $files_html=date0($row['dhr_date']);
                if($rowDhs['dhs_field_files']!="") {$files_html=files2html("docs",date("Y",$row['dhr_date']),$row[$rowDhs['dhs_field_files']],32,2);}
                if($files_html=="") {$files_html="<img src=/files/css/nook.png style=\'width:auto;height:32px;padding:2px;\'>";}
                echo
                "<div dhr_id='{$row['dhr_id']}' class='kanban-list-row kanban-list-row-{$dhr_dhs_id}'>
                    <div><img src='/kanban/load/dhr_photo/{$row['dhr_id']}/".strlen($row['dhr_photo'])."' class='kanban-list-row-photo'></div>
                    <div class='kanban-list-row-name'>
                        <div style='font-weight:bold;'>{$row['dhr_name1']}</div>
                        <div>{$row['dhr_name2']}</div>
                        <div>{$row['dhr_name3']}</div>
                    </div>
                    <div class='kanban-list-row-files'>$files_html</div>
                </div>";
            }
        }
        echo "</div>";
        /*js*/if(1==1) {
            echo "<script type='text/javascript'>jQuery(function(){
                jQuery('.kanban-list-row-{$dhr_dhs_id}').draggable({appendTo: 'body', containment: '#kanban-flex-1', scroll: false, helper: 'clone'});
                jQuery('.kanban-list-row-{$dhr_dhs_id}').on('click',function(){
                    jQuery('#dhr_dia').html('".WAITING."').dialog('open'); jQuery('#dhr_edit,#dhr_del').show(); jQuery('#dhr_add').hide();
                    jQuery('#dhr_dia').load('/kanban/load/dhr_edit/',{ dhr_id: jQuery(this).attr('dhr_id') });
                });
            });</script>";
        }
        return;
    }
    if ($page=="dhr_init") {
        echo "<div id='dhr_dia'></div>
        <script type='text/javascript'>jQuery(function(){

        jQuery('#dhr_dia').dialog({width:800,height:625,autoOpen:false,modal:true,dialogClass:'modal-dialog',show:{effect:'drop',duration:800}, hide:{effect:'drop',duration:400}, buttons: [
            {id: 'dhr_add', text: 'Добавить', class: 'dialog-btn-yellow', click: function() {
                data={dhr_dhj_id:jQuery('#cur_dhj_id').val()}; jQuery('.dhr_vals').each(function(i) {data[jQuery(this).attr('id')]=jQuery(this).val();});
                if (jQuery('#dhr_projbox').prop('checked')) {data['dhr_projbox']='1';} else {data['dhr_projbox']='0';}
                jQuery.ajax({url: '/kanban/ajax/dhr_add/', type: 'POST', data: data,
                    success: function(answer){if (answer.substr(0,2)=='OK') { kanban_reload(); jQuery('#dhr_dia').dialog('close'); } else {alert(answer);}}
                });
            } },
            {id: 'dhr_edit', text: 'Сохранить', class: 'dialog-btn-orange', click: function() {
                data={}; jQuery('.dhr_vals').each(function(i) {data[jQuery(this).attr('id')]=jQuery(this).val();});
                if (jQuery('#dhr_projbox').prop('checked')) {data['dhr_projbox']='1';} else {data['dhr_projbox']='0';}
                jQuery.ajax({url: '/kanban/ajax/dhr_edit/', type: 'POST', data: data,
                    success: function(answer){if (answer.substr(0,2)=='OK') { kanban_reload(); jQuery('#dhr_dia').dialog('close'); } else {alert(answer);}}
                });
            } },
            {id: 'dhr_del', text: 'Удалить', class: 'dialog-btn-red', click: function() {
                if (confirm('Подтвердите удаление!')) jQuery.ajax({url: '/kanban/ajax/dhr_del/', type: 'POST', data: { dhr_id:jQuery('#dhr_id').val() },
                    success: function(answer){if (answer.substr(0,2)=='OK') { kanban_reload(); jQuery('#dhr_dia').dialog('close'); } else {alert(answer);}}
                });
            } },
            {id: 'dhr_cancel', text: 'Отмена', class: 'dialog-btn-grey', click: function() { jQuery(this).dialog('close');} },
        ], });

        });</script>";
        return;
    }
    if ($page=="dhr_add") {
        $dhj_id=(int)$_POST['dhj_id'];
        $resDhj=DoSql("SELECT * FROM dh_jobs WHERE dhj_id='{$dhj_id}'"); if ($rowDhj=$resDhj->fetch_assoc()) {  } else { return; }
        echo "<div class='dialog-title'>Добавление резюме<div class='job-item job-item-active'>{$rowDhj['dhj_name']}</div></div><div id=frominet_wrp></div>";
        //echo "<div class='dialog-title'>Добавление резюме<div id='dhr_addfrominet_btn' class='btn-green-mini'>Добавить по ссылке</div><div class='job-item job-item-active'>{$rowDhj['dhj_name']}</div></div><div id=frominet_wrp></div>";
        self::kanban_resume_blank();
        echo getFilesAjax("docs",date("Y"),"","dhr_res");
        echo getFilesAjax("docs",date("Y"),"","dhr_scr");
        echo getFilesAjax("docs",date("Y"),"","dhr_view");
        echo getFilesAjax("docs",date("Y"),"","dhr_sb");
        echo getFilesAjax("docs",date("Y"),"","dhr_offer");
        //echo "<script type='text/javascript'>jQuery(function(){
            //jQuery('#dhr_addfrominet_btn').on('click',function(){
                //jQuery('#frominet_wrp').load('/kanban/load/dhr_frominet',{ link:jQuery('#dhr_description').val() });
            //});
        //});</script>";
        return;
    }
    if ($page=="dhr_frominet") {
        $link=$_POST['link'];
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$link);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch); $httpcodeping = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        /*статус*/if ($httpcodeping!="200") { echo $httpcodeping; } else {  echo "OK";  }
        return;
    }
    if ($page=="dhr_edit") {
        $dhr_id=(int)$_POST['dhr_id'];
        $resDhr=DoSql("SELECT * FROM dh_resumes JOIN dh_statuses ON dhr_dhs_id=dhs_id  WHERE dhr_id='{$dhr_id}'"); if ($rowDhr=$resDhr->fetch_assoc()) {  } else { return; }
        $dhr_date=$rowDhr['dhr_date']; $rowDhr['dhr_date']=date0($rowDhr['dhr_date']); $dhr_photo=$rowDhr['dhr_photo']; unset($rowDhr['dhr_photo']);
        if ($rowDhr['dhr_projbox']==1) {$set_checkbox="jQuery('#dhr_projbox').prop('checked',true)";} else { $set_checkbox=""; }
        $resDhj=DoSql("SELECT * FROM dh_jobs WHERE dhj_id='{$rowDhr['dhr_dhj_id']}'"); if ($rowDhj=$resDhj->fetch_assoc()) {  } else { return; }
        $class_input_files="dialog-input-bold"; if($rowDhr['dhs_field_files']!="" && $rowDhr[$rowDhr['dhs_field_files']]=="") { $class_input_files="dialog-input-red"; }
        $check_status=""; if ($rowDhr['dhs_field_files']!="") { $check_status="jQuery('div[field_files={$rowDhr['dhs_field_files']}]').addClass('{$class_input_files}');"; }
        echo "<div class='dialog-title'>Редактирование резюме<div class='job-item job-item-active'>{$rowDhj['dhj_name']}</div></div>";
        echo "<input id='dhr_id' class='dhr_vals hidden'>";
        self::kanban_resume_blank();
        echo "<script type='text/javascript'>jQuery(function(){
            jQuery('#dhr_photo_img').html('<img src=\'/kanban/load/dhr_photo/{$dhr_id}/".strlen($dhr_photo)."\' class=\'dialog-photo\'>');
            var data=".json_encode($rowDhr).";  jQuery('.dhr_vals').each(function (i) {jQuery(this).val(data[jQuery(this).attr('id')]);});
            {$set_checkbox}
            {$check_status}
        });</script>";
        echo getFilesAjax("docs",date("Y",$dhr_date),$rowDhr['dhr_res_files'],"dhr_res");
        echo getFilesAjax("docs",date("Y",$dhr_date),$rowDhr['dhr_scr_files'],"dhr_scr");
        echo getFilesAjax("docs",date("Y",$dhr_date),$rowDhr['dhr_view_files'],"dhr_view");
        echo getFilesAjax("docs",date("Y",$dhr_date),$rowDhr['dhr_sb_files'],"dhr_sb");
        echo getFilesAjax("docs",date("Y",$dhr_date),$rowDhr['dhr_offer_files'],"dhr_offer");
        return;
    }
    if ($page=="dhr_photo") {
        $id=(int)$GLOBALS['page1'];
        $res = DoSql ("SELECT dhr_photo FROM dh_resumes WHERE dhr_id=$id");
        if ($row=$res->fetch_assoc()) {
            if ($row['dhr_photo']=="") { header("Content-type: jpg"); echo file_get_contents("/home/bingosites/bingo.softbi.info/img/noimage.png");
            } else  { header("Content-type: jpg"); header("Cache-control: max-age=100"); echo $row['dhr_photo']; }
        } else { header("Content-type: jpg"); echo file_get_contents("/home/bingosites/bingo.softbi.info/img/noimage.png"); }
        return;
    }
    if ($page=="photo_tmp") {
        $filename=$_GET["filename"];
        header("Content-type: jpg"); echo file_get_contents($GLOBALS['bof']."tmp/".$filename);
        return;
    }
    if ($page=="file") {
        if (isset($_GET['fc'])) {$fc=$_GET['fc'];} else {echoError404("Файл не найден!"); }
        if (isset($_GET['fb'])) {$fb=$_GET['fb'];} else {echoError404("Файл не найден!"); }
        if (isset($_GET['fn'])) {$fn=$_GET['fn'];} else {echoError404("Файл не найден!"); }
        $ffn=$GLOBALS['bof'].$fc."/".$fb."/".$fn; $ext=pathinfo($fn, PATHINFO_EXTENSION);
        if (!is_file($ffn)) {echoError404("Файл не найден!"); }
        if ($ext=="pdf" || $ext=="PDF") {header("Content-type: application/pdf");}
        elseif ($ext=="doc" || $ext=="DOC" || $ext=="docx" || $ext=="DOCX") {header("Content-type: application/doc");}
        elseif ($ext=="xls" || $ext=="XLS" || $ext=="xlsx" || $ext=="XLSX") {header("Content-type: application/xls");}
        else {header("Content-type: application/octet-stream");}
        header("Content-Disposition: inline; filename=delta-$fc-$fb-$fn");
        header("Accept-Ranges: bytes"); header("Content-Length: ".filesize($ffn));
        readfile($ffn);  return;
    }
    if ($page=="dhj_list") {
        echo "<div class='dialog-title'>Вакансии<div id=add_dhj_btn class='btn-green-mini'>Добавить вакансию</div></div>";
        echo "<div class='dhj_list-header'>
            <div style='width:8%;'>Трекер</div>
            <div style='width:10%;'>Вакансия</div>
            <div style='width:10%;'>Режим/Опыт</div>
            <div style='width:25%;'>Обязанности</div>
            <div style='width:25%;'>Требования</div>
            <div style='width:25%;'>Условия</div>
        </div>";
        $res=DoSql("SELECT * FROM dh_jobs WHERE dhj_dhu_id='{$GLOBALS['dhu_id']}' AND 1=1 ORDER BY dhj_date DESC, dhj_name");
        while ($row=$res->fetch_assoc()) {
            if($row['dhj_status']=='1') {$checked="checked";} else {$checked="";}
            echo "<div dhj_id='{$row['dhj_id']}' class='dhj_list-rows'>
                <div style='width:8%;text-align:center;'><input dhj_id='{$row['dhj_id']}' class='dhj_status-checkbox' type='checkbox' $checked><div>".date0($row['dhj_date'])."</div></div>
                <div style='width:10%;'><b>{$row['dhj_name']}</b><br>{$row['dhj_salary_min']}-{$row['dhj_salary_max']}</div>
                <div style='width:10%;'>{$row['dhj_whours']}<br>{$row['dhj_experience']}</div>
                <div style='width:25%;'>{$row['dhj_duties']}</div>
                <div style='width:25%;'>{$row['dhj_requirements']}</div>
                <div style='width:25%;'>{$row['dhj_conditions']}</div>
            </div>";
        }
        echo "<script type='text/javascript'>jQuery(function(){
            jQuery('#add_dhj_btn').on('click',function(){
                jQuery('#dhj_dia').html('".WAITING."').dialog('open'); jQuery('#dhj_edit,#dhj_del').hide(); jQuery('#dhj_add').show();
                jQuery('#dhj_dia').load('/kanban/load/dhj_add/',{ dhj_id: jQuery(this).attr('dhj_id') });
            });
            jQuery('.dhj_list-rows').on('click',function(){
                jQuery('#dhj_dia').html('".WAITING."').dialog('open'); jQuery('#dhj_edit,#dhj_del').show(); jQuery('#dhj_add').hide();
                jQuery('#dhj_dia').load('/kanban/load/dhj_edit/',{ dhj_id: jQuery(this).attr('dhj_id') });
            });
            jQuery('.dhj_status-checkbox').on('click',function(e){
                e.stopPropagation();
                jQuery.ajax({url: '/kanban/ajax/dhj_status/', type: 'POST', data: {dhj_id:jQuery(this).attr('dhj_id')}, success: function(answer){jQuery('#dhj_list').load('/kanban/load/dhj_list',{ }); } });
            });
        });</script>";
        return;
    }
    if ($page=="dhj_init") {
        echo "<div id='dhj_list'></div><div id='dhj_dia'></div>
        <script type='text/javascript'>jQuery(function(){

        jQuery('#dhj_list').dialog({width:jQuery(window).width()-20, height:jQuery(window).height()-15,autoOpen:false,modal:true,dialogClass:'modal-dialog', buttons: [
            {id: 'dhj_cancel', text: 'Закрыть', class: 'dialog-btn-orange', click: function() { jQuery(this).dialog('close'); location.reload(); } },
        ], });

        jQuery('#dhj_dia').dialog({width:600,height:625,autoOpen:false,modal:true,dialogClass:'modal-dialog', buttons: [
            {id: 'dhj_add', text: 'Добавить', class: 'dialog-btn-yellow', click: function() {
                data={}; jQuery('.dhj_vals').each(function(i) {data[jQuery(this).attr('id')]=jQuery(this).val();});
                jQuery.ajax({url: '/kanban/ajax/dhj_add/', type: 'POST', data: data,
                    success: function(answer){if (answer.substr(0,2)=='OK') { jQuery('#dhj_list').load('/kanban/load/dhj_list',{ }); jQuery('#dhj_dia').dialog('close'); } else {alert(answer);}}
                });
            } },
            {id: 'dhj_edit', text: 'Сохранить', class: 'dialog-btn-orange', click: function() {
                data={}; jQuery('.dhj_vals').each(function(i) {data[jQuery(this).attr('id')]=jQuery(this).val();});
                jQuery.ajax({url: '/kanban/ajax/dhj_edit/', type: 'POST', data: data,
                    success: function(answer){if (answer.substr(0,2)=='OK') { jQuery('#dhj_list').load('/kanban/load/dhj_list',{ }); jQuery('#dhj_dia').dialog('close'); } else {alert(answer);}}
                });
            } },
            {id: 'dhj_del', text: 'Удалить', class: 'dialog-btn-red', click: function() {
                if (confirm('Подтвердите удаление!')) jQuery.ajax({url: '/kanban/ajax/dhj_del/', type: 'POST', data: { dhj_id:jQuery('#dhj_id').val() },
                    success: function(answer){if (answer.substr(0,2)=='OK') { jQuery('#dhj_list').load('/kanban/load/dhj_list',{ }); jQuery('#dhj_dia').dialog('close'); } else {alert(answer);}}
                });
            } },
            {id: 'dhj_cancel', text: 'Отмена', class: 'dialog-btn-grey', click: function() { jQuery(this).dialog('close');} },
        ], });

        });</script>";
        return;
    }
    if ($page=="dhj_add") {
        echo "<div class='dialog-title'>Добавление вакансии</div>";
        self::kanban_job_blank();
        echo "<script type='text/javascript'>jQuery(function(){
            jQuery('#dhj_date').val('".date0(time())."');
        });</script>";
        return;
    }
    if ($page=="dhj_edit") {
        $dhj_id=(int)$_POST['dhj_id'];
        $resDhj=DoSql("SELECT * FROM dh_jobs WHERE dhj_id='{$dhj_id}'"); if ($rowDhj=$resDhj->fetch_assoc()) { $rowDhj['dhj_date']=date0($rowDhj['dhj_date']); } else { return; }
        echo "<div class='dialog-title'>Редактирование вакансии</div>";
        echo "<input id='dhj_id' class='dhj_vals hidden'>";
        self::kanban_job_blank();
        echo "<script type='text/javascript'>jQuery(function(){
            var data=".json_encode($rowDhj).";  jQuery('.dhj_vals').each(function (i) {jQuery(this).val(data[jQuery(this).attr('id')]);});
        });</script>";
        return;
    }
    echo "page={$page}?";
}
static function kanban_ajax($page) {
    function pack_img($file,$width,$height) {
        $size = getimagesize($file);
        $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
        $icfunc = "imagecreatefrom" . $format;
        if (function_exists($icfunc)) {
            $isrc = $icfunc($file);
            $x_ratio = $width / $size[0];$y_ratio = $height / $size[1];
            $ratio       = min($x_ratio, $y_ratio); $use_x_ratio = ($x_ratio == $ratio);
            $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
            $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
            $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
            $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
            $idest = imagecreatetruecolor($width, $height);
            imagefill($idest, 0, 0, 0xFFFFFF);
            imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]);
            imagejpeg($idest, $file, 80);
            imagedestroy($isrc); imagedestroy($idest);
            return true;
        } else { return false;}
    }
    if ($page=="login") {
        $login=stringForQuery(trim(post_get("login"))); $ok=0;
        if ($login!="") {
            $res=DoSql("SELECT * FROM dh_users WHERE dhu_login='{$login}'");
            if ($row=$res->fetch_assoc()) {
                $pass00=post_get("pass");
                $pass0=$row['dhu_pass'];
                //$pass0=encode(base64_decode($row['user_pass']),$GLOBALS['superpass']);
                if ($pass0==$pass00) { $ok=1;$user_id=$row['dhu_id']; $user_name=$row['dhu_name']; }
            }
        }
        /*авторизовано*/if ($ok==1) {
            $ts=gettimeofday(); $token=$ts['sec'].$ts['usec'].$user_id.rand();
            $val= array ("ses_token"=>$token,"ses_user_id"=>$user_id,"ses_user_ip"=>$_SERVER["REMOTE_ADDR"],"ses_open_ts"=>time());
            DoInsertQuery("dh_sessions",$val);
            setcookie("dh_user_token",$token,time()+360000,"/");
            echo "<div style='text-align:center;color:#008000;font-size:18px;'>Приветствуем, {$user_name},<br>Вы успешно прошли авторизацию!</div>";
            echo "<script type='text/javascript'>setTimeout(function(){location.reload();},2000);</script>";
        /*НЕавторизовано*/} else {
            echo "<div style='text-align:center;color:#FF0000;font-size:18px;'>Неверный логин или пароль!</div>";
            echo "<script type='text/javascript'>setTimeout(function(){location.reload();},3000);</script>";
        }
        return;
    }
    if ($page=="exit") {
        setcookie("dh_user_token",null,-1,"/");
        echo "<script type='text/javascript'>setTimeout(function(){location.reload();},10);</script>";
        return;
    }
    if ($page=="save_cur_dhj_id") {
        DoUpdateQuery("dh_users","dhu_id",$GLOBALS['dhu_id'],array('dhu_dhj_id'=>(int)$_POST['cur_dhj_id']));
        return;
    }
    if ($page=="save_dhr_dhs_id") {
        DoUpdateQuery("dh_resumes","dhr_id",(int)$_POST['dhr_id'],array('dhr_dhs_id'=>(int)$_POST['dhs_id']));
        echo "OK"; return;
    }
    if ($page=="dhr_add") {
        $_POST['dhr_date']=strtotime($_POST['dhr_date']);
        $_POST['dhr_dhu_id']=$GLOBALS['dhu_id'];
        $_POST['dhr_dhs_id']=1;
        if(isset($_POST['dhr_photo_tmp']))  {
            $dhr_photo_tmp=$_POST['dhr_photo_tmp']; unset($_POST['dhr_photo_tmp']);
            if ($dhr_photo_tmp!="") { $filetmpfull=$GLOBALS['bof']."tmp/".$dhr_photo_tmp; if (file_exists($filetmpfull)) { $_POST['dhr_photo']=file_get_contents($filetmpfull); unlink($filetmpfull);} }
        }
        $dhr_id=DoInsertQuery("dh_resumes",$_POST);
        /*обрабатываем прикрепленные файлы*/
        $box=date("Y",$_POST['dhr_date']);
        $dhr_res_files=updateFiles("res".$dhr_id,"docs",$box,$_POST['dhr_res_files'],"");
        $dhr_scr_files=updateFiles("scr".$dhr_id,"docs",$box,$_POST['dhr_scr_files'],"");
        $dhr_view_files=updateFiles("view".$dhr_id,"docs",$box,$_POST['dhr_view_files'],"");
        $dhr_sb_files=updateFiles("sb".$dhr_id,"docs",$box,$_POST['dhr_sb_files'],"");
        $dhr_offer_files=updateFiles("offer".$dhr_id,"docs",$box,$_POST['dhr_offer_files'],"");
        DoUpdateQuery("dh_resumes",'dhr_id',$dhr_id,array('dhr_res_files'=>$dhr_res_files,'dhr_scr_files'=>$dhr_scr_files,'dhr_view_files'=>$dhr_view_files,'dhr_sb_files'=>$dhr_sb_files,'dhr_offer_files'=>$dhr_offer_files));
        echo "OK"; return;
    }
    if ($page=="dhr_edit") {
        $dhr_id=$_POST['dhr_id']; unset($_POST['dhr_id']);
        $resDhr=DoSql("SELECT * FROM dh_resumes WHERE dhr_id='{$dhr_id}'"); if ($rowDhr=$resDhr->fetch_assoc()) {  } else { return; }
        $_POST['dhr_date']=strtotime($_POST['dhr_date']);
        if(isset($_POST['dhr_photo_tmp']))  {
            $dhr_photo_tmp=$_POST['dhr_photo_tmp']; unset($_POST['dhr_photo_tmp']);
            if ($dhr_photo_tmp!="") { $filetmpfull=$GLOBALS['bof']."tmp/".$dhr_photo_tmp; if (file_exists($filetmpfull)) { $_POST['dhr_photo']=file_get_contents($filetmpfull); unlink($filetmpfull);} }
        }
        /*обрабатываем прикрепленные файлы*/
        $box=date("Y",$_POST['dhr_date']);
        $_POST['dhr_res_files']=updateFiles("res".$dhr_id,"docs",$box,$_POST['dhr_res_files'],$rowDhr['dhr_res_files']);
        $_POST['dhr_scr_files']=updateFiles("scr".$dhr_id,"docs",$box,$_POST['dhr_scr_files'],$rowDhr['dhr_scr_files']);
        $_POST['dhr_view_files']=updateFiles("view".$dhr_id,"docs",$box,$_POST['dhr_view_files'],$rowDhr['dhr_view_files']);
        $_POST['dhr_sb_files']=updateFiles("sb".$dhr_id,"docs",$box,$_POST['dhr_sb_files'],$rowDhr['dhr_sb_files']);
        $_POST['dhr_offer_files']=updateFiles("offer".$dhr_id,"docs",$box,$_POST['dhr_offer_files'],$rowDhr['dhr_offer_files']);
        /*сохраняем*/
        DoUpdateQuery("dh_resumes","dhr_id",$dhr_id,$_POST);
        echo "OK"; return;
    }
    if ($page=="dhr_del") {
        $dhr_id=$_POST['dhr_id'];
        DoDeleteQuery("dh_resumes","dhr_id",$dhr_id);
        echo "OK"; return;
    }
    if ($page=="save_photo") {
        $fn="dhr_photo_file";
        if (($_FILES[$fn]["type"] == "image/gif") || ($_FILES[$fn]["type"] == "image/png") || ($_FILES[$fn]["type"] == "image/jpeg")) {
            if ($_FILES[$fn]["error"] > 0) {
                echo "
                <script language=\"JavaScript\" type=\"text/javascript\">
                parent.document.getElementById('dhr_photo_img').innerHTML=\"<img width=200px src='/kanban/load/dhr_photo/' border=0>\";
                parent.document.getElementById('dhr_photo_rez').innerHTML=\"<div class=empty>Ошибка [{$_FILES[$fn]["error"]}]!</div>\";
                parent.document.getElementById('dhr_photo_file').value=\"\";parent.document.getElementById('dhr_photo_tmp').value=\"\";
                </script>";
            } else {
                echo "Upload: ".$_FILES[$fn]["name"]."<br>Type: ".$_FILES[$fn]["type"]."<br>Size: ".($_FILES[$fn]["size"] / 1024)." Kb<br>";
                $filename="IMG{$GLOBALS['dhu_id']}_".rand().".jpg";
                $bof=$GLOBALS['bof']."tmp/";
                if (is_dir($bof)) {} else {mkdir($bof);}
                if (is_dir($bof)) {} else { echo "
                    <script language=\"JavaScript\" type=\"text/javascript\">
                    parent.document.getElementById('dhr_photo_img').innerHTML=\"<img width=200px src='/kanban/load/dhr_photo/' border=0>\";
                    parent.document.getElementById('dhr_photo_rez').innerHTML=\"<div class=empty>Ошибка создания каталога [$bof]!</div>\";
                    parent.document.getElementById('dhr_photo_file').value=\"\";parent.document.getElementById('dhr_photo_tmp').value=\"\";
                    </script>";return;}
                move_uploaded_file($_FILES[$fn]["tmp_name"], $bof.$filename);
                echo "Saved: ".$bof.$filename."<br>";
                if (pack_img($bof.$filename,200,275)) {
                    echo "Packed: OK!<br>
                        <script language=\"JavaScript\" type=\"text/javascript\">
                        parent.document.getElementById('dhr_photo_img').innerHTML=\"<img style='width:200px;height:auto;' src='/kanban/load/photo_tmp/?filename={$filename}'>\";
                        parent.document.getElementById('dhr_photo_rez').innerHTML=\"\";
                        parent.document.getElementById('dhr_photo_tmp').value=\"$filename\";
                        </script>
                    ";
                } else {  echo "
                    <script language=\"JavaScript\" type=\"text/javascript\">
                    parent.document.getElementById('dhr_photo_img').innerHTML=\"<img width=200px src='/kanban/load/dhr_photo/' border=0>\";
                    parent.document.getElementById('dhr_photo_rez').innerHTML=\"<div class=empty>Ошибка сжатия изображения!</div>\";
                    parent.document.getElementById('dhr_photo_file').value=\"\";parent.document.getElementById('dhr_photo_tmp').value=\"\";
                    </script>";return;
                }
            }
        } else { echo "
            <script language=\"JavaScript\" type=\"text/javascript\">
            parent.document.getElementById('dhr_photo_img').innerHTML=\"<img width=200px src='/kanban/load/dhr_photo/' border=0>\";
            parent.document.getElementById('dhr_photo_rez').innerHTML=\"<div class=empty>Ошибка! Попытка загрузить файл другого формата [{$_FILES[$fn]["type"]}].<hr>Разрешено загружать файлы jpg, gif, png.</div>\";
            parent.document.getElementById('v').value=\"\";parent.document.getElementById('dhr_photo_tmp').value=\"\";
            </script>";
        }
        return;
    }
    if ($page=="upload") {
        $prefix=$GLOBALS['page1'];
        $file0=$_FILES[$prefix."_files_file"];
        if ($file0["error"] > 0) {
            echo "<script type=\"text/javascript\">
                parent.document.getElementById('".$prefix."_files_new').innerHTML='<div class=empty>Ошибка загрузки [".$file0["error"]."]</div>';
            </script>";
        } else {
            $bof=$GLOBALS['bof']."tmp/"; $filename="tmp{$GLOBALS['dhu_id']}".rand().".".pathinfo($file0['name'], PATHINFO_EXTENSION);
            if (!is_dir($bof) && !mkdir($bof)) {echo "<script type=\"text/javascript\">
                parent.document.getElementById('".$prefix."_files_new').innerHTML='<div class=empty>Ошибка загрузки [Нет прав по созданию каталога $bof]</div>';
                </script>";return;
            }
            if (move_uploaded_file($file0['tmp_name'], $bof.$filename)) {
                $fsize=round(($file0["size"] / 1024),1);
                $title=str_replace("'","`",$file0["name"]);
                echo "<script type=\"text/javascript\">
                    parent.document.getElementById('".$prefix."_files_new').innerHTML+='<div class=dialog-upload>$title ($fsize кБ) </div>';
                    parent.document.getElementById('".$prefix."_files_waiting').innerHTML='<img src=/files/css/ok.png style=\'width:auto;height:17px;\'>';
                    parent.document.getElementById('".$prefix."_files').value=parent.document.getElementById('".$prefix."_files').value+'+$filename';   </script>";return;
            } else {
                echo "<script type=\"text/javascript\">
                parent.document.getElementById('".$prefix."_files_new').innerHTML='<div class=empty>Ошибка загрузки [Нет прав по созданию файла $bof$filename]</div>';
                </script>";return;
            }
        }; return;
    }
    if ($page=="dhj_add") {
        $_POST['dhj_date']=strtotime($_POST['dhj_date']);
        $_POST['dhj_dhu_id']=$GLOBALS['dhu_id'];
        $_POST['dhj_status']=1;
        DoInsertQuery("dh_jobs",$_POST);
        echo "OK"; return;
    }
    if ($page=="dhj_edit") {
        $dhj_id=$_POST['dhj_id']; unset($_POST['dhj_id']);
        $_POST['dhj_date']=strtotime($_POST['dhj_date']);
        DoUpdateQuery("dh_jobs","dhj_id",$dhj_id,$_POST);
        echo "OK"; return;
    }
    if ($page=="dhj_del") {
        $dhj_id=$_POST['dhj_id']; $k1=0;
        $res=DoSql("SELECT COUNT(*) as k1 FROM dh_resumes WHERE dhr_dhj_id='{$dhj_id}'"); $row=$res->fetch_assoc(); $k1+=$row['k1'];
        if ($k1==0) { DoDeleteQuery("dh_jobs","dhj_id",$dhj_id); echo "OK"; return;} else {echo "Удаление запрещено!"; return;}
    }
    if ($page=="dhj_status") {
        $dhj_id=$_POST['dhj_id'];
        $resDhj=DoSql("SELECT * FROM dh_jobs WHERE dhj_id='{$dhj_id}'"); if ($rowDhj=$resDhj->fetch_assoc()) {  } else { return; }
        DoUpdateQuery("dh_jobs","dhj_id",$dhj_id,array('dhj_status'=>!$rowDhj['dhj_status']));
        echo "OK"; return;
    }
    echo "ct={$page}?";
}
static function kanban_resume_blank() {
    $resDhs=DoSql("SELECT dhs_id, dhs_name FROM dh_statuses ORDER BY dhs_id");
    $dhs_options="";while ($rowDhs=$resDhs->fetch_assoc()) {$dhs_options.="<option value='{$rowDhs['dhs_id']}'>{$rowDhs['dhs_name']}</option>";}
    echo "<div style='display:flex; justify-content:space-between;'>
        <div style='width:200px;'><div id='dhr_photo_img'></div>
            <center><div id='btn_add_photo' class='btn-green-mini' style='width:175px;'>Загрузить фото</div></center>
            <form id='dhr_photo_form' target='dhr_photo_frame' method = 'post' action='/kanban/ajax/save_photo/' enctype='multipart/form-data' style='display:none;'>
                <input type='file' name='dhr_photo_file' id='dhr_photo_file' value=''>
            </form>
            <iframe id='dhr_photo_frame' name='dhr_photo_frame' style='display:none;width:200px;'></iframe>
            <div id=dhr_photo_rez></div>
            <input type='hidden' id='dhr_photo_tmp' value='' class='dhr_vals'>
        </div>
        <div style='margin-top:7px;width:550px;'>
            <div style='display:flex; justify-content:space-between;'>
                <div style='width:33%;'><div class='dialog-label'>Фамилия</div><input id='dhr_name1' class='dhr_vals dialog-input'></div>
                <div style='width:33%;'><div class='dialog-label'>Имя</div><input id='dhr_name2' class='dhr_vals dialog-input'></div>
                <div style='width:33%;'><div class='dialog-label'>Отчество</div><input id='dhr_name3' class='dhr_vals dialog-input' style='width:97%;'></div>
            </div>
            <div style='margin-top:7px;display:flex; justify-content:space-between;'>
                <div style='width:33%;'><div class='dialog-label'>Пол</div><select id='dhr_gender' class='dhr_vals dialog-input' style='height:31px;width:100%;'><option value='М'>Мужской</option><option value='Ж'>Женский</option></select></div>
                <div style='width:33%;'><div class='dialog-label'>Возраст, лет</div><input id='dhr_age' class='dhr_vals dialog-input'></div>
                <div style='width:33%;'><div class='dialog-label'>Стаж работы, лет</div><input id='dhr_experience' class='dhr_vals dialog-input' style='width:97%;'></div>
            </div>
            <div class='dialog-label' style='margin-top:7px;'>Образование</div><input id='dhr_education' class='dhr_vals dialog-input'>
            <div class='dialog-label' style='margin-top:7px;'>Дополнительная информация</div><textarea id='dhr_description' class='dhr_vals dialog-input' style='height:73px;'></textarea>
            <div style='margin-top:7px;display:flex; justify-content:space-between;'>
                <div style='width:50%;'><div class='dialog-label'>Статус</div><select id='dhr_dhs_id' class='dhr_vals dialog-input' style='height:32px;width:100%;'>{$dhs_options}</select></div>
                <div style='width:49%;'><div class='dialog-label'>Дата резюме</div><input id='dhr_date' class='dhr_vals dialog-input' style='width:98%;' value='".date0(time())."'></div>
            </div>
        </div>
    </div>
    <div style='display:flex; justify-content:space-between;'>
      <div style='width:49%;'>
        <div class='dialog-label'>Файлы резюме и личных документов</div><input id='dhr_res_files' type='hidden' class='dhr_vals'>
        <div class='dialog-input' field_files='dhr_res_files' style='display:flex;align-items:center;height:32px;'>
            <div id='dhr_res_files_links' style='display:flex;'></div><div id='dhr_res_files_new' style='display:flex;flex-grow:2;'></div>
            <div id='dhr_res_files_waiting'></div><div id='dhr_res_files_add' class='btn-green-mini'>Присоединить</div>
        </div>
      </div>
      <div style='width:50%;'>
        <div class='dialog-label'>Файлы результатов скрининга</div><input id='dhr_scr_files' type='hidden' class='dhr_vals'>
        <div class='dialog-input' field_files='dhr_scr_files' style='display:flex;align-items:center;height:32px;'>
            <div id='dhr_scr_files_links' style='display:flex;'></div><div id='dhr_scr_files_new' style='display:flex;flex-grow:2;'></div>
            <div id='dhr_scr_files_waiting'></div><div id='dhr_scr_files_add' class='btn-green-mini'>Присоединить</div>
        </div>
      </div>
    </div>
    <div style='display:flex; justify-content:space-between;'>
      <div style='width:49%;'>
        <div class='dialog-label'>Файлы результатов интервью</div><input id='dhr_view_files' type='hidden' class='dhr_vals'>
        <div class='dialog-input' field_files='dhr_view_files' style='display:flex;align-items:center;height:32px;'>
            <div id='dhr_view_files_links' style='display:flex;'></div><div id='dhr_view_files_new' style='display:flex;flex-grow:2;'></div>
            <div id='dhr_view_files_waiting'></div><div id='dhr_view_files_add' class='btn-green-mini'>Присоединить</div>
        </div>
      </div>
      <div style='width:50%;'>
        <div class='dialog-label'>Файлы результатов проверки СБ</div><input id='dhr_sb_files' type='hidden' class='dhr_vals'>
        <div class='dialog-input' field_files='dhr_sb_files' style='display:flex;align-items:center;height:32px;'>
            <div id='dhr_sb_files_links' style='display:flex;'></div><div id='dhr_sb_files_new' style='display:flex;flex-grow:2;'></div>
            <div id='dhr_sb_files_waiting'></div><div id='dhr_sb_files_add' class='btn-green-mini'>Присоединить</div>
        </div>
      </div>
    </div>
    <div style='display:flex; justify-content:space-between;'>
      <div style='width:49%;'>
        <div class='dialog-label'>Файлы оффера</div><input id='dhr_offer_files' type='hidden' class='dhr_vals'>
        <div class='dialog-input' field_files='dhr_offer_files' style='display:flex;align-items:center;height:32px;'>
            <div id='dhr_offer_files_links' style='display:flex;'></div><div id='dhr_offer_files_new' style='display:flex;flex-grow:2;'></div>
            <div id='dhr_offer_files_waiting'></div><div id='dhr_offer_files_add' class='btn-green-mini'>Присоединить</div>
        </div>
      </div>
      <div style='width:50%;'>
        <div class='dialog-label'>Выведен на проект</div>
        <div class='dialog-input' style='display:flex;justify-content:space-around;align-items:center;height:32px;'>
            <input id='dhr_projbox' type='checkbox'>
            <input id='dhr_projtext' class='dhr_vals dialog-input' style='width:93%;border:none;'>
        </div>
      </div>
    </div>

    <script type='text/javascript'>jQuery(function(){
        jQuery('#dhr_date').datepicker({firstDay: 1,dateFormat: 'dd.mm.yy'});
        jQuery('#btn_add_photo').on('click',function(){ jQuery('#dhr_photo_file').click(); });
        jQuery('#dhr_photo_file').on('change', function(){ jQuery('#dhr_photo_img').html('".WAITING."'); jQuery('#dhr_photo_form').submit();  });
    });</script>";
}
static function kanban_job_blank() {
    echo "
     <div style='margin-top:7px;display:flex; justify-content:space-between;'>
        <div style='width:50%;'><div class='dialog-label' style='margin-top:7px;'>Вакансия</div><input id='dhj_name' class='dhj_vals dialog-input'></div>
        <div style='width:50%;'><div class='dialog-label' style='margin-top:7px;'>Дата вакансии</div><input id='dhj_date' class='dhj_vals dialog-input'></div>
     </div>
     <div style='margin-top:7px;display:flex; justify-content:space-between;'>
        <div style='width:50%;'><div class='dialog-label' style='margin-top:7px;'>Зарплата от</div><input id='dhj_salary_min' class='dhj_vals dialog-input'></div>
        <div style='width:50%;'><div class='dialog-label' style='margin-top:7px;'>Зарплата до</div><input id='dhj_salary_max' class='dhj_vals dialog-input'></div>
     </div>
     <div style='margin-top:7px;display:flex; justify-content:space-between;'>
        <div style='width:50%;'><div class='dialog-label' style='margin-top:7px;'>Режим работы</div><input id='dhj_whours' class='dhj_vals dialog-input'></div>
        <div style='width:50%;'><div class='dialog-label' style='margin-top:7px;'>Опыт работы</div><input id='dhj_experience' class='dhj_vals dialog-input'></div>
     </div>
     <div class='dialog-label' style='margin-top:7px;'>Обязанности</div><textarea id='dhj_duties' class='dhj_vals dialog-input' style='height:73px;'></textarea>
     <div class='dialog-label' style='margin-top:7px;'>Требования</div><textarea id='dhj_requirements' class='dhj_vals dialog-input' style='height:73px;'></textarea>
     <div class='dialog-label' style='margin-top:7px;'>Условия</div><textarea id='dhj_conditions' class='dhj_vals dialog-input' style='height:73px;'></textarea>
    <script type='text/javascript'>jQuery(function(){
        jQuery('#dhj_date').datepicker({firstDay: 1,dateFormat: 'dd.mm.yy'});
    });</script>";
}
}

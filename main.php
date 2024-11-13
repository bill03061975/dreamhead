<?php /* dreamhead | main.php */
defined('_BEXEC') or die;
{
    $GLOBALS['token_life']=3600;
    define("WAITING", "<div style=\'display:flex;justify-content:space-around;align-items:center;height:100%;\'><img src=/files/css/waiting.gif style=\'width:50px;height:50px;\'></div>");
    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ ОПЦИИ
    if (isset($_GET['option'])) {$option=$_GET['option'];} else {$option="";}
    if (!isset($option)) {$option="";} $option_parts=explode("/",$option);

    /*конфигурация*/ if (1==1) {
        /*хост*/$conf="/home/bingosites/.host/.host.conf.php";if (file_exists($conf)) {require_once ($conf);}
        /*сервис*/$conf="config.php";if (file_exists($conf)) {require_once ($conf);}
    }
    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ МОДУЛЬ
    if (count($option_parts)>0) { $module=stringForQuery($option_parts[0]);} else {$module="kanban";}
    if ($module=="") {$module='kanban';}

    $GLOBALS['module']=$module;

    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ РАЗДЕЛ МОДУЛЯ
    if (count($option_parts)>1) { $unit=$option_parts[1];} else {$unit="main";}
    if ($unit=="") {$unit='main';}
    $GLOBALS['unit']=stringForQuery($unit);
    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ СТРАНИЦУ РАЗДЕЛ МОДУЛЯ
    if (count($option_parts)>2) { $page=$option_parts[2];} else {$page="mainpage";}
    if ($page=="") {$page='mainpage';}
    $GLOBALS['page']=stringForQuery($page);
    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ СТРАНИЦУ1 РАЗДЕЛ МОДУЛЯ
    if (count($option_parts)>3) {$GLOBALS['page1']=stringForQuery($option_parts[3]);} else {$GLOBALS['page1']="";}
    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ СТРАНИЦУ2 РАЗДЕЛ МОДУЛЯ
    if (count($option_parts)>4) {$GLOBALS['page2']=stringForQuery($option_parts[4]);} else {$GLOBALS['page2']="";}
    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ СТРАНИЦУ3 РАЗДЕЛ МОДУЛЯ
    if (count($option_parts)>5) {$GLOBALS['page3']=stringForQuery($option_parts[5]);} else {$GLOBALS['page3']="";}

    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ ДЕЙСТВИЕ
    if (isset($_GET['ct'])) {$action=stringForQuery($_GET['ct']);} else {$action="default";}
    if ($action=="") {$action='default';}
    $GLOBALS['action']=$action;
    // ----------------------------------------------------------------- ОПРЕДЕЛЯЕМ РЕЖИМ ВЫВОДА
    if (isset($_GET['output'])) {$output=stringForQuery($_GET['output']);} else {$output="display";}
    if ($output=="") {$output='display';}
    $GLOBALS['mode']=$output;

    // ----------------------------------------------------------------- подключение рабочего модуля
    $modphp="{$GLOBALS['module']}.php";
    if (file_exists($modphp)) {require_once ($modphp); } else {echoError404();}
    if (method_exists($GLOBALS['module'], "selector")) {eval ($GLOBALS['module']."::selector();");}

}
function echoError404() {
    header("HTTP/1.0 404 Not Found"); header("HTTP/1.1 404 Not Found"); header("Status: 404 Not Found");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
    <html><head>
    <title>404 Not Found</title>
    </head><body>
    <center><h1>404 Not Found!</h1>
    <center><a href=/>Back</a>
    <script type='text/javascript'>
        location.assign('/');
    </script>
    </body></html>";
    die;
}
function stringForQuery($str) {
    $mysqli=InitDB(); if (!is_object($mysqli)) return null;
    $result=mysqli_real_escape_string($mysqli,$str);
    $mysqli->close();
    $result=str_replace("<","",$result);$result=str_replace(">","",$result);
    return $result;
} // stringForQuery
function date0($ts) {
    if ($ts==0) {return "";} else {return date("d.m.Y",$ts);}
}
function my_error_handler($code, $msg, $file, $line) {
    if (strstr($msg, "ZipArchive::extractTo()")) { return;}
    if (strstr($msg, "HTTP request failed")) {
        echo "<div class=empty>INET ERROR</div>";  save_error($code, $msg, $file, $line); return;}
    echo "<div class=empty>ERROR <br>$msg ($code)<br>$file ($line)</div>";
    //save_error($code, $msg, $file, $line);
} // my_error_handler
function ExtractSubStr ($begin, $end, $str, $default="") {
/*  ====================================================================
 *  Извлечение из строки str подстроки между фразами begin и end
 *  ====================================================================*/
    if ($begin=="") {
        $part1=$str;
    } else {
        $part1=explode($begin,$str);
        if (count($part1)==1) {
            return $default;
        } else {
            $part1=$part1[1];
        }
    }
    if ($end=="") {
        $part2=$part1;
    } else {
        $part2=explode($end,$part1);
        if (count($part2)==1) {
            return $default;
        } else {
            $part2=$part2[0];
        }
    }
    return $part2;
}
function url ($action="", $module="") {
    if ($module=="") { $module=$GLOBALS['module'];  }
    $out= "/".$module."/".$action;
    return $out;
} // url
function inStr ($needle, $haystack) {
  $needlechars = strlen($needle); //gets the number of characters in our needle
  $i = 0;
  for($i=0; $i < strlen($haystack); $i++) //creates a loop for the number of characters in our haystack
  {
    if(substr($haystack, $i, $needlechars) == $needle) //checks to see if the needle is in this segment of the haystack
    {
      return TRUE; //if it is return true
    }
  }
  return FALSE; //if not, return false
}
function Error ($msg) {
    echo "<div class=error>$msg</div> \n";
}
function InitDB($db="") {
    $parts=explode("-",$db); $db=$parts[0]; if (isset($parts[1])) {$host=$parts[1];} else {$host=$GLOBALS['dbhost'];}
    if ($db=="") {$db=$GLOBALS['dbname'];}  $user=$GLOBALS['dbuser']; $pass=$GLOBALS['dbpassword'];
    @ $mysqli = new mysqli($host, $user, $pass, $db);
    if (mysqli_connect_errno()) {
        Error("Невозможно подключиться к базе данных. Код ошибки:".mysqli_connect_error());
        return null;
    }
    return $mysqli;
} //InitMSQLi
function DoSql ($sql) {
    $mysqli=InitDB(); if (!is_object($mysqli)) return null;
    $mysqli->query("SET NAMES utf8");
    $result=$mysqli->query($sql);
    if (mysqli_errno($mysqli)) {
        $bt=debug_backtrace(); $bt=$bt[0]; my_error_handler("MYSQLi:".mysqli_errno($mysqli), mysqli_error($mysqli).">>".$sql, $bt['file'], $bt['line']);
        return null;$mysqli->close();
    }
    return $result;
} // DoSqlMSQLi
function GetInsertQuery ($table, $values=array()) {
    $fields_str="";
    $values_str="";
    foreach ($values as $field => $value) {
        $value = get_magic_quotes_gpc() ? stripslashes($value) : $value;
        $value=addslashes($value);
        $fields_str.="$field,";
        $values_str.="'$value',";
    }
    $fields_str=substr($fields_str, 0, -1);
    $values_str=substr($values_str, 0, -1);
    $qry_str="INSERT INTO `$table` ($fields_str) VALUES ($values_str)";
    return $qry_str;
} // GetInsertQuery
function DoInsertQuery ($table, $values=array()) {
    $mysqli=InitDB(); if (!is_object($mysqli)) return null;
    $mysqli->query("SET NAMES utf8");
    $sql=GetInsertQuery ($table, $values);
    $result=$mysqli->query($sql);
    if (mysqli_errno($mysqli)) {
        $bt=debug_backtrace(); $bt=$bt[0]; my_error_handler("MYSQLi:".mysqli_errno($mysqli), mysqli_error($mysqli).">>".$sql, $bt['file'], $bt['line']);
        return null;$mysqli->close();
    }
    $id=mysqli_insert_id ($mysqli);
    $mysqli->close();
    return $id;
} // DoInsertQuery
function GetUpdateQuery ($table, $key, $keyvalue, $values=array()) {
    $set_str="";
    foreach ($values as $field => $value) {
        $value = get_magic_quotes_gpc() ? stripslashes($value) : $value;
        $value=addslashes($value);
        $set_str.="$field='$value',";
    }
    $set_str=substr($set_str, 0, -1);
    $qry_str="UPDATE $table SET $set_str WHERE $key='$keyvalue'";
    return $qry_str;
} // GetUpdateQuery
function DoUpdateQuery ($table, $key, $keyvalue, $values=array()) {
    $mysqli=InitDB(); if (!is_object($mysqli)) return null;
    $mysqli->query("SET NAMES utf8");
    $sql=GetUpdateQuery ($table, $key, $keyvalue, $values);
    $result=$mysqli->query($sql);
    if (mysqli_errno($mysqli)) {
        $bt=debug_backtrace(); $bt=$bt[0]; my_error_handler("MYSQLi:".mysqli_errno($mysqli), mysqli_error($mysqli).">>".$sql, $bt['file'], $bt['line']);
        return null;$mysqli->close();
    }
    $mysqli->close();
    return;
} // DoUpdateQuery
function GetDeleteQuery ($table, $key, $keyvalue) {
    $qry_str="DELETE FROM $table WHERE $key='$keyvalue'";
    return $qry_str;
} // GetDeleteQuery
function DoDeleteQuery ($table, $key, $keyvalue) {
    $mysqli=InitDB(); if (!is_object($mysqli)) return null;
    $mysqli->query("SET NAMES utf8");
    $sql=GetDeleteQuery ($table, $key, $keyvalue);
    $result=$mysqli->query($sql);
    if (mysqli_errno($mysqli)) { // обнаружена ошибка
        $errno=mysqli_errno($mysqli);
        if ($errno!=1451) {$bt=debug_backtrace(); $bt=$bt[0]; my_error_handler("MYSQLi:".$errno, mysqli_error($mysqli).">>".$sql, $bt['file'], $bt['line']);}
        $mysqli->close(); return $errno;
    }
    $mysqli->close(); return 0;
} // DoDeleteQuery
function forHtml ($value) {
    $value=str_replace("\n"," ",$value);
    $value=str_replace("\r"," ",$value);
    $value=str_replace("'","'",$value);
    return htmlspecialchars($value, ENT_QUOTES);
} // forHtml
function bf($val,$dec) {
    if ($val==0) {return "";}
    $val=number_format($val,$dec,'.',''); $val=rtrim(rtrim($val, '0'), '.'); if ($val=="-0") $val="";
    return $val;
} // bf
function post_get($f) {
    if (isset($_POST[$f])) {$out=$_POST[$f];unset($_POST[$f]);} else {if (isset($_GET[$f])) {$out=$_GET[$f];unset($_GET[$f]);} else {$out="";}}
    return $out;
}
function get_img($str,$height=26,$padding=3) {
    $ext=pathinfo($str, PATHINFO_EXTENSION);
    if ($ext=="pdf" || $ext=="PDF") {return "<img src=/files/css/pdf.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="jpg" || $ext=="JPG") {return "<img src=/files/css/jpg.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="png" || $ext=="PNG") {return "<img src=/files/css/png.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="txt" || $ext=="TXT") {return "<img src=/files/css/txt.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="doc" || $ext=="DOC" || $ext=="docx" || $ext=="DOCX") {return "<img src=/files/css/doc.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="xls" || $ext=="XLS" || $ext=="xlsx" || $ext=="XLSX") {return "<img src=/files/css/xls.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="ppt" || $ext=="PPT" || $ext=="pptx" || $ext=="PPTX") {return "<img src=/files/css/ppt.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="zip" || $ext=="ZIP") {return "<img src=/files/css/zip.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="rar" || $ext=="RAR") {return "<img src=/files/css/rar.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="mp3" || $ext=="MP3") {return "<img src=/files/css/mp3.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="avi" || $ext=="AVI") {return "<img src=/files/css/avi.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    if ($ext=="csv" || $ext=="CSV") {return "<img src=/files/css/csv.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";}
    return "<img src=/files/css/file.png style=\'width:auto;height:{$height}px;padding:{$padding}px;\'>";
}
function files2html($cont,$box,$files,$height,$padding){
    $html="";
    $f_parts=explode("+",$files);$n=0;
    foreach ($f_parts as $fn) {
        if ($fn!="") {
            $ffn=$GLOBALS['bof'].$cont."/".$box."/".$fn;$n++;
            if (file_exists($ffn)) {
                $html.=get_img($fn,$height,$padding);
                //$html.="<a style=\'cursor:pointer;\' target=_blank href=\'/kanban/load/file/?fc=$cont&fb=$box&fn=$fn\'>".get_img($fn)."</a>";
            } else {
               continue;
            }
        }
    }
    return $html;
}
function getFilesAjax($cont,$box,$files,$prefix){
    $links=" ";$files_new="";
    $f_parts=explode("+",$files);$n=0;
    foreach ($f_parts as $fn) {
        if ($fn!="") {
            $ffn=$GLOBALS['bof'].$cont."/".$box."/".$fn;$n++;
            if (file_exists($ffn)) {
                $files_new.=$fn."+";$ext=strtoupper(pathinfo($ffn, PATHINFO_EXTENSION));$fsize=round((filesize($ffn)/1024),1);
                $links.="<a  class=\'tooltip\' id=\'".$prefix."_files_$n\' style=\'cursor:pointer;\' target=_blank href=\'/kanban/load/file/?fc=$cont&fb=$box&fn=$fn\'>".get_img($fn).
                "<span>Формат файла - $ext<br>Размер - $fsize кБ<br>".
                "<aa class=\'".$prefix."_files_del\' idd=\'".$prefix."_files_$n\' fn=\'$fn\'>Удалить файл</aa></span></a>";
            } else {
               continue;
            }
        }
    }
    if ($links=="") {$links.="<div class=empty style='font-size:18px;height:25px;'>—</div>";}
    if ($files_new!="") {$files_new=substr($files_new, 0, -1);}
    $out="
    <form id='".$prefix."_files_form'  target='".$prefix."_files_frame' method = 'post' action='/kanban/ajax/upload/{$prefix}/' enctype='multipart/form-data'>
        <input type='file' name='".$prefix."_files_file' id='".$prefix."_files_file' value='' style='display:none;'>
    </form>
    <iframe id='".$prefix."_files_frame' name='".$prefix."_files_frame' style='display:none;width:200px;'></iframe>

    <script type=\"text/javascript\">
    jQuery(function(){
        jQuery('#".$prefix."_files').val('".$files_new."');
        jQuery('#".$prefix."_files_links').html('".$links."');
        jQuery('.".$prefix."_files_del').on('click',function(){
            var files=jQuery('#".$prefix."_files').val(); files=files.replace(new RegExp(jQuery(this).attr('fn'),'g'),'');
            jQuery('#".$prefix."_files').val(files);jQuery('#'+jQuery(this).attr('idd')).hide('slow');
        });
        jQuery('#".$prefix."_files_add').on('click',function(){jQuery('#".$prefix."_files_file').click();});
        jQuery('#".$prefix."_files_file').on('change', function(){
            jQuery('#".$prefix."_files_waiting').html('<center><img width=26 src=/files/css/waiting9.gif>');
            jQuery('#".$prefix."_files_form').submit();  });
    });</script>\n";
    return $out;
} // getFilesAjax
function updateFiles($id,$cont,$box,$files,$files_old) {
    $bof=$GLOBALS['bof'].$cont."/".$box."/";
    $files_new="";$fff=1;
    $f_parts=explode("+",$files);
    foreach ($f_parts as $fn) {
        if ($fn!="" && substr($fn, 0, 3)!="tmp") {
            $n=ExtractSubStr("_",".",$fn,0);$n=(int)$n;if ($n>=$fff) {$fff=$n+1;}
        }
    }
    foreach ($f_parts as $fn) { // Обрабатываем новые файлы
        if ($fn!="") {
            if (substr($fn, 0, 3)=="tmp") {
               $ffn=$GLOBALS['bof']."tmp/".$fn;
                if (file_exists($ffn)) {
                    if (is_dir($bof)) {} else {mkdir($bof);}
                    if (is_dir($bof)) {} else {echo "Нет прав по созданию каталога $bof";return;}
                    $filenew=$id."_".$fff.".".pathinfo($fn, PATHINFO_EXTENSION);
                    copy($ffn, $bof.$filenew); unlink($ffn);
                    $files_new.=$filenew."+";$fff++;
                } else {echo "Не найдено файл $ffn";return;}
            } else {
               $files_new.=$fn."+"; $files_old=str_replace($fn,"",$files_old);
            }
        }
    }
    $files_new=substr($files_new, 0, -1);
    $f_parts=explode("+",$files_old);
    $bofr=$GLOBALS['bof'].$cont."/recycle/";if (is_dir($bofr)) {} else {mkdir($bofr);}
    foreach ($f_parts as $fn) {
        if ($fn!="") {
            $ffn=$bof.$fn;
            if (file_exists($ffn)) {rename($ffn, $bofr.$fn);}
        }
    }
    return $files_new;
}
?>

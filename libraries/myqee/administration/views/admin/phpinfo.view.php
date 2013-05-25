<style type="text/css">
.phpinfo{background-color: #ffffff; color: #000000;padding:6px 8px;}
.phpinfo,.phpinfo td,.phpinfo th,.phpinfo h1,.phpinfo h2 {font-family:"Lucida Grande",Tahoma,Arial,sans-serif;}
.phpinfo td,.phpinfo th {font-size:1em;}
.phpinfo pre {margin: 0px; font-family: monospace;}
.phpinfo a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
.phpinfo a:hover {text-decoration: underline;}
.phpinfo table {border-collapse: collapse;}
.phpinfo .center {text-align: center;}
.phpinfo .center table { margin-left: auto; margin-right: auto; text-align: left;}
.phpinfo .center th,.phpinfo .center th:hover { text-align: center !important; }
.phpinfo td,.phpinfo th { border: 1px solid #555; font-size: 90%; vertical-align: baseline;}
.phpinfo h1 {font-size: 150%;}
.phpinfo h2 {font-size: 125%;}
.phpinfo .p {text-align: left;}
.phpinfo .e {width:20%;min-width:200px;background-color: #ccccff; font-weight: bold; color: #000000;}
.phpinfo .h,.phpinfo .h:hover ,.phpinfo .h th {background: #9999cc; font-weight: bold; color: #000000;}
.phpinfo .v,.phpinfo .v:hover {background-color: #cccccc; color: #000000;}
.phpinfo .vr {background-color: #cccccc; text-align: right; color: #000000;}
.phpinfo img {float: right; border: 0px;}
.phpinfo hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
</style>
<div class="phpinfo">
<?php
ob_start();
phpinfo();
$phpinfo = ob_get_clean();
if (preg_match_all('#<table[^>]+>(.*)</table>#iUs',$phpinfo, $matches)){
    foreach ($matches[0] as $i=>$item){
        echo '<table border="0" cellpadding="4" cellspacing="1" align="center" class="mainTable">',$matches[1][$i],'</table>';
        echo '<br />';
    }
}
?>
</div>
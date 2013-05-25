<?php
if (IS_CLI)
{

    //得到最前面的字符最大长度
    $maxlen = 0;
    foreach (Profiler::groups() as $group => $benchmarks){
        foreach ($benchmarks as $name => $tokens){
            $maxlen = max(strlen($name.' ('.count($tokens).')'),$maxlen);
        }
    }
    $strlen = $maxlen+64;

    echo "\n";
    echo "\x1b[0;33;44m";
    echo str_pad('PHP Version:'.PHP_VERSION,$strlen,' ',STR_PAD_BOTH);
    foreach (Profiler::groups() as $group => $benchmarks){
        echo "\x1b[32m";
        echo "\n".str_pad($group,$strlen,'-',STR_PAD_BOTH);
        echo "\x1b[33m";
        foreach ($benchmarks as $name => $tokens){
            echo "\x1b[35m";
            echo "\n".str_pad($name.' ('.count($tokens).')',$maxlen,' ',STR_PAD_LEFT);
            echo "\x1b[36m";
            foreach (array('Min          ', 'Max          ', 'Average      ', 'Total        ') as $key){
                echo '   ';
                echo "\x1b[36m";
                echo $key;
            }
            $stats = Profiler::stats($tokens);

            echo "\x1b[36m";
            echo "\n".str_pad('Time:',$maxlen,' ',STR_PAD_LEFT);

            foreach (array('min', 'max', 'average', 'total') as $key){

                echo '   ';
                echo "\x1b[33m";
                echo number_format($stats[$key]['time'], 6)."s    ";
            }

            echo "\x1b[36m";
            echo "\n".str_pad('Memory:',$maxlen,' ',STR_PAD_LEFT);
            foreach (array('min', 'max', 'average', 'total') as $key){
                echo '   ';
                echo "\x1b[33m";
                echo str_pad(number_format($stats[$key]['memory'] / 1024, 4).'kb',13);
            }
            echo "\n".str_pad('',$strlen,' ');
        }
    }
    echo "\x1b[33m";


    $stats = Profiler::application();
    echo "\x1b[32m";
    echo "\n".str_pad('Application Execution',$strlen,'-',STR_PAD_BOTH);
    echo "\n".str_pad('',$maxlen,' ',STR_PAD_LEFT);
    echo "\x1b[36m";
    foreach (array('Min          ', 'Max          ', 'Average      ', 'Total        ') as $key){
        echo '   ';
        echo "\x1b[36m";
        echo $key;
    }
    echo "\x1b[36m";
    echo "\n".str_pad('Time:',$maxlen,' ',STR_PAD_LEFT);
    foreach (array('min', 'max', 'average', 'total') as $key){

        echo '   ';
        echo "\x1b[33m";
        echo number_format($stats[$key]['time'], 6)."s    ";
    }
    echo "\x1b[36m";
    echo "\n".str_pad('Memory:',$maxlen,' ',STR_PAD_LEFT);
    foreach (array('min', 'max', 'average', 'total') as $key){
        echo '   ';
        echo "\x1b[33m";
        echo str_pad(number_format($stats[$key]['memory'] / 1024, 4).'kb',13);
    }
    echo "\n".str_pad('',$strlen,' ');
    echo "\x1b[33m";

    echo "\x1b[0m\n";

}else{
?>

<style type="text/css">
.profilerdiv {padding:10px;text-align:left;font-size:11px;font-family:Arial,sans-serif,Helvetica,"宋体";}
.profilerdiv table.profiler { width: 100%; margin: 0 auto 1em; border-collapse: collapse; }
    .profilerdiv table.profiler th,
    .profilerdiv table.profiler td { font-size:10px;padding: 0.2em 0.4em; background: #fff; border: solid 1px #ccc; text-align: left; font-weight: normal; font-size: 11px; color: #111; font-family:Arial }
    .profilerdiv table.profiler tr.profiler_group th { background: #222; color: #eee; border-color: #222;font-size: 18px;  }
    .profilerdiv table.profiler tr.profiler_headers th { text-transform: lowercase; font-variant: small-caps; background: #ddd; color: #777;font-size: 12px; }
    .profilerdiv table.profiler tr.profiler_mark th.profiler_name { float:none;width: 40%; font-size: 16px; background: #fff; vertical-align: middle; }
    .profilerdiv table.profiler tr.profiler_mark td.profiler_current { background: #eddecc; }
    .profilerdiv table.profiler tr.profiler_mark td.profiler_min { background: #d2f1cb; }
    .profilerdiv table.profiler tr.profiler_mark td.profiler_max { background: #ead3cb; }
    .profilerdiv table.profiler tr.profiler_mark td.profiler_average { background: #ddd; }
    .profilerdiv table.profiler tr.profiler_mark td.profiler_total { background: #d0e3f0; }
    .profilerdiv table.profiler tr.profiler_mark td.profiler_otherdata { background: #e6e6e6; }
    .profilerdiv table.profiler tr.profiler_time td { border-bottom: 0; }
    .profilerdiv table.profiler tr.profiler_memory td { border-top: none; }
    .profilerdiv table.profiler tr.final th.profiler_name { float:none;background: #222; color: #fff; }
    .profilerdiv tbody.hover td{background:#fffacd;}
</style><div style="text-align:left;z-index:100000;position:fixed;_position:absolute;width:100%;bottom:0px;left:0;height:26px;background:#000;filter:alpha(opacity=80);opacity:0.8;"><div style="padding:5px 14px;color:#fff;text-decoration:none;font-size:12px;line-height:15px;"><a href="#onlineprofiler" style="color:#fff;text-decoration:none;font-size:12px;line-height:15px;"
>调试：</a><?php
echo Form::checkbox(null,'1',Core::debug()->profiler('sql')->is_open(),array('id'=>'_profiler_sql')),'<label for="_profiler_sql">SQL:Explain</label> ',
Form::checkbox(null,'1',Core::debug()->profiler('nocached')->is_open(),array('id'=>'_profiler_nocached')),'<label for="_profiler_nocached">显示无缓存内容</label> ',
Form::checkbox(null,'1',Core::debug()->profiler('output')->is_open(),array('id'=>'_profiler_output')),'<label for="_profiler_output">显示模板变量</label> ',
Form::checkbox(null,'1',Core::debug()->profiler('filelist')->is_open(),array('id'=>'_profiler_filelist')),'<label for="_profiler_filelist">显示加载文件</label> ',
Form::checkbox(null,'1',Core::debug()->profiler('xhprof')->is_open(),array('id'=>'_profiler_xhprof')),'<label for="_profiler_xhprof">开启Xhprof</label> '
;?><input type="button" value="GO" onclick="profilerdiv_reload()"/> <input type="button" value="网格" onclick="if(document.body.style.backgroundImage==''){document.body.style.backgroundImage='url(<?php echo Core::url('statics/');?>images/tape.gif)';}else{document.body.style.backgroundImage='';}this.blur();" /></input></div></div>
<script type="text/javascript">
function profilerdiv_reload(){
	var s=document.location.search.substr(1);
	var s2=s.split('&');
	var newsearch = '?';
	for (var i=0 ;i< s2.length;i++){
		var item = s2[i].split('=');
		var n=item[0];
        var v=item[1];
        if (n=='debug'){
            v = document.getElementById('_profiler_sql').checked?'sql':'';
            v += document.getElementById('_profiler_nocached').checked?(v?'|':'')+'nocached':'';
            v += document.getElementById('_profiler_output').checked?(v?'|':'')+'output':'';
            v += document.getElementById('_profiler_filelist').checked?(v?'|':'')+'filelist':'';
            v += document.getElementById('_profiler_xhprof').checked?(v?'|':'')+'xhprof':'';
            if(!v)v='yes';
        }
        newsearch +=n+'='+v;
	}
	document.location.href = newsearch+document.location.hash;
}
</script>
<div style="position:absolute;z-index:99999;width:100%;left:0;background:#fff;">
<div class="profilerdiv"><a name="onlineprofiler"></a><?php foreach (Profiler::groups() as $group => $benchmarks): ?><table class="profiler">
        <tr class="profiler_group">
            <th class="profiler_name" colspan="5" style="float:none;"><?php echo ucfirst($group) ?></th>
        </tr>
        <tr class="profiler_headers">
            <th class="profiler_name" style="float:none;">Benchmark</th>
            <th class="profiler_min">Min</th>
            <th class="profiler_max">Max</th>
            <th class="profiler_average">Average</th>
            <th class="profiler_total">Total</th>
        </tr>
        <?php foreach ($benchmarks as $name => $tokens): ?>
        <tr class="profiler_mark profiler_time">
            <?php $stats = Profiler::stats($tokens); ?>
            <th class="profiler_name" rowspan="2"><?php echo $name, ' (', count($tokens), ')' ?></th>
            <?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
            <td class="profiler_<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 6), ' ', 'seconds' ?></td>
            <?php endforeach ?>
        </tr>
        <tr class="profiler_mark profiler_memory">
            <?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
            <td class="profiler_<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4), ' kb' ?></td>
            <?php endforeach ?>
        </tr>
        <?php if ($stats[$key]['data']):?>
        </table><table class="profiler" style="margin-top:-15px;">
        <tr class="profiler_mark profiler_memory">
            <td colspan="5" class="profiler_otherdata">
            <table width="100%" style="white-space:nowrap">
            <?php
            $i=1;
            foreach ($stats[$key]['data'] as $item){
                if ($i==1){
                    echo '<tr class="profiler_headers"><th width="26">no.</th>';
                    echo "<th>runtime</th>";
                    echo "<th>memory</th>";
                    foreach ($item['rows'][0] as $key=>$value){
                        echo "<th>{$key}</th>";
                    }
                    echo '</tr>';
                }

                $row_num = count($item['rows']);
                echo '<tbody onmouseover="this.className=\'hover\';" onmouseout="this.className=\'\';">';
                foreach ($item['rows'] as $r=>$row){
                    echo '<tr>';
                    if ($r==0){
                        echo '<td rowspan="'.$row_num.'" style="text-align:center;">'.$i.'</td>';
                        echo "<td rowspan='{$row_num}'>";
                        echo '<font style="color:red">'.number_format($item['runtime'], 6). '</font>';
                        echo "</td>";
                        echo "<td rowspan='{$row_num}'>";
                        echo '<font style="color:green">'.number_format($item['memory'] / 1024, 4). ' kb</font>';
                        echo "</td>";
                    }
                    foreach ($row as $key=>$value){
                        $tmpr = $r+1;
                        $tmp_row_num = 1;
                        while ($tmpr<$row_num){
                            if (isset($item['rows'][$tmpr][$key])){
                                break;
                            }else{
                                $tmp_row_num++;
                            }
                            $tmpr++;
                        }
                        echo "<td rowspan=\"{$tmp_row_num}\"><div style=\"max-width:1500px;max-height:500px;padding-right:18px;overflow:auto;\">";

                        if (is_array($value))
                        {
                            echo '<pre style="padding:0;margin:0;">',
                            htmlspecialchars(print_r($value,true)),
                            '</pre>';
                        }else{
                            echo $value;
                        }
                        echo "</div></td>";
                    }
                    echo '</td></tr>';
                }
                echo '</tbody>';
                $i++;
            }
            ?>
            </table>
            </td>
        </tr>
        <?php endif;?>
        <?php endforeach ?></table><?php endforeach ?><?php
if ( Core::debug()->profiler('filelist')->is_open() ){
    $includepath = Core::include_path();
    $filelist = get_included_files();
?><table class="profiler"><tr class="profiler_group">
            <th colspan="3" class="profiler_name" style="float:none;"><?php echo 'Include Path ('.count($includepath).')' ?></th>
        </tr>
            <?php foreach ($includepath as $value): ?>
        <tr class="final profiler_mark profiler_memory">
            <td style="width:88%"><?php echo Core::debug_path($value,true); ?></td>
        </tr>
            <?php endforeach ?>
</table><table class="profiler"><tr class="profiler_group">
            <th colspan="3" class="profiler_name" style="float:none;"><?php echo 'Included Files ('.count($filelist).')' ?></th>
        </tr>
            <?php foreach ($filelist as $i=>$value): ?>
        <tr class="final profiler_mark profiler_memory">
            <td class="profiler_average" style="width:4%;text-align:center;"><?php echo ($i+1); ?></td>
            <td style="width:8%"><?php echo Profiler::bytes(filesize($value));?></td>
            <td style="width:88%"><?php echo Core::debug_path($value,true); ?></td>
        </tr>
            <?php endforeach ?>
</table><?php
}
?><table class="profiler">
        <?php $stats = Profiler::application() ?>
        <tr class="final profiler_mark profiler_time">
            <th class="profiler_name" rowspan="2" style="float:none;"><?php echo 'Application Execution ('.$stats['count'].')' ?></th>
            <?php foreach (array('min', 'max', 'average', 'current') as $key): ?>
            <td class="profiler_<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 6), ' ', 'seconds' ?></td>
            <?php endforeach ?>
        </tr>
        <tr class="final profiler_mark profiler_memory">
            <?php foreach (array('min', 'max', 'average', 'current') as $key): ?>
            <td class="profiler_<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4), ' kb' ?></td>
            <?php endforeach ?>
        </tr>
    </table>
</div>
<br /><br />
</div>

<?php
}
?>
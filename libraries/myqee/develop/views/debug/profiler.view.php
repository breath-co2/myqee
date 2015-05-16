<style type="text/css">
    #_the_profiler_div {
        position: relative;
        z-index: 999999;
        background-color: #fff;
    }
    ._profilerdiv {
        padding:0 10px;
        text-align:left;
        font-size:11px;
        font-family:Arial,sans-serif,Helvetica,"宋体";
    }
    ._profilerdiv table.profiler {font-size:10px;width: 100%; margin: 0 auto 1em; border-collapse: collapse;}
    ._profilerdiv table.profiler th,
    ._profilerdiv table.profiler td {padding: 0.2em 0.4em; background: #fff; border: solid 1px #ccc; text-align: left; font-weight: normal; font-size: 11px; color: #111; font-family:Arial }
    ._profilerdiv table.profiler tr.profiler_group th { background: #222; color: #eee; border-color: #222;font-size: 18px;  }
    ._profilerdiv table.profiler tr.profiler_headers th { text-transform: lowercase; font-variant: small-caps; background: #ddd; color: #777;font-size: 12px; }
    ._profilerdiv table.profiler tr.profiler_mark th.profiler_name { float:none;width: 40%; font-size: 16px; background: #fff; vertical-align: middle; }
    ._profilerdiv table.profiler tr.profiler_mark td.profiler_current { background: #eddecc; }
    ._profilerdiv table.profiler tr.profiler_mark td.profiler_min { background: #d2f1cb; }
    ._profilerdiv table.profiler tr.profiler_mark td.profiler_max { background: #ead3cb; }
    ._profilerdiv table.profiler tr.profiler_mark td.profiler_average { background: #ddd; }
    ._profilerdiv table.profiler tr.profiler_mark td.profiler_total { background: #d0e3f0; }
    ._profilerdiv table.profiler tr.profiler_mark td.profiler_otherdata { background: #e6e6e6; }
    ._profilerdiv table.profiler tr.profiler_time td { border-bottom: 0; }
    ._profilerdiv table.profiler tr.profiler_memory td { border-top: none; }
    ._profilerdiv tbody.hover td{background:#fffacd;}

    ._profiler_bottom_div {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 4px 10px;
        background: rgba(0,0,0,0.75);
        color: #fff;
        border-top: 1px solid #ccc;
        font-size: 12px;
    }
    ._profiler_bottom_div label {
        float: left;
        display: inline-block;
    }
</style>
<br />
<br />
<br />
<br />
<div id="_the_profiler_div">
    <div class="_profilerdiv">
        <a name="onlineprofiler"></a>
        <?php
        foreach (Profiler::groups() as $group => $benchmarks):
        ?>
        <table class="profiler">
        <tr class="profiler_group">
            <th class="profiler_name" colspan="5" style="float:none;"><?php echo ucfirst($group) ?></th>
        </tr>
        <tr class="profiler_headers">
            <th class="profiler_name"><?php echo __('Benchmark');?></th>
            <th class="profiler_min"><?php echo __('Min');?></th>
            <th class="profiler_max"><?php echo __('Max');?></th>
            <th class="profiler_average"><?php echo __('Average');?></th>
            <th class="profiler_total"><?php echo __('Total');?></th>
        </tr>
        <?php foreach ($benchmarks as $name => $tokens): ?>
        <tr class="profiler_mark profiler_time">
            <?php $stats = Profiler::stats($tokens); ?>
            <th class="profiler_name" rowspan="2"><?php echo $name, ' (', count($tokens), ')' ?></th>
            <?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
                <td class="profiler_<?php echo $key ?>"><?php echo number_format($stats[$key]['time'], 6), ' ', __('seconds'); ?></td>
            <?php endforeach ?>
        </tr>
        <tr class="profiler_mark profiler_memory">
            <?php foreach (array('min', 'max', 'average', 'total') as $key): ?>
                <td class="profiler_<?php echo $key ?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4), ' kb' ?></td>
            <?php endforeach ?>
        </tr>
        <?php if ($stats[$key]['data']):?>
        </table>
        <table class="profiler" style="margin-top:-15px;">
        <tr class="profiler_mark profiler_memory">
            <td colspan="5" class="profiler_otherdata">
                <table width="100%" style="white-space:nowrap">
                <?php
                $i=1;
                foreach ($stats[$key]['data'] as $item)
                {
                    if ($i==1)
                    {
                        echo '<tr class="profiler_headers"><th width="26">no.</th>';
                        echo "<th>". __('runtime') ."</th>";
                        echo "<th>". __('memory') ."</th>";
                        foreach ($item['rows'][0] as $key=>$value)
                        {
                            echo "<th>{$key}</th>";
                        }
                        echo '</tr>';
                    }

                    $row_num = count($item['rows']);
                    echo '<tbody onmouseover="this.className=\'hover\';" onmouseout="this.className=\'\';">';
                    foreach ($item['rows'] as $r=>$row)
                    {
                        echo '<tr>';
                        if ($r==0)
                        {
                            echo '<td rowspan="'.$row_num.'" style="text-align:center;">'.$i.'</td>';
                            echo "<td rowspan='{$row_num}'>";
                            echo '<font style="color:red">'.number_format($item['runtime'], 6). '</font>';
                            echo "</td>";
                            echo "<td rowspan='{$row_num}'>";
                            echo '<font style="color:green">'.number_format($item['memory'] / 1024, 4). ' kb</font>';
                            echo "</td>";
                        }

                        foreach ($row as $key=>$value)
                        {
                            $tmpr = $r+1;
                            $tmp_row_num = 1;
                            while ($tmpr<$row_num){
                                if (isset($item['rows'][$tmpr][$key]))
                                {
                                    break;
                                }
                                else
                                {
                                    $tmp_row_num++;
                                }
                                $tmpr++;
                            }
                            echo "<td rowspan=\"{$tmp_row_num}\"><div style=\"max-width:1500px;max-height:500px;padding-right:18px;overflow:auto;white-space:pre-wrap;\">";

                            if (is_array($value))
                            {
                                echo htmlspecialchars(print_r($value, true));
                            }
                            else
                            {
                                echo htmlspecialchars($value);
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
        <?php
        endif;
        endforeach;
        ?>
        </table>
        <?php
        endforeach;
        ?>
        <table class="profiler">
            <tr class="profiler_group">
                <th colspan="4" class="profiler_name"><?php echo __('System Information'); ?></th>
            </tr>
            <tr class="profiler_headers">
                <th class="profiler_name"><?php echo __('Benchmark');?></th>
                <th class="profiler_min"><?php echo __('runtime');?></th>
                <th class="profiler_max"><?php echo __('memory');?></th>
                <th class="profiler_total"><?php echo __('include file');?></th>
            </tr>
        <?php
        foreach (array('Core Execution' => Profiler::core_system(), 'Application Execution' => Profiler::application()) as $key => $stats)
        {
        ?>
            <tr class="profiler_mark profiler_time">
                <th class="profiler_name" style="padding: 8px 6px;"><?php echo __($key); ?></th>
                <td class="profiler_min">
                <?php
                echo number_format($stats['max']['time'], 6), ' ', __('seconds');
                ?>
                </td>
                <td class="profiler_max">
                <?php
                echo number_format($stats['max']['memory'] / 1024, 4), ' kb';
                ?>
                </td>
                <td class="profiler_total">
                <?php
                echo $stats['file_count'];
                ?>
                </td>
            </tr>
        <?php
        }
        ?>
        </table>
        <?php
        if (Core::debug()->profiler('filelist')->is_open())
        {
        $includepath = Core::include_path();
        $filelist = get_included_files();
        ?>
        <table class="profiler">
            <tr class="profiler_group">
                <th colspan="3" class="profiler_name"><?php echo __('Include Path') .' ('.count($includepath).')' ?></th>
            </tr>
            <?php foreach ($includepath as $value): ?>
            <tr class="final profiler_mark profiler_memory">
                <td style="width:88%"><?php echo Core::debug_path($value, true); ?></td>
            </tr>
            <?php endforeach ?>
        </table>
        <table class="profiler">
            <tr class="profiler_group">
                <th colspan="3" class="profiler_name"><?php echo __('Included Files') .' ('.count($filelist).')' ?></th>
            </tr>
            <?php foreach ($filelist as $i=>$value): ?>
                <tr class="final profiler_mark profiler_memory">
                    <td class="profiler_average" style="width:4%;text-align:center;"><?php echo ($i+1); ?></td>
                    <td style="width:8%"><?php echo Profiler::bytes(filesize($value));?></td>
                    <td style="width:88%"><?php echo Core::debug_path($value, true); ?></td>
                </tr>
            <?php endforeach ?>
        </table>
        <?php
        }
        ?>
        <br />
        <br />
    </div>


    <script type="text/javascript">
        function _profilerdiv_reload() {
            var s = document.location.search.substr(1);
            var s2 = s.split('&');
            var newsearch = '';
            for (var i = 0; i< s2.length; i++) {
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
                newsearch += '&'+ n +'='+ v;
            }
            document.location.href = '?' + newsearch.substr(1) + document.location.hash;
        }
    </script>


    <div class="_profiler_bottom_div">
        <div style="float: right">
            <input type="button" onclick="if(typeof unit_tool=='undefined'){document.body.appendChild(document.createElement('script')).src='https://www.tenpay.com/v2/labs/wrtb/js/unit_demo.js';window.setTimeout(function(){if(typeof unit_tool=='undefined'){alert('loading....');}},7000);if(window.loadUnit){var func_loadunit=setInterval(function(){if (loadUnit){loadUnit();clearInterval(func_loadunit);}},50);}if (!document.getElementById('unit-tool')){var func_unittool=setInterval(function(){if (typeof unit_tool!='undefined'){unit_tool();clearInterval(func_unittool);}},50);}}else if(typeof unit_tool!='undefined'){if(!document.getElementById('unit-tool')){unit_tool();}else{alert('已开启');}}" value="页面单元测试" />
        </div>
        <?php
        echo
        '<label>', Form::checkbox(null, '1', Core::debug()->profiler('sql')->is_open(), array('id'=>'_profiler_sql')), 'SQL:Explain</label> ',
        '<label>', Form::checkbox(null, '1', Core::debug()->profiler('nocached')->is_open(), array('id'=>'_profiler_nocached')), '显示无缓存内容</label> ',
        '<label>', Form::checkbox(null, '1', Core::debug()->profiler('output')->is_open(), array('id'=>'_profiler_output')), '显示模板变量</label> ',
        '<label>', Form::checkbox(null, '1', Core::debug()->profiler('filelist')->is_open(), array('id'=>'_profiler_filelist')), '显示加载文件</label> ',
        '<label>', Form::checkbox(null, '1', Core::debug()->profiler('xhprof')->is_open(), array('id'=>'_profiler_xhprof')), '开启Xhprof</label> '
        ;?>
        &nbsp; <input type="button" value="GO" onclick="_profilerdiv_reload()" />
    </div>

</div>

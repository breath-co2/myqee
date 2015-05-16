<?php

//得到最前面的字符最大长度
$maxlen = 0;
foreach (Profiler::groups() as $group => $benchmarks)
{
    foreach ($benchmarks as $name => $tokens)
    {
        $maxlen = max(strlen($name .' ('. count($tokens) .')'), $maxlen);
    }
}
$strlen = $maxlen+64;

echo "\n";
echo "\x1b[0;33;44m";
echo str_pad('PHP Version:'. PHP_VERSION, $strlen, ' ', STR_PAD_BOTH);
foreach (Profiler::groups() as $group => $benchmarks)
{
    echo "\x1b[32m";
    echo "\n". str_pad($group, $strlen, '-', STR_PAD_BOTH);
    echo "\x1b[33m";
    foreach ($benchmarks as $name => $tokens)
    {
        echo "\x1b[35m";
        echo "\n".str_pad($name .' ('. count($tokens) .')', $maxlen, ' ', STR_PAD_LEFT);
        echo "\x1b[36m";
        foreach (array('Min          ', 'Max          ', 'Average      ', 'Total        ') as $key)
        {
            echo '   ';
            echo "\x1b[36m";
            echo $key;
        }
        $stats = Profiler::stats($tokens);

        echo "\x1b[36m";
        echo "\n".str_pad('Time:',$maxlen,' ',STR_PAD_LEFT);

        foreach (array('min', 'max', 'average', 'total') as $key)
        {
            echo '   ';
            echo "\x1b[33m";
            echo number_format($stats[$key]['time'], 6)."s    ";
        }

        echo "\x1b[36m";
        echo "\n".str_pad('Memory:',$maxlen,' ',STR_PAD_LEFT);
        foreach (array('min', 'max', 'average', 'total') as $key)
        {
            echo '   ';
            echo "\x1b[33m";
            echo str_pad(number_format($stats[$key]['memory'] / 1024, 4).'kb',13);
        }
        echo "\n".str_pad('', $strlen, ' ');
    }
}
echo "\x1b[33m";


$stats = Profiler::application();
echo "\x1b[32m";
echo "\n".str_pad('Application Execution', $strlen, '-', STR_PAD_BOTH);
echo "\n".str_pad('', $maxlen, ' ', STR_PAD_LEFT);
echo "\x1b[36m";
foreach (array('Min          ', 'Max          ', 'Average      ', 'Total        ') as $key)
{
    echo '   ';
    echo "\x1b[36m";
    echo $key;
}
echo "\x1b[36m";
echo "\n". str_pad('Time:', $maxlen, ' ', STR_PAD_LEFT);
foreach (array('min', 'max', 'average', 'total') as $key)
{

    echo '   ';
    echo "\x1b[33m";
    echo number_format($stats[$key]['time'], 6) ."s    ";
}
echo "\x1b[36m";
echo "\n".str_pad('Memory:',$maxlen,' ',STR_PAD_LEFT);
foreach (array('min', 'max', 'average', 'total') as $key)
{
    echo '   ';
    echo "\x1b[33m";
    echo str_pad(number_format($stats[$key]['memory'] / 1024, 4) .'kb', 13);
}
echo "\n".str_pad('', $strlen, ' ');
echo "\x1b[33m";

echo "\x1b[0m\n";

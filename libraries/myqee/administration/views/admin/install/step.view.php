<?php
for($i=1;$i<$step;$i++)
{
    next($config['step']);
}
$step_config = current($config['step']);
$step_key = key($config['step']);
reset($config['step']);
?>
<div id="step_div">
    <div id="step_title">
    <table align="center" border="0" cellpadding="0" cellspacing="0">
        <tr>
        <td valign="top" rowspan="2">
        <span class="big_step"><?php echo $step;?>.</span>
        </td>
        <td><span class="big_title"><?php echo $step_config['title']?></span></td>
        </tr>
        <tr>
        <td><span class="big_desc"><?php echo $step_config['desc'];?></span></td>
        </tr>
    </table>
    </div>
    <div id="step_bar">
        <div id="step_line"></div>
        <ul class="ul">
            <?php
            $i=0;
            foreach ($config['step'] as $v)
            {
                $i++;
                echo '<li';
                if( $i<$step )
                {
                    echo ' class="set_ok"';
                }
                elseif ( $i==$step )
                {
                    echo ' class="set_now"';
                }
                echo '>'.$v['title'].'</li>'.CRLF;
            }
            ?>
        </ul>
    </div>
</div>
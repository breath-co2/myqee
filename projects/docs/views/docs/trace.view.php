<style type="text/css">
#expction_div { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
#expction_div h1,
#expction_div h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
#expction_div h1 a,
#expction_div h2 a { color: #fff; }
#expction_div h2 { background: #222; }
#expction_div h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
#expction_div p { margin: 0; padding: 0.2em 0; }
#expction_div a { color: #1b323b; }
#expction_div pre { overflow: auto; white-space: pre-wrap; }
#expction_div table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
#expction_div table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
#expction_div div.content { padding: 0.4em 1em 1em; overflow: hidden; }
#expction_div pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
#expction_div pre.source span.line { display: block; }
#expction_div pre.source span.highlight { background: #f0eb96; }
#expction_div pre.source span.line span.number { color: #666; }
#expction_div ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
#expction_div ol.trace li { margin: 0; padding: 0; }
</style>
<script type="text/javascript">
document.write('<style type="text/css"> .collapsed { display: none; } </style>');
function koggle(elem)
{
elem = document.getElementById(elem);

if (elem.style && elem.style['display'])
// Only works with the "style" attr
var disp = elem.style['display'];
else if (elem.currentStyle)
// For MSIE, naturally
var disp = elem.currentStyle['display'];
else if (window.getComputedStyle)
// For most other browsers
var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

// Toggle the state of the "display" style
elem.style.display = disp == 'block' ? 'none' : 'block';
return false;
}
</script>

<div style="padding:0 10px;">
<div id="expction_div">
<h1><span class="type">Exception [ Notice ]:</span> <span class="message">测试Core::trace()</span></h1>
<div id="error4dd3f2d36e63c" class="content">
<p><span class="file">LIBRARY/MyQEE/Core/classes/MyQEE/Core.class.php [ 238 ]</span></p>
<pre class="source"><code><span class="line"><span class="number">233</span>      */

</span><span class="line"><span class="number">234</span>     public static function trace($msg = 'Trace Tree', $code = E_NOTICE)
</span><span class="line"><span class="number">235</span>     {
</span><span class="line"><span class="number">236</span>         if ( IS_DEBUG )
</span><span class="line"><span class="number">237</span>         {
</span><span class="line highlight"><span class="number">238</span>             throw new Exception($msg, $code);
</span><span class="line"><span class="number">239</span>             exit();

</span><span class="line"><span class="number">240</span>         }
</span><span class="line"><span class="number">241</span>     }
</span><span class="line"><span class="number">242</span>
</span><span class="line"><span class="number">243</span>     /**
</span></code></pre><ol class="trace">
<li>
<p>
    <span class="file">
                    <a href="#error4dd3f2d36e63csource0" onclick="return koggle('error4dd3f2d36e63csource0')">PROJECT/docs/controllers/docs/index.controller.php [ 64 ]</a>

            </span>
    &raquo;
    MyQEE_Core::trace(<a href="#error4dd3f2d36e63cargs0" onclick="return koggle('error4dd3f2d36e63cargs0')">arguments</a>)
</p>
<div id="error4dd3f2d36e63cargs0" class="collapsed">
    <table cellspacing="0">
            <tr>
            <td><code>msg</code></td>

            <td><pre style="padding:0;margin:0;"><small>string</small><span>(19)</span> "测试Core::trace()"</pre></td>
        </tr>
        </table>
</div>
    <pre id="error4dd3f2d36e63csource0" class="source collapsed"><code><pre class="source"><code><span class="line"><span class="number">59</span>         return $file;
</span><span class="line"><span class="number">60</span>     }

</span><span class="line"><span class="number">61</span>
</span><span class="line"><span class="number">62</span>    public function action_trace()
</span><span class="line"><span class="number">63</span>    {
</span><span class="line highlight"><span class="number">64</span>      Core::trace('测试Core::trace()');
</span><span class="line"><span class="number">65</span>    }
</span><span class="line"><span class="number">66</span> }</span></code></pre></code></pre>

</li>
<li>
<p>
    <span class="file">
                    <a href="#error4dd3f2d36e63csource1" onclick="return koggle('error4dd3f2d36e63csource1')">LIBRARY/MyQEE/Core/classes/MyQEE/Request.class.php [ 654 ]</a>
            </span>
    &raquo;
    Controller_Docs__Index->action_trace()
</p>
    <pre id="error4dd3f2d36e63csource1" class="source collapsed"><code><pre class="source"><code><span class="line"><span class="number">649</span>         # 执行方法

</span><span class="line"><span class="number">650</span>         $count_arguments = count($arguments);
</span><span class="line"><span class="number">651</span>         switch ( $count_arguments )
</span><span class="line"><span class="number">652</span>         {
</span><span class="line"><span class="number">653</span>             case 0 :
</span><span class="line highlight"><span class="number">654</span>                 $controller-&gt;$action_name();
</span><span class="line"><span class="number">655</span>                 break;

</span><span class="line"><span class="number">656</span>             case 1 :
</span><span class="line"><span class="number">657</span>                 $controller-&gt;$action_name($arguments[0]);
</span><span class="line"><span class="number">658</span>                 break;
</span><span class="line"><span class="number">659</span>             case 2 :
</span></code></pre></code></pre>
</li>
<li>
<p>

    <span class="file">
                    <a href="#error4dd3f2d36e63csource2" onclick="return koggle('error4dd3f2d36e63csource2')">LIBRARY/MyQEE/Core/classes/MyQEE/Core.class.php [ 189 ]</a>
            </span>
    &raquo;
    MyQEE_HttpIO::execute(<a href="#error4dd3f2d36e63cargs2" onclick="return koggle('error4dd3f2d36e63cargs2')">arguments</a>)
</p>
<div id="error4dd3f2d36e63cargs2" class="collapsed">
    <table cellspacing="0">

            <tr>
            <td><code>uri</code></td>
            <td><pre style="padding:0;margin:0;"><small>string</small><span>(17)</span> "/docs/index/trace"</pre></td>
        </tr>
            <tr>
            <td><code>print</code></td>

            <td><pre style="padding:0;margin:0;"><small>bool</small> FALSE</pre></td>
        </tr>
        </table>
</div>
    <pre id="error4dd3f2d36e63csource2" class="source collapsed"><code><pre class="source"><code><span class="line"><span class="number">184</span>         Core::debug()-&gt;log(Core::$path_info, 'PathInfo');
</span><span class="line"><span class="number">185</span>

</span><span class="line"><span class="number">186</span>         Core::$arguments = explode('/', trim(Core::$path_info, '/ '));
</span><span class="line"><span class="number">187</span>
</span><span class="line"><span class="number">188</span>         # 执行
</span><span class="line highlight"><span class="number">189</span>         $output = HttpIO::execute(Core::$path_info, false);
</span><span class="line"><span class="number">190</span>         if ( false === $output )
</span><span class="line"><span class="number">191</span>         {

</span><span class="line"><span class="number">192</span>             # 抛出404错误
</span><span class="line"><span class="number">193</span>             Core::show_404();
</span><span class="line"><span class="number">194</span>             exit();
</span></code></pre></code></pre>
</li>
<li>
<p>
    <span class="file">
                    <a href="#error4dd3f2d36e63csource3" onclick="return koggle('error4dd3f2d36e63csource3')">LIBRARY/MyQEE/Core/classes/MyQEE/Core.class.php [ 167 ]</a>

            </span>
    &raquo;
    MyQEE_Core::run()
</p>
    <pre id="error4dd3f2d36e63csource3" class="source collapsed"><code><pre class="source"><code><span class="line"><span class="number">162</span>         {
</span><span class="line"><span class="number">163</span>             Profiler::setup();
</span><span class="line"><span class="number">164</span>         }

</span><span class="line"><span class="number">165</span>         if ( $auto_run )
</span><span class="line"><span class="number">166</span>         {
</span><span class="line highlight"><span class="number">167</span>             Core::run();
</span><span class="line"><span class="number">168</span>         }
</span><span class="line"><span class="number">169</span>     }
</span><span class="line"><span class="number">170</span>

</span><span class="line"><span class="number">171</span>     /**
</span><span class="line"><span class="number">172</span>      * 系统执行
</span></code></pre></code></pre>
</li>
<li>
<p>
    <span class="file">
                    <a href="#error4dd3f2d36e63csource4" onclick="return koggle('error4dd3f2d36e63csource4')">LIBRARY/bootstrap.php [ 475 ]</a>
            </span>

    &raquo;
    MyQEE_Core::setup(<a href="#error4dd3f2d36e63cargs4" onclick="return koggle('error4dd3f2d36e63cargs4')">arguments</a>)
</p>
<div id="error4dd3f2d36e63cargs4" class="collapsed">
    <table cellspacing="0">
            <tr>
            <td><code>auto_run</code></td>
            <td><pre style="padding:0;margin:0;"><small>bool</small> TRUE</pre></td>

        </tr>
        </table>
</div>
    <pre id="error4dd3f2d36e63csource4" class="source collapsed"><code><pre class="source"><code><span class="line"><span class="number">470</span>
</span><span class="line"><span class="number">471</span>         # 注册自动加载类
</span><span class="line"><span class="number">472</span>         spl_autoload_register( array( 'Bootstrap', 'auto_load' ) );
</span><span class="line"><span class="number">473</span>
</span><span class="line"><span class="number">474</span>         # 加载系统核心

</span><span class="line highlight"><span class="number">475</span>         Core::setup( $auto_run );
</span><span class="line"><span class="number">476</span>     }
</span><span class="line"><span class="number">477</span>
</span><span class="line"><span class="number">478</span>     /**
</span><span class="line"><span class="number">479</span>      * 设置项目
</span><span class="line"><span class="number">480</span>      * 可重新设置新项目已实现程序内项目切换，但需谨慎使用

</span></code></pre></code></pre>
</li>
<li>
<p>
    <span class="file">
                    <a href="#error4dd3f2d36e63csource5" onclick="return koggle('error4dd3f2d36e63csource5')">SYSTEM/index.php [ 30 ]</a>
            </span>
    &raquo;
    Bootstrap::setup()
</p>
    <pre id="error4dd3f2d36e63csource5" class="source collapsed"><code><pre class="source"><code><span class="line"><span class="number">25</span> # 临时数据目录

</span><span class="line"><span class="number">26</span> $dir_temp    = './temp/';
</span><span class="line"><span class="number">27</span>
</span><span class="line"><span class="number">28</span> include $dir_library . 'bootstrap.php';
</span><span class="line"><span class="number">29</span>
</span><span class="line highlight"><span class="number">30</span> Bootstrap::setup();</span></code></pre></code></pre>
</li>
</ol>
</div>
<?php
$error_id = uniqid('error');
function t_dump( $var, $length = 128, $level = 0 )
{
    if ( $var === NULL )
    {
        return '<small>NULL</small>';
    }
    elseif ( is_bool( $var ) )
    {
        return '<small>bool</small> ' . ($var ? 'TRUE' : 'FALSE');
    }
    elseif ( is_float( $var ) )
    {
        return '<small>float</small> ' . $var;
    }
    elseif ( is_resource( $var ) )
    {
        if ( ($type = get_resource_type( $var )) === 'stream' and $meta = stream_get_meta_data( $var ) )
        {
            $meta = stream_get_meta_data( $var );

            if ( isset( $meta['uri'] ) )
            {
                $file = $meta['uri'];

                if ( function_exists( 'stream_is_local' ) )
                {
                    // Only exists on PHP >= 5.2.4
                    if ( stream_is_local( $file ) )
                    {
                        $file = Core::debug_path( $file );
                    }
                }

                return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars( $file, ENT_NOQUOTES, Core::$charset );
            }
        }
        else
        {
            return '<small>resource</small><span>(' . $type . ')</span>';
        }
    }
    elseif ( is_string( $var ) )
    {
        if ( strlen( $var ) > $length )
        {
            // Encode the truncated string
            $str = htmlspecialchars( substr( $var, 0, $length ), ENT_NOQUOTES, Core::$charset ) . '&nbsp;&hellip;';
        }
        else
        {
            // Encode the string
            $str = @htmlspecialchars( $var, ENT_NOQUOTES, Core::$charset );
        }

        return '<small>string</small><span>(' . strlen( $var ) . ')</span> "' . $str . '"';
    }
    elseif ( is_array( $var ) )
    {
        $output = array();

        // Indentation for this variable
        $space = str_repeat( $s = '	', $level );

        static $marker = null;

        if ( $marker === null )
        {
            // Make a unique marker
            $marker = uniqid( "\x00" );
        }

        if ( empty( $var ) )
        {
            // Do nothing
        }
        elseif ( isset( $var[$marker] ) )
        {
            $output[] = "(\n$space$s*RECURSION*\n$space)";
        }
        elseif ( $level < 5 )
        {
            $output[] = "<span>(";

            $var[$marker] = TRUE;
            foreach ( $var as $key => & $val )
            {
                if ( $key === $marker ) continue;
                if ( ! is_int( $key ) )
                {
                    $key = '"' . $key . '"';
                }

                $output[] = "$space$s$key => " . self::_dump( $val, $length, $level + 1 );
            }
            unset( $var[$marker] );

            $output[] = "$space)</span>";
        }
        else
        {
            // Depth too great
            $output[] = "(\n$space$s...\n$space)";
        }

        return '<small>array</small><span>(' . count( $var ) . ')</span> ' . implode( "\n", $output );
    }
    elseif ( is_object( $var ) )
    {
        // Copy the object as an array
        $array = ( array ) $var;

        $output = array();

        // Indentation for this variable
        $space = str_repeat( $s = '	', $level );

        $hash = spl_object_hash( $var );

        // Objects that are being dumped
        static $objects = array();

        if ( empty( $var ) )
        {
            // Do nothing
        }
        elseif ( isset( $objects[$hash] ) )
        {
            $output[] = "{\n$space$s*RECURSION*\n$space}";
        }
        elseif ( $level < 5 )
        {
            $output[] = "<code>{";

            $objects[$hash] = TRUE;
            foreach ( $array as $key => & $val )
            {
                if ( $key[0] === "\x00" )
                {
                    // Determine if the access is private or protected
                    $access = '<small>' . ($key[1] === '*' ? 'protected' : 'private') . '</small>';

                    // Remove the access level from the variable name
                    $key = substr( $key, strrpos( $key, "\x00" ) + 1 );
                }
                else
                {
                    $access = '<small>public</small>';
                }

                $output[] = "$space$s$access $key => " . self::_dump( $val, $length, $level + 1 );
            }
            unset( $objects[$hash] );

            $output[] = "$space}</code>";
        }
        else
        {
            // Depth too great
            $output[] = "{\n$space$s...\n$space}";
        }

        return '<small>object</small> <span>' . get_class( $var ) . '(' . count( $array ) . ')</span> ' . implode( "\n", $output );
    }
    else
    {
        return '<small>' . gettype( $var ) . '</small> ' . htmlspecialchars( print_r( $var, TRUE ), ENT_NOQUOTES, Core::$charset );
    }
}
?>
<h2><a href="#<?php echo $env_id = $error_id.'environment' ?>" onclick="return koggle('<?php echo $env_id ?>')">Environment</a></h2>
<div id="<?php echo $env_id ?>" class="content collapsed">
	<?php $included = get_included_files() ?>
	<h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')">Included files</a> (<?php echo count($included) ?>)</h3>
	<div id="<?php echo $env_id ?>" class="collapsed">
		<table cellspacing="0">
			<?php foreach ($included as $file): ?>
			<tr>
				<td><code><?php echo Core::debug_path($file) ?></code></td>
			</tr>
				<?php endforeach ?>
		</table>
	</div>
		<?php $included = get_loaded_extensions() ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')">Loaded extensions</a> (<?php echo count($included) ?>)</h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($included as $file): ?>
				<tr>
					<td><code><?php echo Core::debug_path($file) ?></code></td>
				</tr>
				<?php endforeach ?>
			</table>
		</div>
		<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
		<?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($GLOBALS[$var] as $key => $value): ?>
				<tr>
					<td><code><?php echo $key ?></code></td>
					<td><pre style="padding:0;margin:0;"><?php echo t_dump($value) ?></pre></td>
				</tr>
				<?php endforeach ?>
			</table>
		</div>
		<?php endforeach ?>
	</div>
</div>
</div>

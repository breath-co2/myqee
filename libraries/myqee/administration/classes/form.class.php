<?php

/**
 * 表单核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Library_MyQEE_Administration_Form extends Core_Form
{

    /**
     * 时间输入框
     *
     * @param   string  input name
     * @param   string  input value
     * @param   array   html attributes
     * @return  string
     * @uses	Form::input
     * @uses	HTML::attributes
     */
    public static function input_time($name, $value = '', array $attributes = null , $showinput = true)
    {
        $attributes['time'] = true;
        return Form::input_date($name, $value , $attributes , $showinput);
    }

    /**
     * 日期输入框
     *
     * @param   string  input name
     * @param   string  input value
     * @param   array   html attributes
     * @return  string
     * @uses	Form::input
     * @uses	HTML::attributes
     */
    public static function input_date($name, $value = '', array $attributes = null , $showinput = true)
    {
        static $run = null;
        if ( null===$run )
        {
            $run = true;
            $tmpinput = '<link rel="stylesheet" type="text/css" href="'.Core::url_assets('css/calender.css').'" /><script type="text/javascript">MyQEE.$import("'.Core::url_assets('js/calender.js').'");</script>';
        }
        else
        {
            $tmpinput = '';
        }
        $thename = $name ? $name : $attributes['name'];
        $attributes['style'] = 'width:'.($attributes['time']?138:75).'px;font-family:Verdana,Helvetica,Arial,sans-serif;font-size:12px;';
        $attributes['id'] or $attributes['id'] = '_calender_' . $thename;
        $input_att = $attributes;
        $input_att['onclick'] = 'showcalender(event,this,' . ($attributes['time'] ? 'true' : 'false') . ');';
        $input_att['onfocus'] = 'showcalender(event, this,' . ($attributes['time'] ? 'true' : 'false') .');if(this.value==\'0000-00-00' . ($attributes['time'] ? ' 0:0:0' : '') . '\')this.value=\'\';';
        $input_att['onmousewheel'] = 'return wheelcalender(event,this,' . ($attributes['time'] ? 'true' : 'false') . ');';
        unset($input_att['time']);

        $tmpinput .= '<span style="white-space:nowrap;">' . Form::input($name, is_numeric($value)&&$value>0? date("Y-m-d" . ($attributes['time'] ? ' H:i:s' : ''), $value > 0 ? $value : TIME ):$value, $input_att );
        if ( $showinput )
        {
            $tmpinput .= '<img src="'.Core::url_assets('images/icon/calender.png').'" style="margin-right:8px;margin-left:2px;vertical-align:middle;cursor:pointer;" title="显示时间控件" onclick="var myobj=MyQEE.$(\'' . $attributes['id'] .'\');if(myobj){if(myobj.disabled)return false;myobj.focus();myobj.onfocus(event);}" />';
        }
        $tmpinput .= '</span>';

        return $tmpinput;
    }

    /**
     * 输出一个带下拉的input框
     *
     *     // 简单的输出例子
     *     Form::input_select('test', 1, array('a,'b','c'));
     *
     *     // 带JS设置的处理方式
     *     <script>
     *     var set_input = function(obj)
     *     {
     *         obj.url = '/test.php';
     *         obj.method = 'POST';
     *     }
     *     </script>
     *     <?php
     *     Form::input_select('test', 1, array('a,'b','c') , array('size'=>4) , 'set_input');
     *     ?>
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @param array $attributes
     * @param string $calljs 回调JS方法
     */
    public static function input_select($name, $value = null, $options = array(), array $attributes = null , $calljs = null )
    {
        if (!is_array($attributes))
        {
            $attributes = array();
        }
        if (!is_array($options))
        {
            $options = array();
        }

        $attributes['_is_inputselect'] = 'true';
        $attributes['onclick'] = 'if (!this._o){this._o = new MyQEE.suggest(this);this._o.options = '.json_encode($options).';this._o.correction_left = 1;this._o.correction_top = 2;this._o.correction_width = 2;this._o.correction_height = 3;'.($calljs?'try{'.$calljs.'(this._o);}catch(e){}':'').';this.onfocus();}'.($attributes['onclick']?$attributes['onclick']:'');

        $attributes2 = array(
            '_is_inputselect_show' => 'true',
            'onclick' => 'this.style.display=\'none\';var obj=this.nextSibling;if(obj && obj.getAttribute(\'_is_inputselect\')==\'true\'){obj.style.display=\'\';obj.focus();obj.onclick();}',
        );
        if (isset($attributes['size']))$attributes2['size'] = $attributes['size'];
        if (isset($attributes['style']))$attributes2['style'] = $attributes['style'];
        $attributes['style'] = 'display:none;' . $attributes['style'];
        return '<span class="input_select_div">'.Form::input(null , $options[$value] , $attributes2 ).Form::input($name , $value , $attributes ).'</span>';
	}

}
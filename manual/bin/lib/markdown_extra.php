<?php

class MarkdownExtra extends _MarkdownExtra_TmpImpl
{
    function __construct()
    {
        $this->block_gamut += array
        (
            'doReplaceClass' => 1,
            'doReplaceClassListTag' => 1,
            'autoChangeUrlLink' => 1,
            'autoChangeUrlImage' => 1,
            'doHeaders2' => 11,
        );

        parent::__construct();
    }

    function doHeaders2($text) {
        # atx-style headers:
        #   !!! Header 1
        #   !!! Header 2 with closing hashes !!!
        #   ...
        #   ###### Header 6
        #
        $text = preg_replace_callback('{
                ^(\!{3,6}|\[\!\!\])  # $1 = string of !\'s
                [ ]*
                (.+?)       # $2 = Header text
                [ ]*
                \!*         # optional closing #\'s (not counted)
                \n+
            }xm',
            array(&$this, '_doHeaders2_callback_atx'), $text);

        return $text;
    }

    function _doHeaders2_callback_atx($matches) {
        $block = '<div class="alert alert-error">'.$this->runSpanGamut($matches[2])."</div>";
        return "\n" . $this->hashBlock($block) . "\n\n";
    }

    // 自动修改超链接中.md路径
    function autoChangeUrlLink($text) {
        $text = preg_replace_callback('#\(([^\(\)]+)\.md\)#xmi', array(&$this, '_autoChangeUrlLink_callback_atx'), $text);

        return $text;
    }

    function _autoChangeUrlLink_callback_atx($matches)
    {
        $matches[1] = strtolower(ltrim($matches[1], './'));
        $matches[1] = preg_replace('#manual/guide/[a-z0-9_\-]+/#', '', $matches[1]);

        return '('.$matches[1].'.html)';
    }

    // 自动修改图片相对路径
    function autoChangeUrlImage($text) {
        $text = preg_replace_callback('#\(([^\(\)]+)\.(png|gif|jpg|jpeg|bmp)\)#xmi', array(&$this, '_autoChangeUrlImage_callback_atx'), $text);

        return $text;
    }

    function _autoChangeUrlImage_callback_atx($matches)
    {
        global $current_base_href;

        if ($current_base_href)
        {
            if (substr($matches[1],0 , strlen($current_base_href))==$current_base_href)
            {
                $matches[1] = substr($matches[1], strlen($current_base_href));
            }
        }

        if (false!==strpos($matches[1], 'manual/html/assets/'))
        {
            $matches[1] = str_replace('manual/html/assets/', '../assets/', $matches[1]);
        }

        return '('.$matches[1].'.'.$matches[2].')';
    }

    function doReplaceClassListTag($text) {
        $text = preg_replace_callback('#\{\{class\.(core|project|library|team)\.([a-z0-9_]+)(?:\|(list)(?:\|([a-z0-9_\$]+))?)?}}#xmi', array(&$this, '_doReplaceClassListTag_callback_atx'), $text);

        return $text;
    }

    function _doReplaceClassListTag_callback_atx($matches)
    {
        return get_html_by_class($matches[1], $matches[2], $matches[3], $matches[4]);
    }

    function doReplaceClass($text) {
        //$text = preg_replace_callback('#<class>([a-z0-9_]+)</class>#xmi', array(&$this, '_doReplaceClass_callback_atx'), $text);
        //$text = preg_replace_callback('#<method>([a-z0-9_]+)</method>#xmi', array(&$this, '_doReplaceMethod_callback_atx'), $text);

        return $text;
    }

    function _doReplaceClass_callback_atx($matches)
    {

    }

}
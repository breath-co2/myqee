<?php

class MarkdownExtra extends _MarkdownExtra_TmpImpl
{
    function __construct()
    {
        $this->block_gamut += array
        (
            'doReplaceClass' => 1,
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
                ^(\!{1,6}|\[\!\!\])  # $1 = string of !\'s
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


    function doReplaceClass($text) {
        //$test = preg_replace_callback('#<class>([a-z0-9_]+)</class>#xmi', array(&$this, '_doReplaceClass_callback_atx'), $text);
        //$test = preg_replace_callback('#<method>([a-z0-9_]+)</method>#xmi', array(&$this, '_doReplaceMethod_callback_atx'), $text);

        return $text;
    }

    function _doReplaceClass_callback_atx($matches)
    {

    }
}
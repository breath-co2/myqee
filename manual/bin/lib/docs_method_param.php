<?php

/**
 * Class method parameter documentation generator.
 *
 * @package    Kohana/Userguide
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class _Docs_Method_Param extends _Docs
{

    /**
     * @var ReflectionParameter for this property
     */
    public $param;

    public function __construct($method, $param)
    {
        $this->param = new ReflectionParameter($method, $param);

        $this->data['name'] = $this->param->name;

        if ($this->param->isDefaultValueAvailable())
        {
            $this->data['default'] = self::dump($this->param->getDefaultValue());
        }

        if ($this->param->isPassedByReference())
        {
            $this->data['reference'] = true;
        }

        if ($this->param->isOptional())
        {
            $this->data['optional'] = true;
        }
    }

    public function get_html()
    {
        $display = '';

        if ($this->data['type'])
        {
            $display .= '<small>' . $this->data['type'] . '</small> ';
        }

        if ($this->data['reference'])
        {
            $display .= '<small><abbr title="passed by reference">&</abbr></small> ';
        }

        if ($this->data['description'])
        {
            $display .= '<span class="param" data-toggle="tooltip" title="' . $this->data['description'] . '">$' . $this->data['name'] . '</span> ';
        }
        else
        {
            $display .= '$' . $this->data['name'] . ' ';
        }

        if ($this->data['default'])
        {
            $display .= '<small>= ' . $this->data['default'] . '</small> ';
        }

        return $display;
    }

    public function get_text()
    {
        $display = '';

        if ($this->data['reference'])
        {
            $display .= '& ';
        }

        $display .= '$' . $this->data['name'] . ' ';

        if ($this->data['default'])
        {
            $v = $this->param->getDefaultValue();
            $display .= '= ' . (null===$v?'null':var_export($v, true));
        }

        return $display;
    }
}

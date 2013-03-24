<?php
class _Docs_Method extends _Docs
{
    /**
     *
     * @var ReflectionClass The ReflectionClass for this class
     */
    public $class;

    /**
     *
     * @var ReflectionMethod The ReflectionMethod for this class
     */
    public $method;

    public function __construct($class, $method)
    {
        if (is_object($class))
        {
            $this->class = $class;
            $class_name = $class->name;
        }
        else
        {
            $this->class  = new ReflectionClass($class);
            $class_name   = $class;
        }

        $this->method = new ReflectionMethod($class_name, $method);

        $this->data['name']       = $method;
        $this->data['class_name'] = $this->method->class;

        $this->class = $parent = $this->method->getDeclaringClass();

        if ($modifiers = $this->method->getModifiers())
        {
            $this->data['modifiers'] = implode(' ', Reflection::getModifierNames($modifiers));
        }

        do
        {
            if ($parent->hasMethod($method) && $comment = $parent->getMethod($method)->getDocComment())
            {
                // Found a description for this method
                break;
            }
        }
        while ($parent = $parent->getParentClass());

        list ($description, $tags) = self::parse($comment);
        $this->data['title']       = $description[0];
        $this->data['description'] = trim(implode("\n", $description));

//         if ($file = $this->class->getFileName())
//         {
//             $this->data['source'] = self::source($file, $this->method->getStartLine(), $this->method->getEndLine());
//         }

        if (isset($tags['param']))
        {
            $params = array();

            foreach ($this->method->getParameters() as $i => $param)
            {
                $param = new _Docs_Method_Param(array($this->method->class, $this->method->name), $i);

                if (isset($tags['param'][$i]))
                {
                    preg_match('/^(\S+)(?:\s*(?:\$' . $param->data['name'] . '\s*)?(.+))?$/', $tags['param'][$i], $matches);

                    $param->data['type'] = $matches[1];

                    if (isset($matches[2]))
                    {
                        $param->data['description'] = $matches[2];
                    }
                }

                $param->data['html'] = $param->get_html();

                $params[] = $param->getArrayCopy();
            }

            $this->data['params'] = $params;

            unset($tags['param']);
        }

        if (isset($tags['return']))
        {
            foreach ($tags['return'] as $return)
            {
                if (preg_match('/^(\S*)(?:\s*(.+?))?$/', $return, $matches))
                {
                    $this->data['return'][] = array($matches[1], isset($matches[2]) ? $matches[2] : '');
                }
            }

            unset($tags['return']);
        }
        $this->data['tags']          = $tags;
        $this->data['start_line']    = $this->method->getStartLine();
        $this->data['end_line']      = $this->method->getEndLine();
        $this->data['file_name']     = $this->class->getFileName();
        $this->data['debug_file']    = $this->data['file_name']?Core::debug_path($this->data['file_name']):false;
        $this->data['is_static']     = $this->method->isStatic();
        $this->data['is_public']     = $this->method->isPublic();
        if ($this->data['params'])
        {
            $this->data['params_short']  = $this->params_short();
        }
    }

    protected function params_short()
    {
        $out = '';
        $required = true;
        $first = true;
        foreach ($this->data['params'] as $param)
        {
            if ($required && $param['default'] && $first)
            {
                $out .= '[ ' . $param['html'];
                $required = false;
                $first = false;
            }
            elseif ($required && $param['default'])
            {
                $out .= '[, ' . $param['html'];
                $required = false;
            }
            elseif ($first)
            {
                $out .= $param['html'];
                $first = false;
            }
            else
            {
                $out .= ', ' . $param['html'];
            }
        }

        if (!$required)
        {
            $out .= '] ';
        }

        return $out;
    }
}
<?php

class _Docs_Class extends _Docs
{

    /**
     * @var  ReflectionClass The ReflectionClass for this class
     */
    public $class;

    /**
     * Loads a class and uses [reflection](http://php.net/reflection) to parse
     * the class. Reads the class modifiers, constants and comment. Parses the
     * comment to find the description and tags.
     *
     * @param   string   class name
     * @return  void
     */
    public function __construct($class)
    {
        $this->class = new ReflectionClass($class);

        if ($modifiers = $this->class->getModifiers())
        {
            $this->data['modifiers'] = implode(' ', Reflection::getModifierNames($modifiers));
        }

        $this->data['constants'] = array();
        if ($constants = $this->class->getConstants())
        {
            foreach ($constants as $name => $value)
            {
                $this->data['constants'][$name] = self::dump($value);
            }
        }

        $parent = $this->class;

        do
        {
            if ($comment = $parent->getDocComment())
            {
                // Found a description for this class
                break;
            }
        }
        while ($parent = $parent->getParentClass());

        list ($description, $this->data['tags']) = self::parse($comment);

        $parent = $this->class;
        $parents = array();
        while ($parent = $parent->getParentClass())
        {
            if (substr(strtolower($parent->name), 0, 3)=='ex_')
            {
                # 扩展类或略
                continue;
            }

            $rf = new ReflectionClass($parent->name);
            $parents[] = array
            (
                'class_name'   => $parent->name,
                'is_php_class' => $rf->getStartLine()?0:1,
            );
        }
        $this->data['parents'] = $parents;

        $this->data['class_name']        = $this->class->getName();
        $this->data['title']             = $description[0];
        $this->data['is_php_class']      = $this->class->getStartLine()?0:1;
        $this->data['description']       = trim(implode("\n", $description));
        $this->data['properties']        = $this->properties();
        $this->data['methods']           = $this->methods();
        $this->data['dir_type']          = _DOC_DIR_TYPE;
    }

    /**
     * Gets a list of the class properties as [Kodoc_Property] objects.
     *
     * @return  array
     */
    public function properties()
    {
        $props = $this->class->getProperties();

        //sort($props);

        $props_array = array();
        foreach ($props as $key => $property)
        {
            // Only show public properties, because Reflection can't get the private ones
            $tmp = new _Docs_Property($this->class->name, $property->name);
            $props_array[$property->name] = $tmp->getArrayCopy();
                /*
                if (false)$property = new ReflectionProperty();
                $comment = $property->getDocComment();
                list ($description, $tags) = self::parse($comment);

                if (isset($tags['var']))
                {
                    if (preg_match('/^(\S*)(?:\s*(.+?))?$/', $tags['var'][0], $matches))
                    {
                        $type = $matches[1];

                        if (isset($matches[2]))
                        {
                            $description = array($matches[2]);
                        }
                    }
                }

                $props[$key] = array
                (
                    'title'       => $description[0],
                    'description' => trim(implode("\n", $description)),
                    'modifiers'   => trim(($property->isPrivate()?'private':'protected').($property->isStatic()?' static':'')),
                    'type'        => '',
                    'name'        => $property->name,
                    'class_name'  => $this->class->name,
                    'is_static'   => $property->isStatic(),
                    'type'        => $type,
                );
                */
        }

        return $props_array;
    }

    /**
     * Gets a list of the class properties as [Kodoc_Method] objects.
     *
     * @return  array
     */
    public function methods()
    {
        $methods = $this->class->getMethods();

        //usort($methods, array($this, '_method_sort'));

        $tmpArr = array();
        #当implements一些接口后，会导致出现重复的方法
        $out_methods = array();
        foreach ($methods as $key => $method)
        {
            $method_name = $method->name;
            if (isset($tmpArr[$this->class->name][$method_name]))continue;
            $tmpArr[$this->class->name][$method_name] = true;
            $obj = new _Docs_Method($this->class, $method_name);
            if ($obj->method->isPrivate() && $obj->class->name!=$this->class->name)
            {
                // 非被函数的私有方法忽略
            }
            else
            {
                $out_methods[$method_name] = $obj->getArrayCopy();
            }
        }

        return $out_methods;
    }

    protected function _method_sort($a, $b)
    {

        // If both methods are defined in the same class, just compare the method names
        if ($a->class->name == $b->class->name)return strcmp($a->name, $b->name);

        // If one of them was declared by this class, it needs to be on top
        if ($a->name == $this->class->name)return -1;
        if ($b->name == $this->class->name)return 1;

        // Otherwise, get the parents of each methods declaring class, then compare which function has more "ancestors"
        $adepth = 0;
        $bdepth = 0;

        $parent = $a->getDeclaringClass();
        do
        {
            $adepth++;
        }
        while ($parent = $parent->getParentClass());

        $parent = $b->getDeclaringClass();
        do
        {
            $bdepth++;
        }
        while ($parent = $parent->getParentClass());

        return $bdepth - $adepth;
    }
}
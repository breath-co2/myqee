<?php

/**
 * Class property documentation generator.
 *
 * @package    Kohana/Userguide
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class _Docs_Property extends _Docs
{
    public function __construct($class, $property)
    {
        $property = new ReflectionProperty($class, $property);

        list ($description, $tags) = self::parse($property->getDocComment());

        $this->data['title']       = $description[0];
        $this->data['description'] = trim(implode("\n", $description));

        if ($modifiers = $property->getModifiers())
        {
            $this->data['modifiers'] = implode(' ', Reflection::getModifierNames($modifiers));
        }
        else
        {
            $this->data['modifiers'] = 'public';
        }

        if (isset($tags['var']))
        {
            if (preg_match('/^(\S*)(?:\s*(.+?))?$/', $tags['var'][0], $matches))
            {
                $this->data['type'] = $matches[1];

                if (isset($matches[2]))
                {
                    $this->data['description'] = array($matches[2]);
                }
            }
        }

        $this->data['name']       = $property->name;
        $this->data['class_name'] = $property->class;
        $this->data['is_static']  = $property->isStatic();
        $this->data['is_public']  = $property->isPublic();

        $class_rf = $property->getDeclaringClass();

        if ($property->class!=$class)
        {
            $this->data['is_php_class']  = $class_rf->getStartLine()?0:1;
        }
        else
        {
            $this->data['is_php_class'] = false;
        }

        $have_value = false;
        if ($property->isStatic())
        {
            $v = $class_rf->getStaticProperties();
            if (isset($v[$property->name]))
            {
                $value = $v[$property->name];
                $have_value = true;
            }
        }
        else if (!$property->isPrivate())
        {
            if (!$class_rf->isFinal() && !$class_rf->isAbstract())
            {
                $value = self::getValue($class, $property->name);
                $have_value = true;
            }
        }

        if ($have_value)
        {
            $this->data['value'] = self::dump($value);
            $this->data['value_serialized'] = serialize($value);
        }
    }

    /**
     * 获取非private字段的信息
     *
     * @param string $class_name
     * @param string $property_name
     */
    protected static function getValue($class_name, $property_name)
    {
        static $objs = array();

        if (!isset($objs[$class_name]))
        {
            $tmp_class_name = '_temp_for_rf_'.$class_name;
            $str = eval('class '.$tmp_class_name.' extends '.$class_name.'{
                function __construct(){}
                function __destruct(){}
                public function __temp_get_my_default_value__($key){
                    try
                    {
                        return $this->$key;
                    }catch(Exception $e) {
                        return null;
                    }
                }
            }');
            $objs[$class_name] = new $tmp_class_name();
        }

        $obj = $objs[$class_name];

        return $obj->__temp_get_my_default_value__($property_name);
    }
}
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
        if ($property->class!=$class)
        {
            $rs  = new ReflectionClass($property->class);
            $this->data['is_php_class']  = $rs->getStartLine()?0:1;
        }
        else
        {
            $this->data['is_php_class'] = false;
        }

        if ($property->isStatic() && $property->isPublic())
        {
            $this->data['value'] = self::dump($property->getValue($class));
        }
    }
}
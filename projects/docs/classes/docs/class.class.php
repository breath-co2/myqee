<?php

class Docs_Class extends Docs
{

    /**
     * @var  ReflectionClass The ReflectionClass for this class
     */
    public $class;

    /**
     * @var  string  modifiers like abstract, final
     */
    public $modifiers;

    /**
     * @var  string  description of the class from the comment
     */
    public $description;

    /**
     * @var  array  array of tags, retrieved from the comment
     */
    public $tags = array();

    /**
     * @var  array  array of this classes constants
     */
    public $constants = array();

    /**
     * Loads a class and uses [reflection](http://php.net/reflection) to parse
     * the class. Reads the class modifiers, constants and comment. Parses the
     * comment to find the description and tags.
     *
     * @param   string   class name
     * @return  void
     */
    public function __construct( $class )
    {
        $this->class = new ReflectionClass( $class );
        
        if ( $modifiers = $this->class->getModifiers() )
        {
            $this->modifiers = '<small>' . implode( ' ', Reflection::getModifierNames( $modifiers ) ) . '</small> ';
        }
        
        if ( $constants = $this->class->getConstants() )
        {
            foreach ( $constants as $name => $value )
            {
                $this->constants[$name] = Docs::debug( $value );
            }
        }
        
        $parent = $this->class;
        
        do
        {
            if ( $comment = $parent->getDocComment() )
            {
                // Found a description for this class
                break;
            }
        }
        while ( $parent = $parent->getParentClass() );
        
        list ( $this->description, $this->tags ) = Docs::parse( $comment );
    }

    /**
     * Gets a list of the class properties as [Kodoc_Property] objects.
     *
     * @return  array
     */
    public function properties()
    {
        $props = $this->class->getProperties();
        
        sort( $props );
        
        foreach ( $props as $key => $property )
        {
            // Only show public properties, because Reflection can't get the private ones
            if ( $property->isPublic() )
            {
                $props[$key] = new Docs_Property( $this->class->name, $property->name );
            }
            else
            {
                unset( $props[$key] );
            }
        }
        
        return $props;
    }

    /**
     * Gets a list of the class properties as [Kodoc_Method] objects.
     *
     * @return  array
     */
    public function methods()
    {
        static $s_methods = array();
        if ( isset( $s_methods[$this->class->name] ) )
        {
            return $s_methods[$this->class->name];
        }
        $methods = $this->class->getMethods();
        
        usort( $methods, array( $this, '_method_sort' ) );
        
        $tmpArr = array();
        #当implements一些接口后，会导致出现重复的方法
        $out_methods = array();
        foreach ( $methods as $key => $method )
        {
            if ( isset( $tmpArr[$this->class->name][$method->name] ) ) continue;
            $tmpArr[$this->class->name][$method->name] = true;
            $out_methods[] = new Docs_Method( $this->class->name, $method->name );
        }
        $s_methods[$this->class->name] = $out_methods;
        return $out_methods;
    }

    protected function _method_sort( $a, $b )
    {
        
        // If both methods are defined in the same class, just compare the method names
        if ( $a->class == $b->class ) return strcmp( $a->name, $b->name );
        
        // If one of them was declared by this class, it needs to be on top
        if ( $a->name == $this->class->name ) return - 1;
        if ( $b->name == $this->class->name ) return 1;
        
        // Otherwise, get the parents of each methods declaring class, then compare which function has more "ancestors"
        $adepth = 0;
        $bdepth = 0;
        
        $parent = $a->getDeclaringClass();
        do
        {
            $adepth ++;
        }
        while ( $parent = $parent->getParentClass() );
        
        $parent = $b->getDeclaringClass();
        do
        {
            $bdepth ++;
        }
        while ( $parent = $parent->getParentClass() );
        
        return $bdepth - $adepth;
    }
}
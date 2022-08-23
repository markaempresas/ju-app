<?php
/**
 * @file
 * The base class for specific structured data classes.
 */

namespace Drupal\chart_suite\SDSC\StructuredData;





/**
 * @class AbstractData
 * AbstractData is an abstract base class that provides core functionality
 * used by multiple container classes, including Table, Tree, and Graph.
 *
 * #### Data attributes
 * Data objects have an associative array of attributes that
 * provide descriptive metadata for the data content.  Applications may
 * add any number and type of attributes, but this class, and others in
 * this package, recognize a few well-known attributes:
 *
 *  - 'name' (string) is a brief name of the data
 *  - 'longName' (string) is a longer more human-friendly name of the data
 *  - 'description' (string) is a block of text describing the data
 *  - 'sourceFileName' (string) is the name of the source file for the data
 *  - 'sourceMIMEType' (string) is the source file mime type
 *  - 'sourceSchemaName' (string) is the name of a source file schema
 *  - 'sourceSyntax' (string) is the source file base syntax
 *
 * All attributes are optional.
 *
 * The 'name' may be an abbreviation or acronym, while the 'longName' may
 * spell out the abbreviation or acronym.
 *
 * The 'description' may be a block of text containing several unformatted
 * sentences describing the data.
 *
 * When the data originates from a source file, the 'sourceFileName' may
 * be the name of the file. If that file's syntax does not provide a name
 * for the data, the file's name, without extensions, may be used to set
 * the name.
 *
 * In addition to the source file name, the file's MIME type may be set
 * in 'sourceMIMEType' (e.g. 'application/json'), and the equivalent file
 * syntax in 'sourceSyntax' e.g. 'json'). If the source file uses a specific
 * schema, the name of that schema is in 'sourceSchemaName' (e.g.
 * 'json-table').
 *
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    2/8/2016
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 */
abstract class AbstractData
{
//----------------------------------------------------------------------
// Fields
//----------------------------------------------------------------------
    /**
     * @var  array $attributes
     * An associative array of named data attributes.
     */
    private $attributes;


//----------------------------------------------------------------------
// Constants
//----------------------------------------------------------------------
    /**
     * @var array WELL_KNOWN_ATTRIBUTES
     * An associative array where the keys are the names of well-known
     * data attributes.
     */
    private static $WELL_KNOWN_ATTRIBUTES = array(
        'name' => 1,
        'longName' => 1,
        'description' => 1,
        'sourceFileName' => 1,
        'sourceMIMEType' => 1,
        'sourceSyntax' => 1,
        'sourceSchemaName' => 1
    );

    private static $ERROR_attributes_argument_invalid =
        'Data attributes must be an array or object.';
    private static $ERROR_attribute_key_invalid =
        'Data attribute keys must be non-empty strings.';
    private static $ERROR_attribute_wellknown_key_value_invalid =
        'Data attribute values for well-known keys must be strings.';





//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs an empty object with the given initial attribute values.
     *
     * @param   array $attributes  an associatve array of data attributes.
     *
     * @return  object             returns a new empty object with the
     * provided attributes.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function __construct( $attributes = NULL )
    {
        $this->attributes = array( );
        if ( !is_null( $attributes ) )
            $this->setAttributes( $attributes );
    }
    // @}

    /**
     * @name Destructors
     */
    // @{
    /**
     * Destroys the previously constructed table.
     */
    public function __destruct( )
    {
        // No action required.
    }
    // @}
    //----------------------------------------------------------------------
    // Utilities
    //----------------------------------------------------------------------
    /**
     * @name Utilities
     */
    // @{
    /**
     * Returns an array of keywords built by tokenizing the given text
     * and removing all punctuation and stand-alone numbers, then splitting
     * the text on white space.
     *
     * All text is converted to lower case. The returned array is sorted
     * alphabetically, in a natural order, and duplicate words removed.
     *
     * This method is primarily used to build keyword lists.
     *
     * @param   string  $text   the text to tokenize into keywords
     *
     * @return  array           the array of keywords.
     */
    protected function textToKeywords( &$text )
    {
        // 1. Replace all punctuation with spaces, except for - and _.
        //
        // This will leave text with space-delimited words, where each
        // word is composed of alpha, numeric, -, and _ characters,
        // in any combination (e.g. "word", "word42", "42word", "wo-rd",
        // "wo_rd", "_word", "-word", "42", "-42", "42_", etc.).
        //
        // Note that floating-point numbers will be turned into two
        // tokens when the decimal is removed. This is OK because we
        // will be removing pure numeric tokens below (e.g. "42.5" becomes
        // "42 5", and "myfile.png" becomes "myfile png").
        $t = preg_replace( '/[^\w-]+/', ' ', strtolower( $text ) );


        // 2. Tokenize by splitting on white space.
        //
        // This produces an array of words. Empty words are skipped.
        $words = preg_split( '/[\s]+/', $t, -1, PREG_SPLIT_NO_EMPTY );


        // 3. Remove purely numeric tokens.
        //
        // Run through the list and remove any word that is numeric.
        foreach ( $words as $key => $word )
        {
            if ( is_numeric( $word ) )
                unset( $words[$key] );
        }


        // 4. Sort the keywords and remove duplicates.
        sort( $words, SORT_NATURAL | SORT_FLAG_CASE );
        return array_unique( $words );
    }

    /**
     * Returns a text representation of the given value, intended
     * for future use in building keyword lists, such as for search
     * indexes.
     *
     * String values are returned as-is. Numeric, boolean, null, and
     * other scalar types are ignored and returned as an empty
     * string (since these values do not contribute to keyword lists).
     * Array and object values are converted to a string representation.
     *
     * Returned text may include punctuation and numbers, even though
     * pure non-string values are ignored. For example, a string value
     * may read "42" and will be returned, and an array of numbers will
     * be returned as text containing those numbers. It is up to the
     * caller to do further filtering of the text to remove all numbers
     * and punctuation.
     *
     * @param   mixed   $value  the value to convert to text
     *
     * @return  string          the text version of the value, if any
     */
    protected function valueToText( &$value )
    {
        if ( is_scalar( $value ) )
        {
            // Return scalar strings as-is. Ignore all other scalars,
            // including booleans, integers, doubles, and NULLs.
            if ( is_string( $value ) )
                return $value;
            return '';
        }

        if ( is_object( $value ) &&
            method_exists( $value, "__toString" ) )
        {
            // Convert objects that support string conversion to
            // strings. This gives the object's class a chance to
            // present the object well. It may still include
            // punctuation and numbers.
            return strval( $value );
        }

        if ( is_resource( $value ) )
        {
            // There is no useful way to dump a resource. Return
            // nothing.
            return '';
        }

        // Dump arrays and objects. When dumped, we
        // get repeated use of keywords like "Array" and "Object".
        // Delete these before returning the text.
        return preg_replace( '/(Array|Object)/', '',
            var_export( $value, true ) );
    }
    // @}
    //----------------------------------------------------------------------
    // Data attributes methods
    //----------------------------------------------------------------------
    /**
     * @name Data attributes methods
     */
    // @{
    /**
     * Clears all data attributes without affecting any other data
     * content.
     *
     * Example:
     * @code
     *   $data->clearAttributes( );
     * @endcode
     */
    public function clearAttributes( )
    {
        $this->attributes = array( );
    }

    /**
     * Returns a copy of the selected data attribute, or a NULL if there is
     * no such attribute.
     *
     * Attribute keys must be strings. The data type of attribute values
     * varies, but all well-known attributes have string values.
     *
     * Example:
     * @code
     *   $value = $data->getAttribute( 'name' );
     * @endcode
     *
     * @param string $key  the key for a data attribute to query.
     *
     * @return mixed  returns the value for the data attribute, or a
     * NULL if there is no such attribute. The returned value may be of
     * any type, but it is typically a string or number.
     *
     * @throws \InvalidArgumentException  if $key is not a string or is empty.
     *
     * @see getAttributes( ) to get an associative array containing a
     * copy of all data attributes.
     */
    public function getAttribute( $key )
    {
        // Validate argument.
        if ( !is_string( $key ) || empty( $key ) )
            throw new \InvalidArgumentException(
                self::$ERROR_attribute_key_invalid );

        if ( !isset( $this->attributes[$key] ) )
            return NULL;                    // Key not found
        return $this->attributes[$key];
    }

    /**
     * Returns an array of keywords found in the data's attributes,
     * including the name, long name, description, and other attributes.
     *
     * Such a keyword list is useful when building a search index to
     * find this data object. The returns keywords array is in
     * lower case, with duplicate words removed, and the array sorted
     * in a natural sort order.
     *
     * The keyword list is formed by extracting all space or punctuation
     * delimited words found in all attribute keys and values. This
     * includes the name, long name, and description attributes, and
     * any others added by the application. Well known attribute
     * names, such as 'name', 'longName', etc., are not included, but
     * application-specific attribute names are included.
     *
     * Numbers and punctuation are ignored. Array and object attribute
     * values are converted to text and then scanned for words.
     *
     * @return array  returns an array of keywords.
     */
    public function getAttributeKeywords( )
    {
        // Add all attribute keys and values.
        $text = '';
        foreach ( $this->attributes as $key => &$value )
        {
            // Add the key. Skip well-known key names.  Intelligently
            // convert to text.
            if ( !isset( self::$WELL_KNOWN_ATTRIBUTES[$key] ) )
                $text .= ' ' . $this->valueToText( $key );

            // Add the value.  Intelligently convert to text.
            $text .= ' ' . $this->valueToText( $value );
        }

        // Clean the text of numbers and punctuation, and return
        // an array of keywords.
        return $this->textToKeywords( $text );
    }

    /**
     * Returns an associative array containing a copy of all data attributes.
     *
     * If the data has no attributes, an empty array is returned.
     *
     * Example:
     * @code
     *   $attributes = $data->getAttributes( );
     *   foreach ( $attributes as $key => $value )
     *   {
     *     print( "$key = $value\n" );
     *   }
     * @endcode
     *
     * @return array  returns an associative array of data attributes.
     */
    public function getAttributes( )
    {
        return $this->attributes;
    }

    /**
     * Returns a "best" data name by checking for, in order, the long name,
     * short name, and file name, and returning the first non-empty value
     * found, or an empty string if all of those are empty.
     *
     * Example:
     * @code
     *   $bestName = $data->getBestName( );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * checking each of the long name, name, and file name attributes
     * in order.
     *
     * @return  the best name, or an empty string if there is no name
     */
    public function getBestName( )
    {
        if ( !empty( $this->attributes['longName'] ) )
            return strval( $this->attributes['longName'] );
        if ( !empty( $this->attributes['name'] ) )
            return strval( $this->attributes['name'] );
        if ( !empty( $this->attributes['sourceFileName'] ) )
            return strval( $this->attributes['sourceFileName'] );
        return '';
    }

    /**
     * Returns the data description, or an empty string if there is
     * no description.
     *
     * Example:
     * @code
     *   $description = $data->getDescription( );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the data's 'description' attribute.
     *
     * @return  the data description, or an empty string if there is no
     * description
     */
    public function getDescription( )
    {
        if ( !isset( $this->attributes['description'] ) )
            return '';
        return strval( $this->attributes['description'] );
    }

    /**
     * Returns the data long name, or an empty string if there is no long name.
     *
     * Example:
     * @code
     *   $longName = $data->getLongName( );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the data's 'longName' attribute.
     *
     * @return  the data long name, or an empty string if there is no long name
     */
    public function getLongName( )
    {
        if ( !isset( $this->attributes['longName'] ) )
            return '';
        return strval( $this->attributes['longName'] );
    }

    /**
     * Returns the data name, or an empty string if there is no name.
     *
     * Example:
     * @code
     *   $name = $data->getName( );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the data's 'name' attribute.
     *
     * @return  the data name, or an empty string if there is no name
     */
    public function getName( )
    {
        if ( !isset( $this->attributes['name'] ) )
            return '';
        return strval( $this->attributes['name'] );
    }

    /**
     * Returns the data source file name, or an empty string if there
     * is no source file name.
     *
     * Example:
     * @code
     *   $name = $data->getSourceFileName( );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the data's 'sourceFileName' attribute.
     *
     * @return  the source file name, or an empty string if there is no name
     */
    public function getSourceFileName( )
    {
        if ( !isset( $this->attributes['sourceFileName'] ) )
            return '';
        return strval( $this->attributes['sourceFileName'] );
    }






    /**
     * Sets the value for the selected data attribute, overwriting any
     * prior value or adding the attribute if it was not already present.
     *
     * Attribute keys must be strings.
     *
     * Attribute values for well-known attributes must be strings.
     *
     * Example:
     * @code
     *   $data->setAttribute( 'name', 'MyData' );
     * @endcode
     *
     * @param   string  $key    the key of a data attribute.
     *
     * @param   mixed   $value  the new value for the selected data attribute.
     *
     * @throws \InvalidArgumentException  if $key is not a string or is empty,
     * or if $value is not a string when $key is one of the well-known
     * attributes.
     */
    public function setAttribute( $key, $value )
    {
        // Validate argument.
        if ( !is_string( $key ) || empty( $key ) )
            throw new \InvalidArgumentException(
                self::$ERROR_attribute_key_invalid );
        if ( isset( self::$WELL_KNOWN_ATTRIBUTES[$key] ) &&
            !is_string( $value ) )
            throw new \InvalidArgumentException(
                self::$ERROR_attribute_wellknown_key_value_invalid );

        $this->attributes[$key] = $value;
    }

    /**
     * Sets the values for the selected data attributes, overwriting any
     * prior values or adding attributes if they were not already present.
     *
     * Attribute keys must be strings.
     *
     * Attribute values for well-known attributes must be strings.
     *
     * Example:
     * @code
     *   $attributes = array(
     *     'name' => 'MyData',
     *     'description' => 'really cool data!' );
     *   $data->setAttributes( $attributes );
     * @endcode
     *
     * @param   array $attributes  an associatve array of data attributes.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function setAttributes( $attributes )
    {
        // Validate
        if ( !is_array( $attributes ) && !is_object( $attributes ) &&
            $attributes != NULL )
            throw new \InvalidArgumentException(
                self::$ERROR_attributes_argument_invalid );
        if ( empty( $attributes ) )
            return;                     // Request to set nothing

        // Convert object argument to an array, if needed.
        $a = (array)$attributes;

        // Insure keys are all strings and all well-known key values
        // are strings.
        foreach ( $a as $key => $value )
        {
            if ( !is_string( $key ) || empty( $key ) )
                throw new \InvalidArgumentException(
                    self::$ERROR_attribute_key_invalid );
            if ( isset( self::$WELL_KNOWN_ATTRIBUTES[$key] ) &&
                !is_string( $value ) )
                throw new \InvalidArgumentException(
                    self::$ERROR_attribute_wellknown_key_value_invalid );
        }

        // Set.
        foreach ( $a as $key => $value )
        {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Sets the data description.
     *
     * Example:
     * @code
     *   $data->setDescription( "This is a description" );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * setting the data's 'description' attribute.
     *
     * @param   string  $description the data description.
     */
    public function setDescription( $description )
    {
        $this->attributes['description'] = strval( $description );
    }

    /**
     * Sets the data long name.
     *
     * Example:
     * @code
     *   $data->setLongName( "Long name" );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * setting the data's 'longName' attribute.
     *
     * @param   string  $longname  the data long name.
     */
    public function setLongName( $longname )
    {
        $this->attributes['longName'] = strval( $longname );
    }

    /**
     * Sets the data name.
     *
     * Example:
     * @code
     *   $data->setName( "Name" ;
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * setting the data's 'name' attribute.
     *
     * @param   string  $name  the data name.
     */
    public function setName( $name )
    {
        $this->attributes['name'] = strval( $name );
    }

    /**
     * Sets the source file name.
     *
     * Example:
     * @code
     *   $data->setSourceFileName( "myfile.json" ;
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * setting the data's 'sourceFileName' attribute.
     *
     * @param   string  $name  the source file name.
     */
    public function setSourceFileName( $name )
    {
        $this->attributes['sourceFileName'] = strval( $name );
    }
    // @}
}
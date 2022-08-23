<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Table to manage a table containing values
 * arranged in rows and columns, along with metadata describing the
 * table and its columns.
 */

namespace Drupal\chart_suite\SDSC\StructuredData;

/**
 * @class Table
 * Table manages a table containing values arranged in rows and columns,
 * along with metadata describing the table and its columns.
 *
 * #### Table attributes
 * Tables have an associative array of attributes that
 * provide descriptive metadata for the data content.  Applications may
 * add any number and type of attributes, but this class, and others in
 * this package, recognize a few well-known attributes:
 *
 *  - 'name' (string) is a brief name of the data
 *  - 'longName' (string) is a longer more human-friendly name of the data
 *  - 'description' (string) is a block of text describing the data
 *  - 'sourceFileName' (string) is the name of the source file for the data
 *  - 'sourceSyntax' (string) is the source file base syntax
 *  - 'sourceMIMEType' (string) is the source file mime type
 *  - 'sourceSchemaName' (string) is the name of a source file schema
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
 * #### Column attributes
 * Tables have zero or more columns. Each column has an associative array
 * of attributes that provide descriptive metadata for the column. Applications
 * may add any number and type of attributes, but this class, and others in
 * this package, recognize a few well-known attributes:
 *
 *  - 'name' (string) is a brief name of the data
 *  - 'longName' (string) is a longer more human-friendly name of the data
 *  - 'description' (string) is a block of text describing the data
 *  - 'type' (string) is a data type for all values in the column.
 *
 * All attributes are optional.
 *
 * The 'name' may be an abbreviation or acronym, while the 'longName' may
 * spell out the abbreviation or acronym.  The column 'name' is optional
 * but strongly encouraged.  If abscent, classes that format columns for a
 * specific output syntax (e.g. CSV or JSON) will create numbered column
 * names (e.g. '1', '2', etc.).
 *
 * The 'description' may be a block of text containing several unformatted
 * sentences describing the data.
 *
 * The column 'type' is optional and defaults to "any". Well-known type
 * names include:
 *  - 'any' is any type of data
 *  - 'boolean' is 'true' or 'false' values only
 *  - 'number' is floating-point values
 *  - 'integer' is integer values
 *  - 'null' is 'null' values only
 *  - 'string' is strings
 *  - 'date' is dates
 *  - 'time' is times
 *  - 'datetime' is dates with times
 *
 *
 * #### Table rows
 * A table may have zero or more rows of values with one row for each
 * column.
 *
 * Values in a row may be of any data type, but they should generally
 * match the data type indicated in the corresponding column's attributes.
 * Data types are *not* enforced and no conversions take place.
 *
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    1/28/2016
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 *
 * @version 0.0.2  Revised to generalize table and column attributes into
 *   associative arrays instead of explicit attributes; removed column data
 *   type inference and conversion.
 *
 * @version 0.0.3  Revised to rename the 'shortName' attributes to 'name',
 *   add get*Name() method shortcuts, and fix assorted bugs.
 *
 * @version 0.0.4  Revised to subclass AbstractData and throw standard
 *   SPL exceptions.
 */
final class Table
    extends AbstractData
{
//----------------------------------------------------------------------
// Fields
//----------------------------------------------------------------------
    /**
     * @var  array $columnAttributes
     * An array with one associative array per column containing
     * named column attributes. Well-known column attributes include:
     *
     *  - 'name' is the short name of the column
     *  - 'longName' is a longer more human-friendly name of the column
     *  - 'description' is a block of text describing the column
     *  - 'type' is the data type for all values in the column
     *
     *
     * The short name may be an abbreviation or acronym, while the
     * long name may spell out the abbreviation or acronym.  Both
     * names are optional.
     *
     * The description may be a block of text containing several unformatted
     * sentences describing the column. The description is optional.
     *
     * The data type is optional and defaults to "any". Standard type
     * names include:
     *  - 'any' is any type of data
     *  - 'boolean' is 'true' or 'false' values only
     *  - 'number' is floating-point values
     *  - 'integer' is integer values
     *  - 'null' is 'null' values only
     *  - 'string' is strings
     *  - 'date' is dates
     *  - 'time' is times
     *  - 'datetime' is dates with times
     *
     * Each column's attributes array may contain additional application-
     * or file format-specific attributes.
     */
    private $columnAttributes;

    /**
     * @var  array $rows
     * An array with one array per row containing one value for each
     * column in the table. Values are of arbitrary type.
     */
    private $rows;





//----------------------------------------------------------------------
// Constants
//----------------------------------------------------------------------
    /**
     * @var array WELL_KNOWN_COLUMN_ATTRIBUTES
     * An associative array where the keys are the names of well-known
     * column attributes.
     */
    private static $WELL_KNOWN_COLUMN_ATTRIBUTES = array(
        'name' => 1,
        'longName' => 1,
        'description' => 1,
        'type' => 1
    );

    /**
     * @var array WELL_KNOWN_COLUMN_TYPES
     * An associative array where the keys are the names of well-known
     * column types.
     */
    private static $WELL_KNOWN_COLUMN_TYPES = array(
        'any' => 1,
        'boolean' => 1,
        'date' => 1,
        'datetime' => 1,
        'integer' => 1,
        'null' => 1,
        'number' => 1,
        'string' => 1,
        'time' => 1
    );


    private static $ERROR_column_attributes_argument_invalid =
        'Column attributes must be an array or object.';
    private static $ERROR_column_attribute_key_invalid =
        'Column attribute keys must be non-empty strings.';
    private static $ERROR_column_attribute_wellknown_key_value_invalid =
        'Column attribute values for well-known keys must be strings.';
    private static $ERROR_column_attribute_type_invalid =
        'Column type name is not recognized.';

    private static $ERROR_column_index_out_of_bounds =
        'Column index is out of bounds.';
    private static $ERROR_column_count_out_of_bounds =
        'Column count is out of bounds.';


    private static $ERROR_row_index_out_of_bounds =
        'Table row index is out of bounds.';
    private static $ERROR_row_count_out_of_bounds =
        'Table row count is out of bounds.';

    private static $ERROR_column_attributes_invalid =
        'Table column attributes must be an array or object.';

    private static $ERROR_rows_empty =
        'Table row array must not be empty.';
    private static $ERROR_row_invalid =
        'Table row must be an array of values with one value per column.';
    private static $ERROR_rows_invalid =
        'Table rows must be an array of arrays of values.';





//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs an empty table with the given initial attribute values
     * and no rows or columns.
     *
     * @param   array $attributes  an associatve array of data attributes.
     *
     * @return  object             returns a new empty table with the
     * provided attributes.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function __construct( $attributes = NULL )
    {
        parent::__construct( $attributes );

        $this->columnAttributes = array( );
        $this->rows = array( );
    }

    /**
     * Clones the data by doing a deep copy of all attributes and values.
     */
    public function __clone( )
    {
        // For any property that is an object or array, make a
        // deep copy by forcing a serialize, then unserialize.
        foreach ( $this as $key => &$value )
        {
            if ( is_object( $value ) || is_array( $value ) )
                $this->{$key} = unserialize( serialize( $value ) );
        }
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
        parent::__destruct( );
    }
    // @}
    //----------------------------------------------------------------------
    // Column attributes methods
    //----------------------------------------------------------------------
    /**
     * @name Column attributes methods
     */
    // @{
    /**
     * Clears all attributes for the selected column without affecting
     * any other data content.
     *
     * Example:
     * @code
     *   $table->clearColumnAttributes( $index );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     *
     * @see clearRows( ) to clear all of the rows of values in a table
     *   while retaining table and column attributes.
     *
     * @see clearAttributes( ) to clear table attributes while retaining
     *   column attributes and row values.
     */
    public function clearColumnAttributes( $columnIndex )
    {
        // Validate.
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        // Set to an empty array. This retains the notion that there is
        // a column, but with no attributes for the column.
        $this->columnAttributes[$columnIndex] = array( );
    }

    /**
     * Returns the column index of the first column found with the
     * selected name, or a -1 if the column is not found.
     *
     * The table columns are checked one-by-one, in order, looking for
     * the first column with a 'name' or 'longName' attribute
     * with the given name. Column names are looked up with case sensitivity.
     *
     * Example:
     * @code
     *   $index = $table->findColumnByName( 'X' );
     * @endcode
     *
     * @param string $name  the name of a column to look for in the table.
     *
     * @return integer      returns the column index of the first column
     * found with a short or long name that matches, or -1 if not found.
     *
     * @throws \InvalidArgumentException  if $name is not a non-empty string.
     */
    public function findColumnByName( $name )
    {
        // Validate.
        if ( !is_string( $name ) || $name === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_column_attribute_key_invalid );

        // Sweep through the columns to find the requested column.
        $v = (string)$name;
        $n = count( $this->columnAttributes );
        for ( $i = 0; $i < $n; ++$i )
        {
            if ( isset( $this->columnAttributes[$i]['name'] ) )
            {
                $name = $this->columnAttributes[$i]['name'];
                if ( $name == $v )
                    return $i;
            }

            if ( isset( $this->columnAttributes[$i]['longName'] ) )
            {
                $longName = $this->columnAttributes[$i]['longName'];
                if ( $longName == $v )
                    return $i;
            }
        }
        return -1;
    }

    /**
     * Returns a copy of the value of the selected column attribute
     * for the selected column, or NULL if the attribute is not found.
     *
     * Example:
     * @code
     *   $name = $table->getColumnAttribute( $index, 'name' );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @param string $key  the key for a column attribute to query.
     *
     * @return mixed  returns a copy of the value for the selected
     * column and attribute.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     *
     * @throws \InvalidArgumentException  if $key is not a non-empty string.
     */
    public function getColumnAttribute( $columnIndex, $key )
    {
        // Validate.
        if ( !is_string( $key ) || $key === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_column_attribute_key_invalid );
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        // Get
        if ( !isset( $this->columnAttributes[$columnIndex][$key] ) )
            return NULL;                // Key not found
        return $this->columnAttributes[$columnIndex][$key];
    }

    /**
     * Returns an array of keywords found in the column's attributes,
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
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return array  returns an array of keywords.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnAttributeKeywords( $columnIndex )
    {
        // Add all column attribute keys and values for one column.
        $text = '';
        foreach ( $this->columnAttributes[$columnIndex] as $key => &$value )
        {
            // Add the key. Skip well-known key names.  Intelligently
            // convert to text.
            if ( !isset( self::$WELL_KNOWN_COLUMN_ATTRIBUTES[$key] ) )
                $text .= ' ' . $this->valueToText( $key );

            // Add the value.  Intelligently convert to text.
            $text .= ' ' . $this->valueToText( $value );
        }

        // Clean the text of numbers and punctuation, and return
        // an array of keywords.
        return $this->textToKeywords( $text );
    }

    /**
     * Returns an array of keywords found in all column attributes,
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
    public function getAllColumnAttributeKeywords( )
    {
        // Add all column attribute keys and values for all columns.
        $text = '';
        foreach ( $this->columnAttributes as &$att )
        {
            foreach ( $att as $key => &$value )
            {
                // Add the key. Skip well-known key names.  Intelligently
                // convert to text.
                if ( !isset( self::$WELL_KNOWN_COLUMN_ATTRIBUTES[$key] ) )
                    $text .= ' ' . $this->valueToText( $key );

                // Add the value.  Intelligently convert to text.
                $text .= ' ' . $this->valueToText( $value );
            }
        }

        // Clean the text of numbers and punctuation, and return
        // an array of keywords.
        return $this->textToKeywords( $text );
    }

    /**
     * Returns an associative array containing a copy of all attributes
     * for the selected column.
     *
     * If the column has no attributes, an empty array is returned.
     *
     * Example:
     * @code
     *   $attributes = $table->getColumnAttributes( $index );
     *   foreach ( $attributes as $key => $value )
     *   {
     *     print( "$key = $value\n" );
     *   }
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return array  returns an associative array of named attributes
     * associatied with the column, or an empty array if there are no
     * attributes or the column index is out of bounds.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnAttributes( $columnIndex )
    {
        // Validate
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        // Get
        return $this->columnAttributes[$columnIndex];
    }

    /**
     * Returns a "best" column name by checking for, in order, the long name
     * and short name, and returning the first non-empty value
     * found, or the column number if all of those are empty.
     *
     * Example:
     * @code
     *   $bestName = $data->getColumnBestName( $columnIndex );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * checking each of the long name and name attributes in order.
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return  the best name
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnBestName( $columnIndex )
    {
        $v = $this->getColumnAttribute( $columnIndex, 'longName' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        $v = $this->getColumnAttribute( $columnIndex, 'name' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return strval( $columnIndex );
    }

    /**
     * Returns the description of the selected column, or an empty string if
     * the column has no description.
     *
     * Example:
     * @code
     *   $description = $table->getColumnDescription( $index );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the column's 'description' attribute.
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return  the column description, or an empty string if there is
     * no description.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnDescription( $columnIndex )
    {
        $v = $this->getColumnAttribute( $columnIndex, 'description' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns the long name of the selected column, or an empty string if
     * the column has no long name.
     *
     * Example:
     * @code
     *   $longName = $table->getColumnLongName( $index );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the column's 'longName' attribute.
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return  the column long name, or an empty string if there is no
     * long name.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnLongName( $columnIndex )
    {
        $v = $this->getColumnAttribute( $columnIndex, 'longName' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns the name of the selected column, or an empty string if
     * the column has no name.
     *
     * Example:
     * @code
     *   $name = $table->getColumnName( $index );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the column's 'name' attribute.
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return  the column name, or an empty string if there is no name.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnName( $columnIndex )
    {
        $v = $this->getColumnAttribute( $columnIndex, 'name' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns the data type of the selected column, or an empty string if
     * the column has no data type.
     *
     * Example:
     * @code
     *   $type = $table->getColumnType( $index );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * getting the column's 'type' attribute.
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return  the column type, or an empty string if there is no type.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnType( $columnIndex )
    {
        $v = $this->getColumnAttribute( $columnIndex, 'type' );
        if ( is_null( $v ) )
            return '';
        return $v;
    }





    /**
     * Returns true if all values in the selected column are strings.
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return  true if all column values are strings
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function isColumnStrings( $columnIndex )
    {
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        if ( count( $this->rows ) == 0 )
            return false;
        foreach ( $this->rows as &$row )
        {
            if ( !is_string( $row[$columnIndex] ) )
                return false;
        }
        return true;
    }

    /**
     * Returns true if all values in the selected column are numbers
     * (not strings that can be parsed as numbers, or objects with
     * a string representation as a number).
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return  true if all column values are numeric
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function isColumnNumeric( $columnIndex )
    {
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        if ( count( $this->rows ) == 0 )
            return false;
        foreach ( $this->rows as &$row )
        {
            if ( !(is_int( $row[$columnIndex] ) ||
                is_float( $row[$columnIndex] ) ) )
                return false;
        }
        return true;
    }





    /**
     * Sets the value for the selected column attribute for the selected
     * column, overwriting any prior value or adding the attribute if it
     * was not already present.
     *
     * Attribute keys must be strings.
     *
     * Attribute values for well-known attributes must be strings.
     *
     * Attribute values for the 'type' attribute must be one of the
     * well-known type names.
     *
     * Example:
     * @code
     *   $table->setColumnAttribute( $index, 'name', 'Total' );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @param string  $key  the key of a column attribute.
     *
     * @param mixed   $value  the value of a column attribute.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     *
     * @throws \InvalidArgumentException  if $key is not a string or is empty,
     * or if $value is not a string when $key is one of the well-known
     * attributes, or if $key is 'type' but $value is not a well-known
     * data type.
     */
    public function setColumnAttribute( $columnIndex, $key, $value )
    {
        // Validate
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );
        if ( !is_string( $key ) || $key === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_column_attribute_key_invalid );
        if ( isset( self::$WELL_KNOWN_COLUMN_ATTRIBUTES[$key] ) &&
            !is_string( $value ) )
            throw new \InvalidArgumentException(
                self::$ERROR_column_attribute_wellknown_key_value_invalid );
        if ( $key == 'type' &&
            !isset( self::$WELL_KNOWN_COLUMN_TYPES[$value] ) )
            throw new \InvalidArgumentException(
                self::$ERROR_column_attribute_type_invalid );

        $this->columnAttributes[$columnIndex][$key] = $value;
    }

    /**
     * Sets the values for the selected column attributes for the selected
     * column, overwriting any prior values or adding attributes if they
     * were not already present.
     *
     * Attribute keys must be strings.
     *
     * Attribute values for well-known attributes must be strings.
     *
     * Attribute values for the 'type' attribute must be one of the
     * well-known type names.
     *
     * Example:
     * @code
     *   $attributes = array( 'name' => 'Total' );
     *   $table->setColumnAttributes( $index, $attributes );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @param   array $attributes  an associatve array of named
     * attributes associated with the column.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function setColumnAttributes( $columnIndex, $attributes )
    {
        // Validate
        if ( !is_array( $attributes ) && !is_object( $attributes ) &&
            $attributes != NULL )
            throw new \InvalidArgumentException(
                self::$ERROR_column_attributes_argument_invalid );
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );
        if ( empty( $attributes ) )
            return;                     // Request to set with nothing

        // Convert object argument to an array, if needed.
        $a = (array)$attributes;

        // Insure keys are all strings and all well-known key values
        // are strings.
        foreach ( $a as $key => $value )
        {
            if ( !is_string( $key ) || $key === '' )
                throw new \InvalidArgumentException(
                    self::$ERROR_column_attribute_key_invalid );

            if ( isset( self::$WELL_KNOWN_COLUMN_ATTRIBUTES[$key] ) &&
                !is_string( $value ) )
                throw new \InvalidArgumentException(
                    self::$ERROR_column_attribute_wellknown_key_value_invalid );

            if ( $key == 'type' &&
                !isset( self::$WELL_KNOWN_COLUMN_TYPES[$value] ) )
                throw new \InvalidArgumentException(
                    self::$ERROR_column_attribute_type_invalid );
        }

        // Set.
        foreach ( $a as $key => $value )
        {
            $this->columnAttributes[$columnIndex][$key] = $value;
        }
    }
    // @}
    //----------------------------------------------------------------------
    // Table methods
    //----------------------------------------------------------------------
    /**
     * @name Table methods
     */
    // @{
    /**
     * Clears the entire table, removing all rows of values, all table
     * attributes, and all column attributes, leaving an entirely
     * empty table.
     *
     * This method is equivalent to clearing all table attributes, then
     * deleting all columns (and thus all values in all table rows):
     * @code
     *   $table->clearAttributes( );
     *   $table->deleteColumns( 0, $table->getNumberOfColumns( ) );
     * @endcode
     *
     * Example:
     * @code
     *   $table->clear( );
     * @endcode
     *
     * @see clearRows( ) to clear all of the rows of values in a table
     *   while retaining table and column attributes.
     *
     * @see clearAttributes( ) to clear table attributes while retaining
     *   column attributes and row values.
     *
     * @see deleteColumns( ) to delete all columns in the table, including
     *   all values in all rows and all column attributes, while
     *   retaining table attributes.
     */
    public function clear( )
    {
        $this->clearAttributes( );
        $this->rows       = array( );   // Clear all rows of values
        $this->columnAttributes = array( );// Clear all column attributes
    }
    // @}
    //----------------------------------------------------------------------
    // Column operations
    //----------------------------------------------------------------------
    /**
     * @name Column operations
     */
    // @{
    /**
     * Appends a column with the given attributes to the end of the
     * list of columns, and adds a column of values to all rows,
     * initializing values to the given default value.
     *
     * Example:
     * @code
     *   $attributes = array(
     *     'name' => 'X',
     *     'description' => 'X coordinates'
     *   );
     *   $table->appendColumn( $attributes, 0 );
     * @endcode
     *
     * @param   array $attributes  an associatve array of named
     * attributes associated with the column.
     *
     * @param   mixed $defaultValue the default value used to initialize
     * the new column's value in all rows.
     *
     * @return  integer  returns the column index for the new column.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function appendColumn( $attributes = NULL, $defaultValue = 0 )
    {
        // Add the column.
        $columnIndex = count( $this->columnAttributes );
        $this->columnAttributes[] = array( );

        // Set the attributes. This may throw an exception if the
        // attributes are bad.
        try
        {
            $this->setColumnAttributes( $columnIndex, $attributes );
        }
        catch ( \Exception $e )
        {
            // Delete the added column, then rethrow the exception.
            array_splice( $this->columnAttributes, $columnIndex, 1 );
            throw $e;
        }

        // Add a value to all rows.
        foreach ( $this->rows as &$row )
            $row[] = $defaultValue;

        return $columnIndex;
    }

    /**
     * Deletes the selected column, its column attributes, and the
     * corresponding column value from each row in the table.
     *
     * Example:
     * @code
     *   $table->deleteColumn( $index );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     *
     * @see deleteColumns( ) to delete more than one adjacent column.
     */
    public function deleteColumn( $columnIndex )
    {
        $this->deleteColumns( $columnIndex, 1 );
    }

    /**
     * Deletes the selected adjacent columns, their column attributes, and the
     * corresponding values from each row in the table.
     *
     * Example:
     * @code
     *   $table->deleteColumns( $index, $number );
     * @endcode
     *
     * To delete all columns in a table:
     * @code
     *   $table->deleteColumns( 0, $table->getNumberOfColumns( ) );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @param integer $numberOfColumns  the non-negative number of columns to
     * delete.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds,
     * or $numberOfColumns is out of bounds.
     */
    public function deleteColumns( $columnIndex, $numberOfColumns = 1 )
    {
        // Validate
        $nColumns = count( $this->columnAttributes );
        if ( $columnIndex < 0 ||
            $columnIndex >= $nColumns )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );
        if ( $numberOfColumns == 0 )
            return;                     // Nothing to delete
        if ( $numberOfColumns < 0 ||
            ($columnIndex + $numberOfColumns) > $nColumns )
            throw new \OutOfBoundsException(
                self::$ERROR_column_count_out_of_bounds );

        // Delete the columns from the column attributes array.
        array_splice( $this->columnAttributes, $columnIndex, $numberOfColumns );

        // Run through all rows and delete the columns from each row.
        foreach ( $this->rows as &$row )
            array_splice( $row, $columnIndex, $numberOfColumns );
    }

    /**
     * Returns an array of values for the selected column and all rows
     * of the table.
     *
     * Example:
     * @code
     *   $values = $table->getColumnValues( $index );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * an existing table column.
     *
     * @return array  returns an array of values with one value for each
     * table row for the selected column.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     */
    public function getColumnValues( $columnIndex )
    {
        // Validate
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        // Set
        $column = array( );
        foreach ( $this->rows as &$row )
            $column[] = $row[$columnIndex];
        return $column;
    }

    /**
     * Returns the number of columns.
     *
     * Example:
     * @code
     *   $n = $table->getNumberOfColumns( );
     *   for ( $i = 0; $i < $n; ++$i )
     *   {
     *     $name = $table->getColumnName( $i );
     *     print( "$i:  $name\n" )(;
     *   }
     * @endcode
     *
     * @return integer the number of columns.
     */
    public function getNumberOfColumns( )
    {
        return count( $this->columnAttributes );
    }

    /**
     * Inserts a column with the given attributes at the selected
     * column index in the list of columns, and adds a column of values
     * to all rows, initializing values to the given default value.
     *
     * Example:
     * @code
     *   $attributes = array( 'name' => 'Y' );
     *   $table->insertColumn( $index, $attributes, 0 );
     * @endcode
     *
     * @param integer $columnIndex  the non-negative numeric index for
     * a new table column.
     *
     * @param   array $attributes  an associatve array of named
     * attributes associated with the column.
     *
     * @param   mixed $defaultValue the default value used to initialize
     * the new column's value in all rows.
     *
     * @return  integer  returns the column index for the new column.
     *
     * @throws \OutOfBoundsException  if $columnIndex is out of bounds.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function insertColumn( $columnIndex, $attributes = NULL,
                                  $defaultValue = 0 )
    {
        // Validate.
        $nColumns = count( $this->columnAttributes );
        if ( $columnIndex < 0 || $columnIndex > $nColumns )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        // Insert an empty column's attributes.
        if ( $columnIndex == $nColumns )
            $this->columnAttributes[] = array( );
        else
            array_splice( $this->columnAttributes, $columnIndex, 0,
                array( array( ) ) );

        // Set the attributes. This may throw an exception if the
        // attributes are bad.
        try
        {
            $this->setColumnAttributes( $columnIndex, $attributes );
        }
        catch ( \Exception $e )
        {
            // Delete the added column, then rethrow the exception.
            array_splice( $this->columnAttributes, $columnIndex, 1 );
            throw $e;
        }

        // Set.
        if ( $columnIndex == $nColumns )
        {
            foreach ( $this->rows as &$row )
                $row[] = $defaultValue;
        }
        else
        {
            foreach ( $this->rows as &$row )
                array_splice( $row, $columnIndex, 0, array( $defaultValue ) );
        }
        return $columnIndex;
    }

    /**
     * Moves a selected column to a new location before or after the
     * the column.
     *
     * Example:
     * @code
     *   $table->moveColumn( $from, $to );
     * @endcode
     *
     * @param integer $fromColumnIndex  the non-negative numeric index for
     * the existing column to move.
     *
     * @param integer $toColumnIndex  the non-negative numeric index at
     * which the column should be placed.
     *
     * @throws \OutOfBoundsException  if $fromColumnIndex or $toColumnIndex
     * is out of bounds.
     */
    public function moveColumn( $fromColumnIndex, $toColumnIndex )
    {
        // Validate.
        $nColumns = count( $this->columnAttributes );
        if ( $fromColumnIndex < 0 || $fromColumnIndex >= $nColumns )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );
        if ( $toColumnIndex < 0 || $toColumnIndex >= $nColumns )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );
        if ( $fromColumnIndex == $toColumnIndex )
            return;                     // Request to move to same location

        // Move.
        if ( $fromColumnIndex < $toColumnIndex )
        {
            // Move towards end.
            $ca = $this->columnAttributes[$fromColumnIndex];
            for ( $i = $fromColumnIndex; $i < $toColumnIndex; $i++ )
                $this->columnAttributes[$i] = $this->columnAttributes[$i+1];
            $this->columnAttributes[$toColumnIndex] = $ca;

            foreach ( $this->rows as &$row )
            {
                $v = $row[$fromColumnIndex];
                for ( $i = $fromColumnIndex; $i < $toColumnIndex; $i++ )
                    $row[$i] = $this->columnAttributes[$i+1];
                $row[$toColumnIndex] = $v;
            }
        }
        else
        {
            // Move towards start.
            $ca = $this->columnAttributes[$fromColumnIndex];
            for ( $i = $fromColumnIndex; $i > $toColumnIndex; $i-- )
                $this->columnAttributes[$i] = $this->columnAttributes[$i-1];
            $this->columnAttributes[$toColumnIndex] = $ca;

            foreach ( $this->rows as &$row )
            {
                $v = $row[$fromColumnIndex];
                for ( $i = $fromColumnIndex; $i > $toColumnIndex; $i-- )
                    $row[$i] = $this->columnAttributes[$i-1];
                $row[$toColumnIndex] = $v;
            }
        }
    }
    // @}
    //----------------------------------------------------------------------
    // Row operations
    //----------------------------------------------------------------------
    /**
     * @name Row operations
     */
    // @{
    /**
     * Appends a new row of values to the end of the table.
     *
     * The method's $row argument must be an array of values with one
     * value per column in the table. The array's values are copied
     * into a new last row of the table.
     *
     * Example:
     * @code
     *   $row = array( 1, 2, 3 );
     *   $table->appendRow( $row );
     * @endcode
     *
     * @param array $row  an array of values with one value for each
     * table column.
     *
     * @return integer    returns the non-negative row index of the
     * new row.
     *
     * @throws \InvalidArgumentException  if $row is not an array, or if
     * it does not have one value for each table column.
     */
    public function appendRow( $row )
    {
        // Validate.
        if ( !is_array( $row ) ||
            count( $row ) != count( $this->columnAttributes ) )
            throw new \InvalidArgumentException( self::$ERROR_row_invalid );

        // Set.
        $this->rows[] = $row;
        return count( $this->rows ) - 1;
    }

    /**
     * Appends an array of arrays of new row of values to the end of
     * the table.
     *
     * The method's $row argument should be an array of row arrays where
     * each row array has one value per column in the table. The array's
     * values are copied into new rows at the end of the table.
     *
     * Example:
     * @code
     *   $rows = array( );
     *   $rows[] = array( 1, 2, 3 );
     *   $rows[] = array( 4, 5, 6 );
     *   $table->appendRows( $rows );
     * @endcode
     *
     * @param array $rows  an array of arrays of values with one value for each
     * table column.
     *
     * @return integer    returns the non-negative row index of the
     * first new row.
     *
     * @throws \InvalidArgumentException  if $rows is not an array,
     * it's an empty array, if any entry in the array is not an array, or
     * if any array in 4rows does not have one value for each table column.
     */
    public function appendRows( &$rows )
    {
        // Validate.
        if ( !is_array( $rows ) )
            throw new \InvalidArgumentException(
                self::$ERROR_rows_invalid );
        if ( count( $rows ) == 0 )
            throw new \InvalidArgumentException(
                self::$ERROR_rows_empty );

        $nColumns = count( $this->columnAttributes );
        foreach ( $rows as &$row )
            if ( !is_array( $row ) || count( $row ) != $nColumns )
                throw new \InvalidArgumentException(
                    self::$ERROR_row_invalid );

        // Set.
        $newRowIndex = count( $this->rows );
        foreach ( $rows as &$row )
            $this->rows[] = $row;
        return $newRowIndex;
    }

    /**
     * Clears the table of all rows of values while retaining table
     * attributes and column attributes.
     *
     * Example:
     * @code
     *   $table->clearRows( );
     * @endcode
     *
     * This is equivalent to:
     * @code
     *  $table->deleteRows( 0, $table->getNumberOfRows( ) );
     * @endcode
     *
     * @see clearAttributes( ) to clear table attributes
     * @see clearColumnAttributes( ) to clear column attributes
     */
    public function clearRows( )
    {
        $this->rows = array( );
    }

    /**
     * Deletes a selected row from the table.
     *
     * Example:
     * @code
     *   $table->deleteRow( $index );
     * @endcode
     *
     * @param integer $rowIndex the non-negative row index of the row
     * to delete.
     *
     * @throws \OutOfBoundsException  if $rowIndex is out of bounds.
     */
    public function deleteRow( $rowIndex )
    {
        $this->deleteRows( $rowIndex, 1 );
    }

    /**
     * Deletes a selected range of rows from the table.
     *
     * Example:
     * @code
     *   $table->deleteRows( $index, $count );
     * @endcode
     *
     * @param integer $rowIndex the non-negative row index of the
     * first row to delete.
     *
     * @param integer $numberOfRows the non-negative number of rows to
     * delete.
     *
     * @throws \OutOfBoundsException  if $rowIndex is out of bounds, or if
     * $numberOfRows is out of bounds.
     */
    public function deleteRows( $rowIndex, $numberOfRows = 1 )
    {
        // Validate
        $nRows = count( $this->rows );
        if ( $rowIndex < 0 || $rowIndex >= $nRows )
            throw new \OutOfBoundsException(
                self::$ERROR_row_index_out_of_bounds );
        if ( $numberOfRows == 0 )
            return;                     // Nothing to delete
        if ( $numberOfRows < 0 || ($rowIndex + $numberOfRows) >= $nRows )
            throw new \OutOfBoundsException(
                self::$ERROR_row_count_out_of_bounds );

        // Unset.
        array_splice( $this->rows, $rowIndex, $numberOfRows );
    }

    /**
     * Returns the number of rows in the table.
     *
     * Example:
     * @code
     *   $n = $table->getNumberOfRows( );
     * @endcode
     *
     * @return integer  returns the non-negative number of rows in the table.
     */
    public function getNumberOfRows( )
    {
        return count( $this->rows );
    }

    /**
     * Returns an array of keywords found in the table's rows.
     *
     * Such a keyword list is useful when building a search index to
     * find this data object. The returns keywords array is in
     * lower case, with duplicate words removed, and the array sorted
     * in a natural sort order.
     *
     * The keyword list is formed by extracting all space or punctuation
     * delimited words found in all row values.  Numbers and punctuation
     * are ignored. Array and object values are converted to text and
     * then scanned for words.
     *
     * @return array  returns an array of keywords.
     */
    public function getAllRowKeywords( )
    {
        // Add all values for all rows and columns.
        $text = '';
        foreach ( $this->rows as &$row )
        {
            foreach ( $row as &$value )
            {
                // Add the value.  Intelligently convert to text.
                $text .= ' ' . $this->valueToText( $value );
            }
        }

        // Clean the text of numbers and punctuation, and return
        // an array of keywords.
        return $this->textToKeywords( $text );
    }

    /**
     * Returns an array containing a copy of the values in the selected
     * row of the table.
     *
     * Example:
     * @code
     *   $values = $table->getRowValues( $index );
     * @endcode
     *
     * @param integer $rowIndex the non-negative row index of the
     * row to get.
     *
     * @return array  returns an array of values with one value for
     * each column of the table.
     *
     * @throws \OutOfBoundsException  if $rowIndex is out of bounds.
     */
    public function getRowValues( $rowIndex )
    {
        // Validate.
        if ( $rowIndex < 0 || $rowIndex >= count( $this->rows ) )
            throw new \OutOfBoundsException(
                self::$ERROR_row_index_out_of_bounds );

        // Get.
        return $this->rows[$rowIndex];
    }

    /**
     * Inserts a row of values into the table so that the new row
     * has the selected row index.
     *
     * The method's $row argument should be an array of values with one
     * value per column in the table. The array's values are copied
     * into the table.
     *
     * Example:
     * @code
     *   $row = array( 1, 2, 3 );
     *   $table->insertRow( $index, $row );
     * @endcode
     *
     * @param integer $rowIndex the non-negative row index of the
     * row insert point.
     *
     * @param array $row  an array of values with one value for each
     * table column.
     *
     * @return integer    returns the non-negative row index of the
     * new row.
     *
     * @throws \OutOfBoundsException  if $rowIndex is out of bounds.
     *
     * @throws \InvalidArgumentException  if $row is not an array, or if
     * it does not have one value for each table column.
     */
    public function insertRow( $rowIndex, $row )
    {
        $a = array( $row );
        return $this->insertRows( $rowIndex, $a );
    }

    /**
     * Inserts an array of arrays that each contain a row of values to insert
     * into the table so that the first new row has the selected row index.
     *
     * The method's $rows argument should be an array of values with one
     * value per column in the table. The array's values are copied
     * into the table.
     *
     * Example:
     * @code
     *   $rows = array( );
     *   $rows[] = array( 1, 2, 3 );
     *   $rows[] = array( 4, 5, 6 );
     *   $table->insertRows( $index, $rows );
     * @endcode
     *
     * @param integer $rowIndex the non-negative row index of the
     * row insert point.
     *
     * @param array $rows  an array of arrays of values with one value for each
     * table column.
     *
     * @return integer    returns the non-negative row index of the
     * new row.
     *
     * @throws \OutOfBoundsException  if $rowIndex is out of bounds.
     *
     * @throws \InvalidArgumentException  if $rows is not an array,
     * it's an empty array, if any entry in the array is not an array, or
     * if any array in 4rows does not have one value for each table column.
     */
    public function insertRows( $rowIndex, &$rows )
    {
        // Validate.
        $nRows = count( $this->rows );
        if ( $rowIndex < 0 || $rowIndex > $nRows )
            throw new \OutOfBoundsException(
                self::$ERROR_row_index_out_of_bounds );
        if ( !is_array( $rows ) )
            throw new \InvalidArgumentException(
                self::$ERROR_rows_invalid );
        if ( count( $rows ) == 0 )
            throw new \InvalidArgumentException(
                self::$ERROR_rows_empty );

        $nColumns = count( $this->columnAttributes );
        foreach ( $rows as &$row )
            if ( !is_array( $row ) || count( $row ) != $nColumns )
                throw new \InvalidArgumentException(
                    self::$ERROR_row_invalid );

        // Set.
        if ( $rowIndex == $nRows )
            foreach ( $rows as &$row )
                $this->rows[] = $row;
        else
            array_splice( $this->rows, $rowIndex, 0, $rows );
        return $rowIndex;
    }

    /**
     * Moves a selected row of values from the table to a new selected
     * row position.
     *
     * Example:
     * @code
     *   $table->moveRow( $from, $to );
     * @endcode
     *
     * @param integer $fromRowIndex the non-negative row index of the
     * row to move.
     *
     * @param integer $toRowIndex the non-negative row index of the
     * new position of the row.
     *
     * @throws \OutOfBoundsException  if $fromRowIndex or $toRowIndex are
     * out of bounds.
     */
    public function moveRow( $fromRowIndex, $toRowIndex )
    {
        $this->moveRows( $fromRowIndex, $toRowIndex, 1 );
    }

    /**
     * Moves a selected row of values from the table to a new selected
     * row position.
     *
     * Example:
     * @code
     *   $table->moveRows( $from, $to, $count );
     * @endcode
     *
     * @param integer $fromRowIndex the non-negative row index of the
     * row to move.
     *
     * @param integer $toRowIndex the non-negative row index of the
     * new position of the row.
     *
     * @param integer $numberOfRows the positive number of rows to move.
     *
     * @throws \OutOfBoundsException  if $fromRowIndex or $toRowIndex are out
     * of bounds, or if $numberOfRows is out of bounds.
     */
    public function moveRows( $fromRowIndex, $toRowIndex, $numberOfRows = 1 )
    {
        // Validate
        $nRows = count( $this->rows );
        if ( $fromRowIndex < 0 || $fromRowIndex >= $nRows )
            throw new \OutOfBoundsException(
                self::$ERROR_row_index_out_of_bounds );
        if ( $toRowIndex < 0 || $toRowIndex >= $nRows )
            throw new \OutOfBoundsException(
                self::$ERROR_row_index_out_of_bounds );
        if ( $numberOfRows == 0 )
            return;                     // Nothing to move
        if ( $numberOfRows < 0 || ($fromRowIndex + $numberOfRows) > $nRows )
            throw new \OutOfBoundsException(
                self::$ERROR_row_count_out_of_bounds );
        if ( $fromRowIndex == $toRowIndex )
            return;                         // Request to move to same location

        // Move
        $mv = array_splice( $this->rows, $fromRowIndex, $numberOfRows );
        if ( $fromRowIndex < $toRowIndex )
            array_splice( $this->rows, $toRowIndex, 0, $mv );
        else
            array_splice( $this->rows, $toRowIndex, 0, $mv );
    }
    // @}
    //----------------------------------------------------------------------
    // Cell values methods
    //----------------------------------------------------------------------
    /**
     * @name Cell values methods
     */
    // @{
    /**
     * Returns the table value at the selected row and column.
     *
     * Example:
     * @code
     *   $nRows = $table->getNumberOfRows( );
     *   $nCols = $table->getNumberOfColumns( );
     *   for ( $i = 0; $i < $nRows; ++$i )
     *   {
     *     for ( $j = 0; $j < $nCols; ++$j )
     *     {
     *       $v = $table->getValue( $i, $j );
     *       print( "$v " );
     *     }
     *     print( "\n" );
     *   }
     * @endcode
     *
     * @param integer  $rowIndex    the non-negative row index of the
     * row to query.
     *
     * @param integer  $columnIndex the non-negative column index of the
     * column to query.
     *
     * @return mixed   returns the value at the selected row and column.
     *
     * @throws \OutOfBoundsException if $rowIndex or $columnIndex are
     * out of bounds.
     */
    public function getValue( $rowIndex, $columnIndex )
    {
        // Validate.
        if ( $rowIndex < 0 || $rowIndex >= count( $this->rows ) )
            throw new \OutOfBoundsException(
                self::$ERROR_row_index_out_of_bounds );
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        // Get.
        return $this->rows[$rowIndex][$columnIndex];
    }

    /**
     * Sets the table value at the selected row and column to the given
     * value.
     *
     * Example:
     * @code
     *   $nRows = $table->getNumberOfRows( );
     *   $nCols = $table->getNumberOfColumns( );
     *   for ( $i = 0; $i < $nRows; ++$i )
     *   {
     *     for ( $j = 0; $j < $nCols; ++$j )
     *     {
     *       $table->setValue( $i, $j, 0 );
     *     }
     *   }
     * @endcode
     *
     * @param integer  $rowIndex    the non-negative row index of the
     * row to query.
     *
     * @param integer  $columnIndex the non-negative column index of the
     * column to query.
     *
     * @param mixed    $value       the value to be used to set the
     * table value at the selected row and column.
     *
     * @throws \OutOfBoundsException if $rowIndex or $columnIndex are
     * out of bounds.
     */
    public function setValue( $rowIndex, $columnIndex, $value )
    {
        // Validate.
        if ( $rowIndex < 0 || $rowIndex >= count( $this->rows ) )
            throw new \OutOfBoundsException(
                self::$ERROR_row_index_out_of_bounds );
        if ( $columnIndex < 0 ||
            $columnIndex >= count( $this->columnAttributes ) )
            throw new \OutOfBoundsException(
                self::$ERROR_column_index_out_of_bounds );

        // Set.
        $this->rows[$rowIndex][$columnIndex] = $value;
    }
    // @}
}

<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\JSONTableFormat to parse and
 * serialize data in the JSON (JavaScript Object Notation) text syntax
 * for tables.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;


use Drupal\chart_suite\SDSC\StructuredData\Table;


/**
 * @class JSONTableFormat
 * JSONTableFormat provides decode and encode functions that map
 * between JSON (JavaScript Object Notation) text and a
 * Drupal\chart_suite\SDSC\StructuredData\Table.
 *
 * JSON is a general-purpose syntax for describing objects, arrays,
 * scalars, and arrays of objects, arrays, of scalars to an arbitrary
 * nesting depth. This class, however, focuses on a narrower subset of
 * JSON usage in order to build tables.
 *
 *
 * #### Table syntax
 * A JSON table is a list of rows where each row has the same number of
 * columns of values. Each column always has a short name, and in some
 * syntax forms each column also has a long name, description, and data type.
 * In some syntax forms, the table itself may have a short name, long
 * name, and description.
 *
 * There are several syntax variants:
 * - Array of arrays
 * - Array of objects
 * - Object containing metadata, schema, and array of arrays
 * - Object containing metadata, schema, and array of objects
 *
 * ##### Array of arrays
 * JSON tables can be expressed as an array of arrays of scalars (see
 * ENCODE_AS_ARRAY_OF_ARRAYS). Each array gives one row of data. The
 * first array gives column names. All arrays must have the same number
 * of columns.
 * <pre>
 *  [
 *      [ "Column 1", "Column 2", "Column 3" ],
 *      [ 1, 2, 3 ],
 *      [ 4, 5, 6 ]
 *  ]
 * </pre>
 *
 * ##### Array of objects
 * JSON tables can be expressed as an array of objects (see
 * ENCODE_AS_ARRAY_OF_OBJECTS).  Each object gives one row of data.
 * Property names for the first object give column names, and the same
 * properties must be used for all further row objects. All objects
 * must have these same properties, though they may be given in any
 * order.
 * <pre>
 *  [
 *      { "Column 1": 1, "Column 2": 2, "Column 3": 3 },
 *      { "Column 1": 4, "Column 2": 5, "Column 3": 6 }
 *  ]
 * </pre>
 *
 * ##### Parent object
 * JSON tables can be included in a parent object within a "table"
 * property (see ENCODE_AS_OBJECT):
 * <pre>
 *  {
 *      "table": [ ... ]
 *  }
 * </pre>
 *
 * ##### Table names
 * JSON tables within a parent object may have additional properties
 * that give the table's short name (name), long name (title), and
 * description.  The name, title, and description property values may
 * be a scalar string or an array with at least one scalar string value.
 * Non-string values are silently converted to strings.
 * <pre>
 *  {
 *      "name":  [ "tbl" ],
 *      "title": [ "Big table" ],
 *      "description": [ "A big table with lots of data" ],
 *      "table": [ ... ]
 *  }
 * </pre>
 *
 * ##### Table schema name
 * JSON tables can have a microformat schema name that refers to
 * a well-known schema by setting the "type" property of the parent
 * object.  The type property value may be an array or a scalar with a
 * single string value.
 * <pre>
 *  {
 *      "type": [ "json-array" ],
 *      "table": [ ... ]
 *  }
 * </pre>
 *
 * Several generic schema names refer to the above tables containing
 * column names.  These are functionally identical to having no schema name:
 *  @li "json-array"
 *  @li "json-table"
 *  @li "array"
 *  @li "table"
 *
 * Other well-known schema names may have more complex schemas associated
 * with them that define the number and names of columns, and column
 * data types. For instance, the "messages" type is used for a 2-column
 * table where each row has a text message and a time stamp.
 *
 * When a well-known schema name maps to a schema with defined columns,
 * the first row of the table is not used for column names.
 *
 * ##### Table schema
 * JSON tables can include an explicit schema to provide column short names,
 * long names, descriptions, and data types using the JSON Table Schema
 * microformat (see ENCODE_AS_OBJECT_WITH_SCHEMA).  When an explicit schema
 * is given, the first row of the table is not used for column names.
 * <pre>
 *  {
 *      "type": [ "json-array" ],
 *      "fields" : [
 *          {
 *              "name": "col1",
 *              "title": "Column 1",
 *              "type": "number",
 *              "format": "default",
 *              "description": "This is column 1"
 *          },
 *          {
 *              "name": "col2",
 *              "title": "Column 2",
 *              "type": "number",
 *              "format": "default",
 *              "description": "This is column 2"
 *          }
 *      ],
 *      "table": [ ... ]
 *  }
 * </pre>
 *
 *
 * #### Table decode limitations
 * The amount of table and column descriptive information available
 * in a JSON file depends upon which syntax form above is used. For
 * instance, in some forms, columns only have short names. In other
 * forms columns have short and long names, descriptions, and data
 * types. Similarly, in some forms tables have names and descriptions,
 * while in other forms they do not. In all of these cases, names
 * and descriptions default to empty strings and are only set if the
 * syntax parsed includes names and descriptions.
 *
 * When a schema is provided, columns may have specific data types.
 * But when column data types are not chosen, these data types
 * are automatically inferred by the Drupal\chart_suite\SDSC\StructuredData\Table class.
 * That class scans through each column and looks for a consistent
 * interpretation of the values as integers, floating-point numbers,
 * booleans, etc., then sets the data type accordingly.
 *
 *
 * #### Table encode limitations
 * The encoder can output tables in several JSON syntax forms. Some
 * of those forms include full information on the table's short and
 * long names and description, and on the short and long names,
 * description, and data types for all table columns.  But other
 * syntax forms omit most of this information and only output column
 * short names as the first row of the table.
 *
 * Column value data types are used to guide JSON encoding. Values
 * that are integers, floating-point numbers, booleans, or nulls are
 * output as single un-quoted tokens. All other value types are output
 * as single-quoted strings.
 *
 *
 * @see     Drupal\chart_suite\SDSC\StructuredData\Table   the StructuredData Table class
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    1/27/2016
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 *
 * @version 0.0.2  Revised to provide format attributes per RDA, and to
 * create tables using the updated Table API that uses an array of attributes.
 *
 * @version 0.0.3  Moved Table, Tree, and Graph handling into separate classes.
 */
final class JSONTableFormat
    extends AbstractFormat
{
//----------------------------------------------------------------------
// Constants
//----------------------------------------------------------------------
    /**
     * An encoding style that generates a JSON array of row arrays.
     * The first row array contains the column names.
     *
     * <pre>
     * [
     *   [ "Column1", "Column2", "Column3", ... ],
     *   [ value1, value2, value3, ... ],
     *   [ value1, value2, value3, ... ],
     *   ...
     * ]
     * </pre>
     */
    const ENCODE_AS_ARRAY_OF_ARRAYS = 1;

    /**
     * An encoding style that generates a JSON array of row objects.
     * Each object has the same properties based upon the column names.
     *
     * <pre>
     * [
     *   { "Column1": value1, "Column2": value2, "Column3": value3, ... },
     *   { "Column1": value1, "Column2": value2, "Column3": value3, ... },
     *   ...
     * ]
     * </pre>
     */
    const ENCODE_AS_ARRAY_OF_OBJECTS = 2;

    /**
     * An encoding style identical to ENCODE_AS_ARRAY_OF_OBJECTS, but
     * with a parent object that provides the table's name, description,
     * and schema type, if any. The table is contained within the
     * 'table' property of the object.
     *
     * <pre>
     * {
     *   "name": "table short name",
     *   "title": "table long name"
     *   "description": "table description",
     *   "type": "table source schema name",
     *   "table": [ ... ]
     * }
     * </pre>
     */
    const ENCODE_AS_OBJECT = 3;

    /**
     * An encoding style identical to ENCODE_AS_OBJECT, but with
     * a schema included that provides column names, descriptions,
     * and data types.
     *
     * This is the default encoding.
     *
     * <pre>
     * {
     *   "name": "table short name",
     *   "title": "table long name"
     *   "description": "table description",
     *   "type": "table source schema name",
     *   "fields": [
     *     {
     *       "name": "Column short name",
     *       "title": "Column long name",
     *       "description": "Column description",
     *       "type": "Column data type",
     *       "format": "Column data type format"
     *     },
     *     { ... }
     *   ]
     *   "table": [ ... ]
     * }
     * </pre>
     */
    const ENCODE_AS_OBJECT_WITH_SCHEMA = 4;



    /**
     * A list of well-known table schemas.
     */
    public static $WELL_KNOWN_TABLE_SCHEMAS = array(
        'messages' => array(
            array(
                'name'        => 'message',
                'title'       => 'Message',
                'type'        => 'string',
                'format'      => 'default',
                'description' => 'The ticker message'
            ),
            array(
                'name'        => 'time',
                'title'       => 'Time',
                'type'        => 'string',
                'format'      => 'default',
                'description' => 'The ticker timestamp'
            )
        )
    );


    /**
     * A hash table of accepted column data types.
     */
    private static $WELL_KNOWN_COLUMN_TYPES = array(
        'any' => 1,     'boolean' => 1, 'date' => 1,    'datetime' => 1,
        'integer' => 1, 'null' => 1,    'number' => 1,  'string' => 1,
        'time' => 1
    );



//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs and returns a new format object that may be used to
     * decode and encode tables in JSON (JavaScript Object Notation).
     */
    public function __construct( )
    {
        parent::__construct( );

        $this->attributes['syntax']         = 'JSON';
        $this->attributes['name']           = 'json-table';
        $this->attributes['longName']       = 'JavaScript Object Notation (JSON) Table';
        $this->attributes['MIMEType']       = 'application/json';
        $this->attributes['fileExtensions'] = array( 'json' );
        $this->attributes['description'] =
            'The JSON (JavaScript Object Notation) format encodes ' .
            'a variety of data, including tables. ' .
            'Tabular data may have an unlimited number of rows and ' .
            'columns with an optional schema. Each column may have ' .
            'a short name, long name, and description. All rows have ' .
            'a value for every column. Row values are typically integers ' .
            'or floating-point numbers, but they also may be strings and ' .
            'booleans.';
        $this->attributes['expectedUses'] = array(
            'Tabular data with named columns and rows of values'
        );
        $this->attributes['standards'] = array(
            array(
                'issuer' => 'RFC',
                'name' => 'IETF RFC 7159',
                'natureOfApplicability' => 'specifies',
                'details' => 'The JavaScript Object Notation (JSON) Data Interchange Format'
            ),
            array(
                'issuer' => 'ad hoc',
                'name' => 'JSON Table',
                'natureOfApplicability' => 'specifies',
                'details' => 'http://dataprotocols.org/json-table-schema/'
            )
        );

        // Unknown:
        //  identifier
        //  creationDate
        //  lastModificationDate
        //  provenance
    }
    // @}

    /**
     * @name Destructors
     */
    // @{
    /**
     * Destroys a previously-constructed format object.
     */
    public function __destruct( )
    {
        parent::__destruct( );
    }
    // @}





//----------------------------------------------------------------------
// Encode/decode attribute methods
//----------------------------------------------------------------------
    /**
     * @name Encode/decode attribute methods
     */
    // @{
    /**
     * @copydoc AbstractFormat::getComplexity
     */
    public function getComplexity( )
    {
        return 10;
    }

    /**
     * @copydoc AbstractFormat::canDecodeTables
     */
    public function canDecodeTables( )
    {
        return true;
    }

    /**
     * @copydoc AbstractFormat::canEncodeTables
     */
    public function canEncodeTables( )
    {
        return true;
    }
    // @}
    //----------------------------------------------------------------------
    // Encode/decode methods
    //----------------------------------------------------------------------
    /**
     * @name Encode/decode methods
     */
    // @{
    /**
     * @copydoc AbstractFormat::decode
     *
     * #### Decode limitations
     * The JSON format always returns an array containing a single
     * Drupal\chart_suite\SDSC\StructuredData\Table object.
     */
    public function decode( &$text )
    {
        // Parse JSON
        // -----------------------------------------------------
        //   Parse JSON text.
        if ( empty( $text ) )
            return array( );        // No table

        // Passing 'false' to json_decode( ) means that it should *not*
        // silently convert objects into arrays. We need to know whether
        // something in the text is an object or array because they have
        // different meanings and different parse paths below.
        $content = json_decode( $text, false );

        if ( $content == NULL )
        {
            // Failure to parse.
            $code = json_last_error( );
            switch ( $code )
            {
                case JSON_ERROR_STATE_MISMATCH:
                case JSON_ERROR_SYNTAX:
                    throw new SyntaxException(
                        'Malformed JSON. Problem with commas, brackets, or parenthesis?' );
                case JSON_ERROR_CTRL_CHAR:
                case JSON_ERROR_UTF8:
                    throw new SyntaxException(
                        'Malformed JSON. Control characters or bad UTF-8?' );

                // The maximum nesting depth is not defined by PHP and may
                // vary with changes in the implementation. This makes unit
                // testing for this case is not practical, so we ignore it.
                // @codeCoverageIgnoreStart
                case JSON_ERROR_DEPTH:
                    throw new SyntaxException(
                        'Malformed JSON. Nesting too deep.' );
                // @codeCoverageIgnoreEnd
            }

            // There is no content, and yet we don't know what the
            // error is, if any.
            throw new SyntaxException(
                'Malformed JSON.' );
        }
        // At this point we don't know what type of content we have.
        // We could have a table in any of several formats.


        // Determine content type
        // -----------------------------------------------------
        //   If the content is an array, we have a table.
        //
        //   If the content is an object, look for a few
        //   tell-tale properties to see what we have.
        if ( is_array( $content ) )
            return $this->_decodeTableArray( $content, NULL );

        if ( is_object( $content ) )
            return $this->_decodeTableObject( $content );

        // Otherwise we don't know what it is.
        throw new SyntaxException(
            'Unrecognized JSON content. Does not appear to be a table.' );
    }





    /**
     * @copydoc AbstractFormat::encode
     *
     * #### Encode limitations
     * The JSON format only supports encoding a single
     * Drupal\chart_suite\SDSC\StructuredData\Table in the format. An exception is thrown
     * if the $objects argument is not an array, is empty, contains
     * more than one object, or it is not a Table.
     */
    public function encode( &$objects, $options = 0 )
    {
        //
        // Validate arguments
        // -----------------------------------------------------
        if ( $objects == NULL )
            return NULL;            // No table to encode

        if ( !is_array( $objects ) )
            throw new \InvalidArgumentException(
                'JSON encode requires an array of objects.' );

        if ( count( $objects ) > 1 )
            throw new \InvalidArgumentException(
                'JSON encode only supports encoding a single object.' );
        $object = &$objects[0];

        //
        // Encode
        // -----------------------------------------------------
        // Reject anything that isn't a Table.
        if ( is_a( $object, 'Drupal\chart_suite\SDSC\StructuredData\Table', false ) )
            return $this->_encodeTable( $object, $options );
        else
            throw new \InvalidArgumentException(
                'JSON encode object must be a table.' );
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
     * Decodes a table from an array.
     *
     * This is the simplest form of JSON table. It has no table name,
     * schema name, or schema. The array has the table's rows. Each
     * row may be an array or an object. The first row sets the column
     * names.
     *
     * Array of arrays:
     * <pre>
     *  [
     *      [ "Column 1", "Column 2", "Column 3" ],
     *      [ 1, 2, 3 ],
     *      [ 4, 5, 6 ]
     *  ]
     * </pre>
     *
     * Array of objects:
     * <pre>
     *  [
     *      { "Column 1": 1, "Column 2": 2, "Column 3": 3 },
     *      { "Column 1": 4, "Column 2": 5, "Column 3": 6 }
     *  ]
     * </pre>
     *
     *
     * @param   array  $content  an array containing the freshly
     * parsed array of table data.
     *
     * @param   array  $columns  an array of column attributes, if any.
     *
     * @return  Table              the table parsed from the content,
     * including columns and rows.
     *
     * @throws  InvalidContentException  if the table is malformed.
     */
    private function _decodeTableArray( &$content, $columns = NULL )
    {
        if ( count( $content ) == 0 )
            return array( );            // No table

        if ( $columns == NULL )
            $columns = array( );

        $rows = NULL;


        // Get rows and columns
        // -----------------------------------------------------
        //   Rows may be arrays or objects. Handle them a bit
        //   differently.
        if ( is_array( $content[0] ) )
        {
            // Each row is an array.
            //
            // If the 1st row array has no columns, we have a table
            // with no columns. Since a table cannot have rows without
            // columns, giving a row without columns is an error.
            //
            // Error example:
            //  [
            //      [ ]
            //  ]
            if ( count( $content[0] ) == 0 )
                throw new InvalidContentException(
                    'JSON table first row should have column names, but is empty.' );

            // We have a 1st row with columns. Use it to get
            // column names, if needed.  Each column name must be a scalar,
            // which we convert to a string, if needed.
            //
            // Good example:
            //  [
            //      [ "Column 1", "Column 2", "Column 3" ]
            //  ]
            if ( count( $columns ) == 0 )
            {
                $header = array_shift( $content );
                foreach ( $header as &$name )
                {
                    if ( !is_scalar( $name ) )
                        throw new InvalidContentException(
                            'JSON table first row column names must be scalars (usually strings).' );
                    if ( $name === '' )
                        throw new InvalidContentException(
                            'JSON table column name must not be empty.' );
                    $columns[] = array( 'name' => (string)$name );
                }
            }

            // There may be further rows.  All of them must be
            // arrays too.  All values in each row must be scalars.
            // Check to be sure.
            //
            // Error example:
            //  [
            //      [ "Column 1", "Column 2" ],
            //      { "this": "that", "thing": 42 }
            //  ]
            //
            // Good example:
            //  [
            //      [ "Column 1", "Column 2" ],
            //      [ 123, 456 ],
            //      [ 789, 012 ]
            //  ]
            $rows = &$content;
            foreach ( $rows as &$row )
            {
                if ( !is_array( $row ) )
                    throw new InvalidContentException(
                        'JSON table rows should be all arrays or all objects.' );
                foreach ( $row as &$value )
                {
                    if ( !is_scalar( $value ) && $value != NULL )
                        throw new InvalidContentException(
                            'JSON table row values must be scalars.' );
                }
            }
        }
        else if ( is_object( $content[0] ) )
        {
            // Each row is an object.
            //
            // If the 1st row object has no properties, we have
            // a table with no columns. Since a table cannot have
            // rows without columns, giving a row without columns
            // is an error.
            //
            // Error example:
            //  [
            //      { }
            //  ]
            //
            // If we have a 1st row with columns, use it to get
            // column names, if needed.  Each column name must be a scalar,
            // which we convert to a string, if needed.
            //
            // Good example:
            //  [
            //      { "Column 1": 123, "Column 2": 456, "Column 3": 789 }
            //  ]
            if ( count( $columns ) == 0 )
            {
                $properties = get_object_vars( $content[0] );
                if ( count( $properties ) == 0 )
                    throw new InvalidContentException(
                        'JSON table first row should have column names, but is empty.' );
                foreach ( $properties as $name => &$value )
                {
                    if ( $name == '_empty_' )
                        throw new InvalidContentException(
                            'JSON table column name must not be empty.' );
                    $columns[] = array( 'name' => (string)$name );
                }
            }

            // There may be further rows.  All of them must be
            // objects too.  All values in each row must be scalars.
            // Check to be sure.
            //
            // Error example:
            //  [
            //      { "Column 1": 123, "Column 2": 456, "Column 3": 789 },
            //      [ 1, 2, 3 ]
            //  ]
            //
            // Good example:
            //  [
            //      { "Column 1": 123, "Column 2": 456, "Column 3": 789 },
            //      { "Column 1": 321, "Column 2": 654, "Column 3": 987 }
            //  ]
            //
            // Convert all objects into arrays of values for further
            // processing.
            $rows = array( );
            foreach ( $content as &$rowObject )
            {
                if ( !is_object( $rowObject ) )
                    throw new InvalidContentException(
                        'JSON table rows should be all arrays or all objects' );

                $row = array( );
                foreach ( $columns as &$column )
                {
                    $name = $column['name'];
                    if ( !property_exists( $rowObject, $name ) )
                        throw new InvalidContentException(
                            'JSON table row objects must all have the same properties as the 1st row.' );
                    $value = $rowObject->{$name};
                    if ( !is_scalar( $value ) && $value != NULL )
                        throw new InvalidContentException(
                            'JSON table row values must be scalars.' );
                    $row[] = $value;
                }
                $rows[] = $row;
            }
        }
        else
            throw new SyntaxException(
                'Unrecognized JSON content. Does not appear to be a table.' );

        // Make sure all of the rows are complete.
        $nColumns = count( $columns );
        foreach ( $rows as &$row )
        {
            if ( count( $row ) != $nColumns )
                throw new InvalidContentException(
                    'JSON table rows must all have one value per column.' );
        }


        // Build table
        // -----------------------------------------------------
        //  No table name or description.
        //  JSON as syntax. No schema.
        $attributes = array(
            // 'name' unknown
            // 'longName' unknown
            // 'description' unknown
            // 'sourceFileName' unknown
            'sourceMIMEType'   => $this->getMIMEType( ),
            'sourceSyntax'     => $this->getSyntax( ),
            'sourceSchemaName' => 'json-table'
        );
        $table = new Table( $attributes );

        foreach ( $columns as &$column )
            $table->appendColumn( $column );

        if ( count( $rows ) != 0 )
            $table->appendRows( $rows );

        return array( $table );
    }




    /**
     * Decodes a table from an object.
     *
     * The object has already been recognized as having a 'table'
     * property containing the table's rows. Additional properties
     * may be present for the table's name, etc.
     *
     * Minimal object:
     * <pre>
     *  {
     *      "table": [ ... ]
     *  }
     * </pre>
     *
     * Object with schema name:
     * <pre>
     *  {
     *      "type": [ "json-array" ],
     *      "table": [ ... ]
     *  }
     * </pre>
     *
     * Object with schema name and table attributes:
     * <pre>
     *  {
     *      "type":  [ "json-array" ],
     *      "name":  [ "tbl" ],
     *      "title": [ "Big table" ],
     *      "description": [ "A big table with lots of data" ],
     *      "table": [ ... ]
     *  }
     * </pre>
     *
     * Object with schema name, table attributes, and column attributes
     * (called 'fields' in the JSON Table Schema):
     * <pre>
     *  {
     *      "type":  [ "json-array" ],
     *      "name":  [ "tbl" ],
     *      "title": [ "Big table" ],
     *      "description": [ "A big table with lots of data" ],
     *      "fields" : [
     *          {
     *              "name": "col1",
     *              "title": "Column 1",
     *              "type": "number",
     *              "format": "default",
     *              "description": "This is column 1"
     *          },
     *          {
     *              "name": "col2",
     *              "title": "Column 2",
     *              "type": "number",
     *              "format": "default",
     *              "description": "This is column 2"
     *          }
     *      ],
     *      "table": [ ... ]
     *  }
     * </pre>
     *
     * @param  mixed  $content  an object containing the freshly
     * parsed properties and rows array of table data.
     *
     * @return  Table              the table parsed from the content,
     * including columns and rows.
     *
     * @throws  SyntaxException  if the content does not appear to be a table.
     *
     * @throws  InvalidContentException  if the table is malformed.
     */
    private function _decodeTableObject( &$content )
    {
        // Validate it is a table
        // -----------------------------------------------------
        // The 'table' property must exist and contain an array
        // of rows.
        //
        // Error example:
        //  {
        //      "table": 123
        //  }
        //
        // Good example:
        //  {
        //      "table": [
        //        [ 1, 2, 3 ]
        //      ]
        //  }
        if ( !property_exists( $content, 'table' ) )
            throw new SyntaxException(
                'Unrecognized JSON content. Does not appear to be a table.' );
        $rows = $content->table;
        if ( !is_array( $rows ) )
            throw new InvalidContentException(
                'JSON "table" property must be an array of rows.' );


        // Set default attributes.
        $attributes = array(
            // 'name' unknown
            // 'longName' unknown
            // 'description' unknown
            // 'sourceFileName' unknown
            // 'sourceMIMEType' unknown
            'sourceMIMEType'   => $this->getMIMEType( ),
            'sourceSyntax'     => $this->getSyntax( ),
            'sourceSchemaName' => 'json-table'
        );


        // Get table information
        // -----------------------------------------------------
        // Look for descriptive properties. These are all
        // optional.  All of them should be scalars or arrays.
        // If they are arrays, use the 1st value.
        //
        // Good example:
        //  {
        //      "type":  [ "json-array" ],
        //      "name":  [ "tbl" ],
        //      "title": [ "Big table" ],
        //      "description": [ "A big table with lots of data" ],
        //      "table": [ ... ]
        //  }
        $properties = get_object_vars( $content );
        if ( property_exists( $content, 'name' ) )
        {
            if ( is_scalar( $content->name ) )
                $name = (string)$content->name;
            else if ( is_array( $content->name ) &&
                count( $content->name ) > 0 )
                $name = (string)$content->name[0];
            else
                throw new InvalidContentException(
                    'JSON table "name" property must be a scalar or array (usually a string).' );
            $attributes['name'] = $name;
            unset( $properties['name'] );
        }

        if ( property_exists( $content, 'title' ) )
        {
            if ( is_scalar( $content->title ) )
                $title = (string)$content->title;
            else if ( is_array( $content->title ) &&
                count( $content->title ) > 0 )
                $title = (string)$content->title[0];
            else
                throw new InvalidContentException(
                    'JSON table "title" property must be a scalar or array (usually a string).' );
            $attributes['longName'] = $title;
            unset( $properties['longName'] );
        }

        if ( property_exists( $content, 'description' ) )
        {
            if ( is_scalar( $content->description ) )
                $description = (string)$content->description;
            else if ( is_array( $content->description ) &&
                count( $content->description ) > 0 )
                $description = (string)$content->description[0];
            else
                throw new InvalidContentException(
                    'JSON table "description" property must be a scalar or array (usually a string).' );
            $attributes['description'] = $description;
            unset( $properties['description'] );
        }


        // Add any further properties as-is to the table's attributes,
        // but skip the "fields" and "type" properties addressed below.
        unset( $properties['type'] );
        unset( $properties['fields'] );
        $attributes = array_merge( $attributes, $properties );


        // Get schema information
        // -----------------------------------------------------
        // Look for schema properties. These are all optional.
        //
        $schemaName = NULL;
        $schema     = NULL;

        // Get column info, if any.
        if ( property_exists( $content, 'fields' ) )
        {
            if ( !is_array( $content->fields ) )
                throw new InvalidContentException(
                    'JSON table schema "fields" must be an array.' );
            $schema = $content->fields;
        }

        // Get schema name (type), if any.  If we have the name,
        // but not the column info, then see if the name is well-known
        // and we already have the column info.
        if ( property_exists( $content, 'type' ) )
        {
            // The schema name must be a scalar string, or an array
            // with at least one scalar string.
            //
            // Good example:
            //  {
            //      "type":  [ "json-array" ],
            //      ...
            //  }
            if ( is_scalar( $content->type ) )
                $schemaName = (string)$content->type;
            else if ( is_array( $content->type ) &&
                count( $content->type ) > 0 )
                $schemaName = (string)$content->type[0];
            else
                throw new InvalidContentException(
                    'JSON table schema "type" must be a scalar or array (usually a string).' );


            // If we don't have the column info given explicitly,
            // then see if this is a well-known schema name for
            // which we already have the column info.
            //
            // Ignore generic schema names since they don't tell us
            // anything about what the columns should be.
            if ( !isset( $schema ) &&
                $schemaName != 'array' &&
                $schemaName != 'table' &&
                $schemaName != 'json-array' &&
                $schemaName != 'json-table' )
            {
                // Check the well-known schema table.
                //
                // Good example:
                //  {
                //      "type":  [ "messages" ],
                //      ...
                //  }
                if ( isset( self::$WELL_KNOWN_TABLE_SCHEMAS[$schemaName] ) )
                    $schema = self::$WELL_KNOWN_TABLE_SCHEMAS[$schemaName];
                else
                    throw new InvalidContentException(
                        'JSON table must define "fields" with column attributes, or have a well-known schema type.' );
                $attributes['sourceSchemaName'] = $schemaName;
            }
        }


        // Parse columns schema
        // -----------------------------------------------------
        $columns = array( );
        if ( isset( $schema ) )
        {
            // Use the schema for the column names.
            $columns = $this->_decodeTableSchema( $schema );
            if ( count( $columns ) == 0 )
                throw new InvalidContentException(
                    'JSON table schema should define columns, but is empty.' );
        }


        // Get rows and columns
        // -----------------------------------------------------
        //   The table part of the object is an array of rows.
        //   Parse it into a table, passing in the column info
        //   we've already gathered, if any.
        //
        //   This may throw an exception.
        //
        //   On success, we get an array. If the array is empty,
        //   the rows part of the table was empty. Create an
        //   empty table.
        //
        //   If the array is not empty, the rows part of the
        //   table was parsed into a Table object with all the
        //   rows and columns set. Update it with table attributes.
        $results = $this->_decodeTableArray( $rows, $columns );
        if ( count( $results ) == 0 )
        {
            // Rows were empty.  Create an empty table with the
            // given attributes and columns, but now rows.
            $table = new Table( $attributes );
            foreach ( $columns as &$c )
                $table->appendColumn( $c, 0 );
        }
        else
        {
            $table = $results[0];
            $table->setAttributes( $attributes );
        }
        return array( $table );
    }


    /**
     * Decodes a table schema and returns the array of column attributes.
     *
     * <pre>
     *      [
     *          {
     *              "name": "col1",
     *              "title": "Column 1",
     *              "type": "number",
     *              "format": "default",
     *              "description": "This is column 1"
     *          },
     *          {
     *              "name": "col2",
     *              "title": "Column 2",
     *              "type": "number",
     *              "format": "default",
     *              "description": "This is column 2"
     *          }
     *      ]
     * </pre>
     *
     * @param  array  $schema  an array of schema objects describing
     * columns in a table.
     *
     * @return array  an array of parsed column information for the
     * table.
     *
     * @throws  InvalidContentException  if the schema entries are not
     * arrays or objects.
     *
     * @throws  InvalidContentException  if the name, title, description
     * or type are not scalars.
     *
     * @throws  InvalidContentException  if the name is empty or missing.
     *
     * @throws  InvalidContentException  if the data type is not recognized.
     */
    private function _decodeTableSchema( $schema )
    {
        $columns = array( );
        $columnIndex = 0;

        // The schema is an array of objects, with one per column.
        foreach ( $schema as &$schemaColumn )
        {
            // Normally, each entry is an object. We'll accept an
            // associative array too.  Convert to an associative
            // array.
            $columnAttributes = array( );
            $columnArray      = NULL;

            if ( is_object( $schemaColumn ) )
                $columnArray = (array)$schemaColumn;
            else if ( is_array( $schemaColumn ) )
                $columnArray = $schemaColumn;
            else
                throw new InvalidContentException(
                    'JSON table schema "fields" items must be objects or arrays.' );


            // Get well-known column attributes:
            //  - name
            //  - title
            //  - description
            //  - type
            // Each of these must be either a scalar or an array with
            // at least one entry. The first entry in the array is used.
            // Values are cast to strings.
            //
            // Names cannot be empty strings, but the others can be.
            //
            // If the column name is not given, the column's numeric
            // index is used.
            if ( !isset( $columnArray['name'] ) )
                $columnAttributes['name'] = (string)$columnIndex;
            else
            {
                // Good example:
                //   "name": "Column 1"
                // or
                //   "name": [ "Column 1" ]
                //
                // Error example:
                //   "name": { "this": "that" }
                // or
                //   "name": ""
                if ( is_scalar( $columnArray['name'] ) )
                    $columnAttributes['name'] = (string)$columnArray['name'];
                else if ( is_array( $columnArray['name'] ) &&
                    count( $columnArray['name'] ) > 0 )
                    $columnAttributes['name'] = (string)$columnArray['name'][0];
                else
                    throw new InvalidContentException(
                        'JSON table schema column names must be scalars (usually strings).' );

                if ( $columnAttributes['name'] === '' )
                    throw new InvalidContentException(
                        'JSON table schema column names must not be empty.' );
                unset( $columnArray['name'] );
            }

            if ( isset( $columnArray['title'] ) )
            {
                // Good example:
                //   "title": "Column 1"
                // or
                //   "title": [ "Column 1" ]
                // or
                //   "title": ""
                //
                // Error example:
                //   "title": { "this": "that" }
                if ( is_scalar( $columnArray['title'] ) )
                    $columnAttributes['longName'] = (string)$columnArray['title'];
                else if ( is_array( $columnArray['title'] ) &&
                    count( $columnArray['title'] ) > 0 )
                    $columnAttributes['longName'] = (string)$columnArray['title'][0];
                else
                    throw new InvalidContentException(
                        'JSON table schema column titles must be scalars (usually strings).' );
                unset( $columnArray['title'] );
            }

            if ( isset( $columnArray['description'] ) )
            {
                // Good example:
                //   "description": "Column 1"
                // or
                //   "description": [ "Column 1" ]
                // or
                //   "description": ""
                //
                // Error example:
                //   "description": { "this": "that" }
                if ( is_scalar( $columnArray['description'] ) )
                    $columnAttributes['description'] = (string)$columnArray['description'];
                else if ( is_array( $columnArray['description'] ) &&
                    count( $columnArray['description'] ) > 0 )
                    $columnAttributes['description'] = (string)$columnArray['description'][0];
                else
                    throw new InvalidContentException(
                        'JSON table schema column descriptions must be scalars (usually strings).' );

                unset( $columnArray['description'] );
            }

            if ( isset( $columnArray['type'] ) )
            {
                // Good example:
                //   "type": "integer"
                // or
                //   "type": [ "integer" ]
                // or
                //   "type": ""
                //
                // Error example:
                //   "type": { "this": "that" }
                if ( is_scalar( $columnArray['type'] ) )
                    $columnAttributes['type'] = (string)$columnArray['type'];
                else if ( is_array( $columnArray['type'] ) &&
                    count( $columnArray['type'] ) > 0 )
                    $columnAttributes['type'] = (string)$columnArray['type'][0];
                else
                    throw new InvalidContentException(
                        'JSON table schema column types must be scalars (usually strings).' );

                // Verify the type is known.
                if ( !isset( self::$WELL_KNOWN_COLUMN_TYPES[$columnAttributes['type']] ) )
                    throw new InvalidContentException(
                        'JSON table schema type not recognized.' );

                unset( $columnArray['type'] );
            }
            $columns[] = $columnAttributes;
            ++$columnIndex;
        }
        return $columns;
    }





    /**
     * Encodes a table as JSON text, controlled by the given
     * options.
     *
     * @param  mixed   $table  a table object to be encoded.
     *
     * @param integer  $options  encoding options to control how
     * JSON text is generated.
     *
     * @return  string        the JSON text that encodes the table,
     * or a NULL if there was no table.
     */
    private function _encodeTable( &$table, $options )
    {
        if ( $table->getNumberOfColumns( ) <= 0 )
            return NULL;            // No data to encode


        // Encode header and rows
        // -----------------------------------------------------
        //   Encode the table either as an array, or as an object
        //   that includes the array as a property value.  Add
        //   the schema if needed.
        if ( $options == self::ENCODE_AS_ARRAY_OF_ARRAYS )
        {
            $text  = "[\n";
            $text .= $this->_encodeTableColumnsAsArray( '  ', $table );
            $text .= $this->_encodeTableRowsAsArrays( '  ', $table );
            $text .= "]\n";
            return $text;
        }
        if ( $options == self::ENCODE_AS_ARRAY_OF_OBJECTS )
        {
            $tmp = $this->_encodeTableRowsAsObjects( '  ', $table );
            if ( $tmp === '' )
                return '';
            return "[\n$tmp ]\n";
        }
        if ( $options == self::ENCODE_AS_OBJECT )
        {
            $text  = "{\n";
            $text .= $this->_encodeTableObjectHeader( '  ', $table );
            $text .= "  \"table\": [\n";
            $text .= $this->_encodeTableRowsAsObjects( '    ', $table );
            $text .= "  ]\n";
            $text .= "}\n";
            return $text;
        }

        // Otherwise ENCODE_AS_OBJECT_WITH_SCHEMA (default)
        $text  = "{\n";
        $text .= $this->_encodeTableObjectHeader( '  ', $table );
        $text .= "  \"fields\": [\n";
        $text .= $this->_encodeTableColumnsAsObjects( '    ', $table );
        $text .= "  ],\n";
        $text .= "  \"table\": [\n";
        $text .= $this->_encodeTableRowsAsObjects( '    ', $table );
        $text .= "  ]\n";
        $text .= "}\n";
        return $text;
    }





    /**
     * Encodes a table's information as name:value pairs
     * for a header object.
     *
     * The returned text has the form:
     * <pre>
     *      "name": "shortName",
     *      "title": "longName",
     *      "description": "description",
     *      "type": "schema name",
     * </pre>
     *
     * @param  string  $indent  a string containing the indentation
     * string (presumably just spaces) the prefix every row of
     * encoded text generated by this method.
     *
     * @param  Table  $table  the table who's attributes are being
     * encoded into the returned JSON text.
     *
     * @return  string  the JSON text that encodes the table's
     * attributes.
     */
    private function _encodeTableObjectHeader( $indent, &$table )
    {
        // Get attributes. Any of these may be NULL.
        $attributes = $table->getAttributes( );

        $name = '';
        if ( isset( $attributes['name'] ) )
            $name = $attributes['name'];

        $title = '';
        if ( isset( $attributes['longName'] ) )
            $title = $attributes['longName'];

        $description = '';
        if ( isset( $attributes['description'] ) )
            $description = $attributes['description'];

        $type = '';
        if ( isset( $attributes['sourceSchemaName'] ) )
            $type = $attributes['sourceSchemaName'];


        $text = '';
        if ( $name !== '' )
            $text .= "$indent\"name\": \"$name\",\n";

        if ( $title !== '' )
            $text .= "$indent\"title\": \"$title\",\n";

        if ( $description !== '' )
            $text .= "$indent\"description\": \"$description\",\n";

        if ( $type !== '' )
            $text .= "$indent\"type\": \"$type\",\n";

        return $text;
    }





    /**
     * Encodes a table's column information as a table
     * schema array of field objects.
     *
     * The returned text has the form:
     * <pre>
     *    {
     *      "name": [ "shortName" ],
     *      "title": [ "longName" ],
     *      "description": [ "description" ],
     *      "type": [ "dataType" ],
     *      "format": [ "default" ]
     *    },
     *    {
     *      ...etc...
     *    }
     * </pre>
     *
     * @param  string  $indent  a string containing the indentation
     * string (presumably just spaces) the prefix every row of
     * encoded text generated by this method.
     *
     * @param  Table  $table  the table who's columns are being
     * encoded into the returned JSON text.
     *
     * @return  string  the JSON text that encodes the table's
     * column attributes.
     */
    private function _encodeTableColumnsAsObjects( $indent, &$table )
    {
        $nColumns = $table->getNumberOfColumns( );
        $text = '';

        for ( $i = 0; $i < $nColumns; ++$i )
        {
            // Get column information. There must be a short name,
            // but the long name and description may be empty.
            $attributes = $table->getColumnAttributes( $i );

            $name = '';
            if ( isset( $attributes['name'] ) )
                $name = $attributes['name'];

            $title = '';
            if ( isset( $attributes['longName'] ) )
                $title = $attributes['longName'];

            $description = '';
            if ( isset( $attributes['description'] ) )
                $description = $attributes['description'];

            $type = '';
            if ( isset( $attributes['type'] ) )
                $type = $attributes['type'];

            $format = 'default';

            // Add the column object.
            $text .= "$indent{\n";

            $text .= "$indent  \"name\": [ \"$name\" ],\n";
            if ( $title !== '' )
                $text .= "$indent  \"title\": [ \"$title\" ],\n";

            if ( $description !== '' )
                $text .= "$indent  \"description\": [ \"$description\" ],\n";

            if ( $type !== '' )
                $text .= "$indent  \"type\": [ \"$type\" ],\n";

            $text .= "$indent  \"format\": [ \"$format\" ]\n";

            if ( $i != ($nColumns-1) )
                $text .= "$indent},\n";
            else
                $text .= "$indent}\n";
        }
        return $text;
    }





    /**
     * Encodes a table's column names as an array of values.
     *
     * The returned text has the form:
     * <pre>
     *  [ "Name 1", "Name 2", "Name 3", ... ],
     * </pre>
     *
     * @param  string  $indent  a string containing the indentation
     * string (presumably just spaces) the prefix every row of
     * encoded text generated by this method.
     *
     * @param  Table  $table  the table who's columns are being
     * encoded into the returned JSON text.
     *
     * @return  string  the JSON text that encodes the table's
     * column attributes.
     */
    private function _encodeTableColumnsAsArray( $indent, &$table )
    {
        $nRows    = $table->getNumberOfRows( );
        $nColumns = $table->getNumberOfColumns( );

        // Create a single line of text with the column names
        // quoted, comma separated, and surrounded by square
        // brackets.
        $text = $indent . '[ ';
        for ( $i = 0; $i < $nColumns; $i++ )
        {
            if ( $i != 0 )
                $text .= ', ';

            // Get the column name.
            $name = $table->getColumnName( $i );

            // Add it as a quoted string.
            $text .= '"' . $name . '"';
        }

        if ( $nRows > 0 )
            $text .= " ],\n";
        else
            $text .= " ]\n";
        return $text;
    }





    /**
     * Encodes the given table's rows, with each row encoded as an
     * array of comma-separated values.
     *
     * The returned text has the form:
     * <pre>
     *  [ 1, 2, 3 ],
     *  [ 4, 5, 6 ],
     *  ...
     * </pre>
     *
     * @param  string  $indent  a string containing the indentation
     * string (presumably just spaces) the prefix every row of
     * encoded text generated by this method.
     *
     * @param  Table  $table  the table who's rows are being
     * encoded into the returned JSON text.
     *
     * @return  string  the JSON text that encodes the table's rows.
     */
    private function _encodeTableRowsAsArrays( $indent, &$table )
    {
        $nRows    = $table->getNumberOfRows( );
        if ( $nRows == 0 )
            return '';
        $nColumns = $table->getNumberOfColumns( );
        $text = '';

        for ( $row = 0; $row < $nRows; $row++ )
        {
            // Put each table row on one line, surrounded
            // by square brackets, and separated by commas.
            // Double-quote anything that isn't a simple
            // scalar.
            $text .= $indent . '[ ';
            for ( $i = 0; $i < $nColumns; $i++ )
            {
                if ( $i != 0 )
                    $text .= ', ';

                // Get the value.
                $v = $table->getValue( $row, $i );

                // Add it as a simple scalar or a
                // quoted string.
                if ( is_int( $v ) ||
                    is_float( $v ) )
                    $text .= $v;
                else if ( is_bool( $v ) )
                {
                    if ( $v === true )
                        $text .= 'true';
                    else
                        $text .= 'false';
                }
                else if ( is_null( $v ) )
                    $text .= 'null';
                else
                    $text .= '"' . $v . '"';
            }

            if ( $row != $nRows-1 )
                $text .= " ],\n";
            else
                $text .= " ]\n";
        }
        return $text;
    }





    /**
     * Encodes the given table's rows, with each row encoded as an
     * object with name:value pairs using the column names.
     *
     * The returned text has the form:
     * <pre>
     *  { "Name 1": 1, "Name 2": 2, "Name 3": 3, ... },
     *  { "Name 1": 4, "Name 2": 5, "Name 3": 6, ... }
     * </pre>
     *
     * @param  string  $indent  a string containing the indentation
     * string (presumably just spaces) the prefix every row of
     * encoded text generated by this method.
     *
     * @param  Table  $table  the table who's rows are being
     * encoded into the returned JSON text.
     *
     * @return  string  the JSON text that encodes the table's rows.
     */
    private function _encodeTableRowsAsObjects( $indent, &$table )
    {
        $nRows = $table->getNumberOfRows( );
        if ( $nRows == 0 )
            return '';
        $nColumns = $table->getNumberOfColumns( );
        $text = '';

        for ( $row = 0; $row < $nRows; $row++ )
        {
            // Put each table row on one line, surrounded
            // by curly braces, and separated by commas.
            // Use name:value pairs for each column.
            // Double-quote anything that isn't a simple
            // scalar.
            $text .= "$indent{ ";
            for ( $i = 0; $i < $nColumns; $i++ )
            {
                if ( $i != 0 )
                    $text .= ', ';

                // Get the value and column name.
                $v = $table->getValue( $row, $i );
                $name = $table->getColumnName( $i );

                // Add it as a simple scalar or a
                // quoted string.
                $text .= '"' . $name . '": ';
                if ( is_int( $v ) ||
                    is_float( $v ) )
                    $text .= $v;
                else if ( is_bool( $v ) )
                {
                    if ( $v === true )
                        $text .= 'true';
                    else
                        $text .= 'false';
                }
                else if ( is_null( $v ) )
                    $text .= 'null';
                else
                    $text .= '"' . $v . '"';
            }

            if ( $row != $nRows-1 )
                $text .= " },\n";
            else
                $text .= " }\n";
        }
        return $text;
    }
    // @}
}

<?php
/**
 * @file
 * Defines \Drupal\chart_suite\SDSC\StructuredData\Format\TSVTableFormat to parse and
 * serialize data in the Tab-Separated Value (TSV) text format.
 */
namespace Drupal\chart_suite\SDSC\StructuredData\Format;

use Drupal\chart_suite\SDSC\StructuredData\Table;

/**
 * @class TSVTableFormat
 * TSVTableFormat provides decode and encode functions that map between
 * Tab-Separated Values (TSV) text and a Table.
 *
 *
 * #### Table Syntax
 * TSV is a de facto standard and only loosely documented.
 *
 * A TSV file contains a header and zero or more records.  The header and
 * records are each terminated by CRLF (carriage-return linefeed).
 *
 * The header and each record is a list of fields, separated by tabs. The
 * fields in a header are presumed to be column names.
 *
 * The TSV format is a de facto standard but is not documented by any
 * format specification.  A TSV file contains a single table made up
 * of a list of records written as lines in a TSV text file. Records are
 * separated by CRLF (carriage-return and linefeed) pairs, in that order.
 * A common and supported variant is to use a linfeed alone as a record
 * delimiter (typical on Linux and OS X).
 *
 * Values in each record are separated by TABs. Values may be numbers,
 * strings, and arbitrary multi-word tokens. Values are not surrounded
 * by quotes, or any other delimiters.
 *
 * The first record in a TSV file provides the names for table columns.
 * All further records provide table data. Every record must have the
 * same number of values.
 *
 *
 * #### Table decode limitations
 * TSV does not provide descriptive information beyond table column
 * names. The returned table uses these TSV names as column short names,
 * but leaves column long names, descriptions, and data types empty.
 * The returned table's own short name, long name, and description are
 * also left empty.
 *
 * Since the TSV syntax does not provide data types for column values,
 * these data types are automatically inferred by the
 * Drupal\chart_suite\SDSC\StructuredData\Table class. That class scans through each column and
 * looks for a consistent interpretation of the values as integers,
 * floating-point numbers, booleans, etc., then sets the data type
 * accordingly.
 *
 *
 * #### Table encode limitations
 * Since TSV does not support descriptive information for the table,
 * the table's short name, long name, and description are not included
 * in the encoded text.
 *
 * Since TSV only supports a single name for each column, the table's
 * column short names are output to the encoded text, but the column
 * long names, descriptions, and data types are not included.
 *
 *
 * @see     Drupal\chart_suite\SDSC\StructuredData\Table   the StructuredData Table class
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    9/24/2018
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 *
 * @version 0.0.2  Revised to provide format attributes per RDA, and to
 * create tables using the updated Table API that uses an array of attributes.
 *
 * @version 0.0.3. Revised to insure that CR-LF, LF-CR, CR alone, and LF alone
 * as line endings/delimiters are all accepted.
 */
final class TSVTableFormat
    extends AbstractFormat
{
//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs and returns a new format object that may be used to
     * decode and encode tables in TSV.
     */
    public function __construct( )
    {
        parent::__construct( );

        $this->attributes['syntax']         = 'TSV';
        $this->attributes['name']           = 'TSV';
        $this->attributes['longName']       = 'Tab-Separated Values (TSV)';
        $this->attributes['fileExtensions'] = array( 'tsv', 'txt' );
        $this->attributes['MIMEType']       = 'text/tab-separated-values';
        $this->attributes['description'] =
            'The TSV (Tab-Separated Values) format encodes tabular data ' .
            'with an unlimited number of rows and columns. Each column has ' .
            'a short name. All rows have a value for every column. Row ' .
            'values are typically integers or floating-point numbers, but ' .
            'they also may be strings and booleans.';
        $this->attributes['expectedUses'] = array(
            'Tabular data with named columns and rows of values' );

        // Unknown:
        //  identifier
        //  creationDate
        //  lastModificationDate
        //  provenance
        //  standards (none)
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
// Encode/decode attributes methods
//----------------------------------------------------------------------
    /**
     * @name Encode/decode attributes methods
     */
    // @{
    /**
     * @copydoc AbstractFormat::getComplexity
     */
    public function getComplexity( )
    {
        return 0;
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
     * The TSV format always returns an array containing a single
     * Drupal\chart_suite\SDSC\StructuredData\Table object.
     */
    public function decode( &$text )
    {
        if ( empty( $text ) )
            return array( );        // No table

        //
        // Preprocess
        // -----------------------------------------------------
        //   Change all CR-LF, LF-CR, CR alone, or LF alone into LF.
        //
        //   Remove the last LF, if any, so that exploding on LF
        //   doesn't leave us an extra empty line at the end.
        $newtext = preg_replace( '/\r\n?/', "\n", $text );
        $newtext = preg_replace( '/\n\r?/', "\n", $newtext );
        $newtext = preg_replace( '/\n$/', '', $newtext );


        //
        // Explode
        // -----------------------------------------------------
        //   Explode the string into lines on LF. We've already
        //   insured that LF doesn't exist in any quoted text.
        $lines = explode( "\n", $newtext );
        unset( $newtext );


        //
        // Parse
        // -----------------------------------------------------
        //   Explode each line on a tab.
        $rows = array_map(
            function( $line )
            {
                return explode( "\t", $line );
            }, $lines );
        unset( $lines );

        // If there are no rows, the file was empty and there is
        // no table to return.
        //
        // This 'if' checks will stay in the code, but there appears
        // to be no way to trigger it. An empty string '' is caught
        // earlier. A white-space string '   ' is really one row of
        // text in one column.  So, there is no obvious way to
        // hit this condition, but let's be paranoid.
        // @codeCoverageIgnoreStart
        if ( count( $rows ) == 0 )
            return array( );
        // @codeCoverageIgnoreEnd

        // The first row should be the column names. We have no way
        // of knowing if it is or not, so we just have to hope.
        $header   = array_shift( $rows );
        $nColumns = count( $header );

        // An empty file parsed as TSV produces a single column,
        // no rows, and a column with an empty name. Catch this
        // and return a NULL.
        if ( count( $rows ) == 0 && $nColumns == 1 && empty( $header[0] ) )
            return array( );

        // Every row must have the same number of values, and that
        // number must match the header.
        foreach ( $rows as &$row )
        {
            if ( count( $row ) != $nColumns )
                throw new SyntaxException(
                    'TSV table rows must all have the same number of values as the first row.' );
        }


        //
        // Build the table
        // -----------------------------------------------------
        $attributes = array(
            // 'name' unknown
            // 'longName' unknown
            // 'description' unknown
            // 'sourceFileName' unknown
            'sourceMIMEType'   => $this->getMIMEType( ),
            'sourceSyntax'     => $this->getSyntax( ),
            'sourceSchemaName' => $this->getName( )
        );
        $table = new Table( $attributes );


        //
        // Add columns
        // -----------------------------------------------------
        //   Header provides column names.
        //   No column descriptions or data types.
        foreach ( $header as &$field )
            $table->appendColumn( array( 'name' => $field ) );


        // Convert values rows
        // -----------------------------------------------------
        //   So far, every value in every row is a string. But
        //   we'd like to change to the "best" data type for
        //   the value. If it is an integer, make it an integer.
        //   If it is a float, make it a double. If it is a
        //   boolean, make it a boolean. Only fall back to string
        //   types if nothing better will do.
        foreach ( $rows as &$row )
        {
            foreach ( $row as $key => &$value )
            {
                // Ignore any value except a string. But really,
                // they should all be strings so we're just being
                // paranoid.
                // @codeCoverageIgnoreStart
                if ( !is_string( $value ) )
                    continue;
                // @codeCoverageIgnoreEnd

                $lower = strtolower( $value );
                if ( is_numeric( $value ) )
                {
                    // Convert to float or int.
                    $fValue = floatval( $value );
                    $iValue = intval( $value );

                    // If int and float same, then must be an int
                    if ( $fValue == $iValue )
                        $row[$key] = $iValue;
                    else
                        $row[$key] = $fValue;
                }
                else if ( $lower === 'true' )
                    $row[$key] = true;
                else if ( $lower === 'false' )
                    $row[$key] = false;

                // Otherwise leave it as-is.
            }
        }


        // Add rows
        // -----------------------------------------------------
        //   Parsed content provides rows.
        if ( count( $rows ) != 0 )
            $table->appendRows( $rows );
        return array( $table );
    }




    /**
     * @copydoc AbstractFormat::encode
     *
     * #### Encode limitations
     * The TSV format only supports encoding a single
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
                'TSV encode requires an array of objects.' );

        if ( count( $objects ) > 1 )
            throw new \InvalidArgumentException(
                'TSV encode only supports encoding a single object.' );

        $table = &$objects[0];
        if ( !is_a( $table, 'Drupal\chart_suite\SDSC\StructuredData\Table', false ) )
            throw new \InvalidArgumentException(
                'TSV encode object must be a table.' );

        $nColumns = $table->getNumberOfColumns( );
        if ( $nColumns <= 0 )
            return NULL;            // No data to encode
        $nRows = $table->getNumberOfRows( );
        $text  = '';


        //
        // Encode header
        // -----------------------------------------------------
        //   Generate a single row with comma-separated column
        //   names.
        for ( $column = 0; $column < $nColumns; $column++ )
        {
            if ( $column != 0 )
                $text .= "\t";

            $text .= $table->getColumnName( $column );
        }
        $text .= "\r\n";


        //
        // Encode rows
        // -----------------------------------------------------
        for ( $row = 0; $row < $nRows; $row++ )
        {
            $r = $table->getRowValues( $row );

            for ( $column = 0; $column < $nColumns; $column++ )
            {
                if ( $column != 0 )
                    $text .= "\t";
                $text .= $r[$column];
            }
            $text .= "\r\n";
        }

        return $text;
    }
    // @}
}
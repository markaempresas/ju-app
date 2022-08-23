<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\CSVTableFormat to parse and
 * serialize data in the Comma-Separated Value (CSV) text format.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;


use Drupal\chart_suite\SDSC\StructuredData\Table;


/**
 * @class CSVTableFormat
 * CSVTableFormat provides decode and encode functions that map
 * between Comma-Separated Values (CSV) text and a Table.
 *
 * CSV is a general-purpose text format used for the exchange of tabular
 * data, such as that used by spreadsheets (e.g. Microsoft Excel,
 * Apple Numbers) and some visualization applications. CSV files store
 * a single table with an arbitrary number of rows and columns. All
 * columns have a name. Row values may be of any data type, though they
 * are typically numeric.
 *
 *
 * #### Table syntax
 * The CSV format is documented by RFC 4180 from the IETF (Internet
 * Engineering Task Force). The RFC was never ratified as a standard and
 * it is not well-followed.
 *
 * A CSV file contains a single table made up of a list of records written
 * as lines in a text file. Each line is terminated by some mix of
 * carriage-return and line-feed:
 *
 * - RFC 4180 directs that each line end with CR-LF, in that order.
 *
 * - MS Excel 365, Apple Numbers, LibreOffice, and OpenOffice all end each
 *   line with LF.
 *
 * - MS Excel 2011 on the Mac, and MS Excel 365 saving into the "CSV for Mac"
 *   format, end all lines except the last one with a CR. The last line is
 *   ended with an LF.
 *
 * Excel saving data for the Mac is the outlier here. It is still saving
 * Mac files per the old Classic MacOS conventions that ended with the last
 * release of Classic MacOS in 2001. Modern macOS is based on BSD UNIX and
 * follows the UNIX convention of ending lines with LF.
 *
 * Values in each record are separated by commas. Numeric and other
 * single-word values may be given directly, while multi-word values are
 * enclosed in double quotes. Quoted values may include carriage returns
 * and linefeeds, though this is rare. Quoted values may include a double
 * quote by preceding it with an additional double quote.
 *
 * The first record in a CSV file may include the names for table columns,
 * however:
 *
 * - There is no syntax within a CSV file to indicate if the first line is
 *   a header or not.
 *
 * - RFC 4180 recommends using a MIME type argument to indicate that a header
 *   is present, however this requires first detecting the header in order to
 *   set the MIME type. Yet there is no way to do so.
 *
 * - RFC 4180 and common use all use the .csv file name extension, which does
 *   not have a MIME type or any indication that a header is present.
 *
 * So, while a CSV header row is optional, there is no way to detect when it
 * is or is not there. We are forced to follow overwhelming convention that
 * the first row is always a header.
 *
 * All further records provide table data. Every record must have the
 * same number of values.
 *
 *
 * #### Table decode limitations
 * Description: CSV files do not support descriptions. The returned table's
 * description is left empty.
 *
 * Name: CSV files do not support table names. The returned table's short
 * and long names are left empty.
 *
 * Column names: This class assumes the first row of the CSV file contains
 * the names of columns.  The returned table uses these CSV names as column
 * short names, but leaves column long names empty.
 *
 * Column data types: The CSV syntax does not provide data types for column
 * values, these data types are automatically inferred by the
 * Drupal\chart_suite\SDSC\StructuredData\Table class. That class scans through each column and
 * looks for a consistent interpretation of the values as integers,
 * floating-point numbers, booleans, etc., then sets the data type
 * accordingly.
 *
 *
 * #### Table encode limitations
 * Since CSV does not support descriptive information for the table,
 * the table's short name, long name, and description are not included
 * in the encoded text.
 *
 * Since CSV only supports a single name for each column, the table's
 * column short names are output to the encoded text, but the column
 * long names, descriptions, and data types are not included.
 *
 * Column value data types are used to guide CSV encoding. Values
 * that are integers, floating-point numbers, booleans, or nulls are
 * output as single un-quoted tokens. All other value types are output
 * as double-quoted strings.
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
 * @version 0.0.3. Revised to support parsing Mac Excel CSV files, which use
 * CR line endings for middle file lines, and LF for the last line of the file.
 */
final class CSVTableFormat
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
     * decode and encode tables in CSV.
     */
    public function __construct( )
    {
        parent::__construct( );

        $this->attributes['syntax']         = 'CSV';
        $this->attributes['name']           = 'CSV';
        $this->attributes['longName']       = 'Comma-Separated Values (CSV)';
        $this->attributes['MIMEType']       = 'text/csv';
        $this->attributes['fileExtensions'] = array( 'csv' );
        $this->attributes['description']    =
            'The CSV (Comma-Separated Values) format encodes tabular data ' .
            'with an unlimited number of rows and columns. Each column has ' .
            'a short name. All rows have a value for every column. Row ' .
            'values are typically integers or floating-point numbers, but ' .
            'they also may be strings and booleans.';
        $this->attributes['expectedUses'] = array(
            'Tabular data with named columns and rows of values' );
        $this->attributes['standards'] = array(
            array(
                'issuer' => 'RFC',
                'name' => 'IETF RFC 4180',
                'natureOfApplicability' => 'specifies',
                'details' => 'Common Format and MIME Type for Comma-Separated Values (CSV) Files'
            )
        );
        $this->attributes['creationDate']         = '2005-10-01 00:00:00';
        $this->attributes['lastModificationDate'] = '2005-10-01 00:00:00';

        $this->attributes['contributors'] = array(
            array(
                'name'            => 'Y. Shafranovich',
                'details'         => 'SolidMatrix Technologies, Inc',
                'identifiedUsing' => 'Text'
            )
        );

        // Unknown:
        //  identifier
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
        return 2;
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
     * The CSV format always returns an array containing a single
     * Drupal\chart_suite\SDSC\StructuredData\Table object.
     */
    public function decode( &$text )
    {
        // PHP's str_getcsv( ) does not parse IETF RFC 4180
        // compliant CSV files properly. Further, it parses only
        // a single line of text, so parsing an entire table
        // requires exploding the text into rows first. But this
        // requires handling embedded carriage returns and line feeds
        // in the text, which can't be done with a simple PHP
        // explode( ). So we are forced to use a custom parser.
        //
        // IETF RFC 4180 and common use differ on the treatment of line
        // endings:
        //
        // - RFC 4180 directs that each line end with CR-LF, in that order.
        //
        // - MS Excel 365, Apple Numbers, LibreOffice, and OpenOffice all
        //   end each line with LF.
        //
        // - MS Excel 2011 on the Mac, and MS Excel 365 saving into the
        //   "CSV for Mac" format, end all lines except the last one with
        //   a CR. The last line is ended with an LF.

        //
        // Preprocess
        // -----------------------------------------------------
        // Sweep through the string and execute a function on every
        // double-quoted string. In each one, replace special characters with
        // a special marker. There are four special characters:
        //
        // - Comma = \eC.
        // - Carriage-return = \eR.
        // - Linefeed = \eN.
        // - Double-quote = \eQ.
        //
        // \e is the ESCAPE character. Here we *ASSUME* that none of the
        // above escape sequences will occur within quoted text. This seems
        // very unlikely, but if they do this approach will garble the data.
        //
        if ( empty( $text ) )
            return array( );        // No table

        $markedText = preg_replace_callback(
            '/([^"]*)("((""|[^"])*)"|$)/s',
            // ----- look for all characters up to a "
            //        - look for a " to start the string
            //           --------- look for "" or all characters up to a "
            //                       - or look for the end of line
            //
            //......................... = $match[0] = whole string
            //(.....)                   = $match[1] = text up to string
            //       (................) = $match[2] = string with quotes & EOL
            //         (..........)     = $match[3] = string without quotes
            //          (.......)       = $match[4] = string without quotes
            function( $match )
            {
                // If the match doesn't find any quoted strings,
                // then return the original text
                if ( count( $match ) < 4 )
                    return $match[0];

                // Carriage returns and line feeds within quoted text will
                // confuse a later explode, so replace them with a special
                // marker sequence.  The marker uses a CR, which we'll
                // later insure cannot occur outside of quoted text.

                // Use the string without quotes and replace CR & LF
                // with a CR marker.
                $str = str_replace( "\r", "\eR", $match[3] );
                $str = str_replace( "\n", "\eN", $str );

                // Replace embedded double quotes with a CR marker.
                $str = str_replace( '""', "\eQ", $str );

                // Replace embedded commas with a CR marker.
                $str = str_replace( ',',  "\eC", $str );

                // Replace CRLF in the text before the string with
                // just LF. Replace CR alone with just LF.
                //$before = preg_replace( '/\r\n?/', "\n", $match[1]);

                // Append the processed quoted string, now without quotes
                // and without embedded LF, quotes, or commas.
                return $match[1] . $str;
            }, $text );

        // Unify the line-ending style by replacing all CR-LF, LF-CR, CR,
        // and LF endings with LF alone.
        $markedText = preg_replace( '/\r\n?/', "\n", $markedText);
        $markedText = preg_replace( '/\n\r?/', "\n", $markedText);

        // Remove the last LF, if any, so that exploding on LF
        // doesn't leave us an extra empty line at the end.
        $markedText = preg_replace( '/\n$/', '', $markedText );


        //
        // Explode
        // -----------------------------------------------------
        // Explode the string into lines on LF. We've already
        // insured that LF doesn't exist in any quoted text.
        $lines = explode( "\n", $markedText );
        unset( $markedText );


        //
        // Parse
        // -----------------------------------------------------
        // Explode each line on a comma, then unmark the marked
        // text inside double-quote values.
        $rows = array_map(
            function( $line )
            {
                $fields = explode( ',', $line );
                return array_map(
                    function( $field )
                    {
                        // Un-escape the previously marked characters.
                        $field = str_replace( "\eC", ',', $field );
                        $field = str_replace( "\eQ", '"', $field );
                        $field = str_replace( "\eN", '\n', $field );
                        $field = str_replace( "\eR", '\r', $field );
                        return $field;
                    }, $fields );
            }, $lines );
        unset( $lines );

        // If there are no rows, the file was empty and there is
        // no table to return.
        //
        // This 'if' checks will stay in the code, but there appears
        // to be no way to trigger it. An empty string '' is caught
        // earlier. A white-space string '   ' is really one row of
        // text in one column.  An empty quote string '""' is also
        // really one row of text. So, there is no obvious way to
        // hit this condition, but let's be paranoid.
        // @codeCoverageIgnoreStart
        if ( count( $rows ) == 0 )
            return array( );
        // @codeCoverageIgnoreEnd

        // The first row should be the column names. We have no way
        // of knowing if it is or not, so we just have to hope.
        $header   = array_shift( $rows );
        $nColumns = count( $header );

        // An empty file parsed as CSV produces a single column,
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
                    'CSV table rows must all have the same number of values as the first row.' );
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
        // Header provides column names.
        // No column descriptions or data types.
        foreach ( $header as &$field )
            $table->appendColumn( array( 'name' => $field ) );


        // Convert values rows
        // -----------------------------------------------------
        // So far, every value in every row is a string. But
        // we'd like to change to the "best" data type for
        // the value. If it is an integer, make it an integer.
        // If it is a float, make it a double. If it is a
        // boolean, make it a boolean. Only fall back to string
        // types if nothing better will do.
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
        // Parsed content provides rows.
        if ( count( $rows ) != 0 )
            $table->appendRows( $rows );
        return array( $table );
    }




    /**
     * @copydoc AbstractFormat::encode
     *
     * #### Encode limitations
     * The CSV format only supports encoding a single
     * Drupal\chart_suite\SDSC\StructuredData\Table in the format. An exception is thrown
     * if the $objects argument is not an array, is empty, contains
     * more than one object, or it is not a Table.
     */
    public function encode( &$objects, $options = '' )
    {
        //
        // Validate arguments
        // -----------------------------------------------------
        if ( $objects == NULL )
            return NULL;            // No table to encode

        if ( !is_array( $objects ) )
            throw new \InvalidArgumentException(
                'CSV encode requires an array of objects.' );

        if ( count( $objects ) > 1 )
            throw new \InvalidArgumentException(
                'CSV encode only supports encoding a single object.' );

        $table = &$objects[0];
        if ( !is_a( $table, 'Drupal\chart_suite\SDSC\StructuredData\Table', false ) )
            throw new \InvalidArgumentException(
                'CSV encode object must be an Drupal\chart_suite\SDSC\StructuredData\Table.' );

        $nColumns = $table->getNumberOfColumns( );
        if ( $nColumns <= 0 )
            return NULL;            // No data to encode
        $nRows = $table->getNumberOfRows( );
        $text  = '';


        //
        // Encode header
        // -----------------------------------------------------
        //   Ignore the table name and other attributes since
        //   CSV has no way to include them.
        //
        //   Generate a single row with comma-separated column
        //   names, each within double quotes.
        for ( $column = 0; $column < $nColumns; $column++ )
        {
            $name = $table->getColumnName( $column );
            if ( $column != 0 )
                $text .= ",";
            $text .= '"' . $name . '"';
        }
        $text .= "\r\n";


        //
        // Encode rows
        // -----------------------------------------------------
        //   Output unquoted values for integers, floating-point
        //   values, booleans, and nulls. The rest are quoted.
        for ( $row = 0; $row < $nRows; $row++ )
        {
            $r = $table->getRowValues( $row );

            for ( $column = 0; $column < $nColumns; $column++ )
            {
                if ( $column != 0 )
                    $text .= ",";
                $v = $r[$column];
                if ( is_int( $v ) || is_float( $v ) ||
                    is_bool( $v ) || is_null( $v ) )
                    $text .= $v;
                else
                    $text .= '"' . $v . '"';
            }
            $text .= "\r\n";
        }

        return $text;
    }
    // @}
}

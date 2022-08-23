<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\HTMLTableFormat to parse and
 * serialize data in the HTML text format.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;

//use Drupal\chart_suite\SDSC\StructuredData\Table;

/**
 * @class HTMLTableFormat
 * HTMLTableFormat provides decode and encode functions that
 * map between HTML table text and a Table.
 *
 * HTML is a general-purpose document structure and content syntax
 * used for web pages. Those pages may include headings, paragraphs,
 * images, linkes, and... tables. This parser looks for tables in
 * the HTML, and parses and returns the selected table (defaulting
 * to the first table). An HTML table can have an arbitrary number
 * of rows and columns. All columns have a name. Row values may be
 * of any data type, though they are typically numeric.
 *
 *
 * ####Table syntax
 * An HTML table is delimted by `<table>...</table>`.  Between these,
 * column headings are usually included within `<thead>...</thead>`,
 * while table content is within `<tbody>...</tbody>`.
 *
 * Headings and content are divided into rows, delimited by
 * `<tr>...</tr>`. Each value in a row is delimited by either
 * `<th>...</th>` or `<td>...</td>`.
 *
 * This parser uses the first row in the `<thead>...</thead>` section
 * to use as the table's column names. All other heading rows are
 * ignored. If there are no heading rows, this parser uses the first
 * row in the `<tbody>...</tbody>` section for the column names.
 * All further body rows are used as table content.
 *
 * An optional `<caption>...</caption>` section's contents are used
 * as the table's short name. If there is no caption, the table's
 * name is left empty.
 *
 * HTML attributes are ignored, including `colspan`, `rowspan`, and
 * all styles, classes, IDs, etc. Table `<colgroup>...</colgroup>`
 * and `<col>...</col>` are ignored as these are used for styling and
 * provide no structure or content information.  Table
 * `<tfoot>...</tfoot>` are ignored.
 *
 * The following HTML table is parsed as having two columns and two
 * data rows:
 * <pre>
 *     &lt;table>
 *         &lt;head>
 *             &lt;tr>&lt;th>Temperature&lt;/th>&lt;th>Pressure&lt;/th>&lt;/tr>
 *         &lt;thead>
 *         &lt;tbody>
 *             &lt;r>&lt;td>123.4&lt;/td><&lt;d>567.8&lt;/td>&lt;/tr>
 *             &lt;r>&lt;td>901.2&lt;/td>&lt;td>345.6&lt;/td>&lt;/tr>
 *         &lt;tbody>
 *     &lt;table>
 * </pre>
 *
 *
 * #### Table decode limitations
 * HTML does not provide column descriptive information beyond column
 * names. The returned table uses these HTML names as column short names,
 * but leaves column long names, descriptions, and data types empty.
 *
 * HTML's table caption sets the table's short name. The returned table's
 * long name and description are left empty.
 *
 * Since the HTML syntax does not provide data types for column values,
 * these data types are automatically inferred by the
 * Drupal\chart_suite\SDSC\StructuredData\Table class. That class scans through each column and
 * looks for a consistent interpretation of the values as integers,
 * floating-point numbers, booleans, etc., then sets the data type
 * accordingly.
 *
 *
 * #### Table encode limitations
 * The table's short name, if any, is used as the HTML table caption.
 * The table's long name and description are not included in the encoded
 * text.
 *
 * Since HTML only supports a single name for each column, the table's
 * column short names are output to the encoded text. The column
 * long names, descriptions, and data types are not included.
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
 */
final class HTMLTableFormat
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
     * decode and encode tables in HTML.
     */
    public function __construct( )
    {
        parent::__construct( );

        $this->attributes['syntax']         = 'HTML';
        $this->attributes['name']           = 'HTML-Table';
        $this->attributes['longName']       = 'Hypertext Markup Language (HTML) Table';
        $this->attributes['MIMEType']       = 'text/html';
        $this->attributes['fileExtensions'] = array( 'htm', 'html' );
        $this->attributes['description'] =
            'The HTML (Hyper-Text Markup Language) format encodes ' .
            'documents with multiple headings, body text, tables, and ' .
            'images. Tabular data may have an unlimited number of rows ' .
            'and columns. Each column has a short name. All rows have a ' .
            'value for every column. Row values are typically integers ' .
            'or floating-point numbers, but they also may be strings and ' .
            'booleans.';
        $this->attributes['expectedUses'] = array(
            'Tabular data with named columns and rows of values' );
        $this->attributes['standards'] = array(
            array(
                'issuer' => 'W3C',
                'name' => 'HTML5',
                'natureOfApplicability' => 'specifies',
                'details' => 'A vocabulary and associated APIs for HTML and XHTML'
            )
        );
        $this->attributes['creationDate']         = '2014-10-28 00:00:00';
        $this->attributes['lastModificationDate'] = '2014-10-28 00:00:00';

        $this->attributes['contributors'] = array(
            array(
                'name'            => 'Ian Hickson',
                'details'         => 'Google, Inc.',
                'identifiedUsing' => 'Text'
            ),
            array(
                'name'            => 'Robin Berjon',
                'details'         => 'W3C',
                'identifiedUsing' => 'Text'
            ),
            array(
                'name'            => 'Steve Faulkner',
                'details'         => 'The Paciello Group',
                'identifiedUsing' => 'Text'
            ),
            array(
                'name'            => 'Travis Leithead',
                'details'         => 'Microsoft Corporation',
                'identifiedUsing' => 'Text'
            ),
            array(
                'name'            => 'Erika Doyle Navara',
                'details'         => 'Microsoft Corporation',
                'identifiedUsing' => 'Text'
            ),
            array(
                'name'            => 'Edward O\'Conner',
                'details'         => 'Apple Inc.',
                'identifiedUsing' => 'Text'
            ),
            array(
                'name'            => 'Silvia Pfeiffer',
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
        return 5;
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
     * The HTML format always returns an array containing zero or more
     * Drupal\chart_suite\SDSC\StructuredData\Table objects.
     */
    public function decode( &$text )
    {
        if ( empty( $text ) )
            return array( );        // No table

        //
        // Parse
        // -----------------------------------------------------
        // Parse the HTML text into a DOM document.  If any error
        // occurs, reject the content.
        $oldSetting = libxml_use_internal_errors( true );

        $doc = new \DOMDocument( );
        $doc->loadHTML( $text );

        $errors = libxml_get_errors( );
        if ( count( $errors ) > 0 )
        {
            // One or more errors occurred. $errors holds a list of
            // libXMLError objects that each have a level, code, column,
            // message, file, and line.
            //
            // We forward the message for the first error.
            libxml_use_internal_errors( $oldSetting );
            throw new SyntaxException( $errors[0]->message );
        }
        libxml_use_internal_errors( $oldSetting );


        //
        // Find and decode the tables
        // -----------------------------------------------------
        $tableNodes = $doc->getElementsByTagName( 'table' );
        $tables = array( );
        for ( $tableIndex = 0; $tableIndex < $tableNodes->length; $tableIndex++ )
        {
            $tableNode = $tableNodes->item( $tableIndex );


            //
            // Get caption as table name
            // -----------------------------------------------------
            //   Look for a <caption> as the table name.
            $tableName = NULL;
            $captionNodes = $tableNode->getElementsByTagName( 'caption' );
            if ( $captionNodes->length > 0 )
                $tableName = $captionNodes[0]->nodeValue;


            //
            // There are multiple variations we need to handle.
            //
            // 1. A correct table has column names in a <thead> and
            // table rows in a <tbody>
            //  <table>
            //      <thead>
            //          <tr><td>Name1</td><td>...</td></tr>
            //      </thead>
            //      <tbody>
            //          <tr><td>Value1</td><td>...</td></tr>
            //      </tbody>
            //  </table>
            //
            // 2. There may be no <thead>. The first row in the <tbody>
            // has the column names:
            //  <table>
            //      <tbody>
            //          <tr><td>Name1</td><td>...</td></tr>
            //          <tr><td>Value1</td><td>...</td></tr>
            //      </tbody>
            //  </table>
            //
            // 3. There may be no <tbody>, with rows just given in
            // the table:
            //  <table>
            //      <tr><td>Name1</td><td>...</td></tr>
            //      <tr><td>Value1</td><td>...</td></tr>
            //  </table>
            //
            // 4. There may be a <thead> for the column names, but
            // the rest of the table's rows are not in a <tbody>:
            //  <table>
            //      <thead>
            //          <tr><td>Name1</td><td>...</td></tr>
            //      </thead>
            //      <tr><td>Value1</td><td>...</td></tr>
            //  </table>
            //
            // In all cases, we treat <th> and <td> the same, ignore
            // <tfoot> for a footer, and ignore <colgroup> and <col>,
            // which are primarily for column formatting. We also
            // ignore all attributes (such as colspan).
            //
            // For column names in the <thead>, we only use the first
            // row.

            //
            // Get column names
            // -----------------------------------------------------
            //   Look through all <thead>s (should be zero or one) and
            //   all <tr>s in those <thead>s. Use the first <tr>'s
            //   <th> or <td> elements as column names.
            $columnNames = array( );
            $headNodes = $tableNode->getElementsByTagName( 'thead' );
            foreach ( $headNodes as $headNode )
            {
                // Get the <tr>s in this <thead>
                $trNodes = $headNode->getElementsByTagName( 'tr' );
                foreach ( $trNodes as $trNode )
                {
                    // Use <td> or <th> children of the <tr>
                    // as column names.
                    $children = $trNode->childNodes;
                    foreach ( $children as $child )
                    {
                        if ( $child->nodeName == 'td' ||
                            $child->nodeName == 'th' )
                            $columnNames[] = $child->nodeValue;
                    }

                    // If we found column names, stop this.
                    if ( !empty( $columnNames ) )
                        break;
                }

                // If we found column names, stop this.
                if ( !empty( $columnNames ) )
                    break;
            }

            // At this point, we may have found column names in a
            // <thead>, or we may not have. Move on to look for
            // rows of data.


            //
            // Get rows
            // -----------------------------------------------------
            //   Collect all <tr>s. Ignore those that aren't direct
            //   children of the <table> or a <tbody> child of the
            //   <table>. This will skip <tr>s in <thead> or <tfoot>,
            //   and any <tr>s in nested tables.
            $rows = array( );
            $trNodes = $tableNode->getElementsByTagName( 'tr' );
            foreach ( $trNodes as $trNode )
            {
                // Ignore this <tr> unless its parent is a <tbody>
                // or the <table>. This eliminates <tr>s in <thead>
                // and <tfoot>.
                if ( $trNode->parentNode->nodeName != 'table' &&
                    $trNode->parentNode->nodeName != 'tbody' )
                    continue;

                // Ignore this <tr> unless its parent is the table
                // we're parsing, or unless its parent is a <tbody>
                // and that node's parent is the table.
                if ( $trNode->parentNode->nodeName == 'table' &&
                    $trNode->parentNode !== $tableNode )
                    continue;
                if ( $trNode->parentNode->nodeName == 'tbody' &&
                    $trNode->parentNode->parentNode !== $tableNode )
                    continue;

                // Collect the <tr> node's children <tr> or <th>
                // nodes and use their values as row values.
                // This ignores any nested HTML.
                $children = $trNode->childNodes;
                if ( $children->length > 0 )
                {
                    // Create a row from the <th> and
                    // <td> nodes
                    $row = array( );
                    foreach ( $children as $child )
                    {
                        if ( $child->nodeName == 'td' ||
                            $child->nodeName == 'th' )
                            $row[] = $child->nodeValue;
                    }
                    $rows[] = $row;
                }
            }

            // If there was no <thead> earlier, use the first
            // table row for the column names. If there are no rows,
            // though, then we have no columns or rows, and thus no
            // table.
            if ( count( $columnNames ) == 0 )
            {
                if ( count( $rows ) <= 0 )
                    continue;           // No rows or columns! No table.
                $columnNames = array_shift( $rows );
            }


            // Build table
            // -----------------------------------------------------
            $attributes = array(
                // 'name' perhaps unknown
                // 'longName' unknown
                // 'description' unknown
                // 'sourceFileName' unknown
                'sourceMIMEType'   => $this->getMIMEType( ),
                'sourceSyntax'     => $this->getSyntax( ),
                'sourceSchemaName' => $this->getName( )
            );
            if ( $tableName != NULL )
                $attributes['name'] = $tableName;
            $table = new Table( $attributes );


            //
            // Add columns
            // -----------------------------------------------------
            //   Header provides column names.
            //   No column descriptions or data types.
            foreach ( $columnNames as &$name )
                $table->appendColumn( array( 'name' => $name ) );


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
            {
                try
                {
                    $table->appendRows( $rows );
                }
                catch ( \InvalidArgumentException $e )
                {
                    throw new InvalidContentException( $e->getMessage( ) );
                }
            }
            $tables[] = $table;
        }
        if ( empty( $tables ) )
            return NULL;
        return $tables;
    }





    /**
     * @copydoc AbstractFormat::encode
     *
     * #### Encode limitations
     * The HTML format supports encoding multiple
     * Drupal\chart_suite\SDSC\StructuredData\Table objects in the format. An exception is thrown
     * if the $objects argument is not an array or contains an object that
     * is not a Table.
     */
    public function encode( &$objects, $options = '' )
    {
        //
        // Validate arguments
        // -----------------------------------------------------
        if ( empty( $objects ) )
            return NULL;            // No table to encode
        if ( !is_array( $objects ) )
            throw new \InvalidArgumentException(
                'HTML encode requires an array of objects.' );

        //
        // Encode all table objects
        // -----------------------------------------------------
        $text = '';
        foreach ( $objects as &$object )
        {
            if ( !is_a( $object, 'Drupal\chart_suite\SDSC\StructuredData\Table', false ) )
                throw new \InvalidArgumentException(
                    'HTML encode object must be a table.' );

            $nColumns = $object->getNumberOfColumns( );
            if ( $nColumns <= 0 )
                continue;
            $nRows    = $object->getNumberOfRows( );
            $text     = "<table>\n";


            //
            // Encode caption
            // -----------------------------------------------------
            //  If there is a table name, use it as the caption.
            $tableName = $object->getName( );
            if ( !empty( $tableName ) )
                $text .= "  <caption>$tableName</caption>\n";


            //
            // Encode header
            // -----------------------------------------------------
            //   Generate a single row with comma-separated column
            //   names.
            $text .= "  <thead>\n";
            $text .= '    <tr>';
            for ( $column = 0; $column < $nColumns; $column++ )
            {
                $text .= '<th>' .
                    $object->getColumnName( $column ) .
                    '</th>';
            }
            $text .= "</tr>\n";
            $text .= "  </thead>\n";


            //
            // Encode rows
            // -----------------------------------------------------
            $text .= "  <tbody>\n";
            for ( $row = 0; $row < $nRows; $row++ )
            {
                $r = $object->getRowValues( $row );

                $text .= '    <tr>';
                for ( $column = 0; $column < $nColumns; $column++ )
                {
                    $text .= '<td>' . $r[$column] . '</td>';
                }
                $text .= "</tr>\n";
            }
            $text .= "  </tbody>\n</table>\n";
        }
        if ( empty( $text ) )
            return NULL;

        return $text;
    }
    // @}
}

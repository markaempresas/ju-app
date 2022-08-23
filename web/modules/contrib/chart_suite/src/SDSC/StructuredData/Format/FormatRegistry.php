<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\FormatRegistry with methods to
 * list known formats and intuit the format of an unknown file.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;

use Drupal\chart_suite\SDSC\StructuredData\Table;
/**
 * @class FormatRegistry
 * FormtRegistry is a static class that provides methods to list known
 * format encoders/decoders, find an encoder based on the type of data
 * to encode, and find a decoder capable of decoding a given file.
 *
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    2/17/2016
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 */
final class FormatRegistry
{
//----------------------------------------------------------------------
// Fields
//----------------------------------------------------------------------
    /**
     * @var  array $formatsByName
     * An associative array of all formats where array keys are the
     * lower case format names (which should be unique) and array
     * values are format objects.
     */
    private static $formatsByName;

    /**
     * @var  array $formatsByComplexity
     * An array of 11 entries with keys 0..10 for the standard format
     * complexity levels, where array values are associative arrays
     * where array keys are the lower case format names and array
     * values are format objects.
     */
    private static $formatsByComplexity;

    /**
     * @var  boolean $initialized
     * True if the $formatsByName array has been initialized.
     */
    private static $initialized = FALSE;




//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Blocked constructor.
     *
     * Because private constructors cannot be tested in unit testing,
     * we mark this function as being ignored in code coverage reports.
     * @codeCoverageIgnore
     */
    private function __construct( )
    {
    }
    // @}
    /**
     * @name Destructors
     */
    // @{
    /**
     * Blocked constructor.
     *
     * Because private constructors cannot be tested in unit testing,
     * we mark this function as being ignored in code coverage reports.
     * @codeCoverageIgnore
     */
    private function __destruct( )
    {
    }
    // @}





//----------------------------------------------------------------------
// Format list operations
//----------------------------------------------------------------------
    /**
     * @name Format list operations
     */
    // @{
    /**
     * Initializes the format list with instances of known formats.
     */
    static private function initialize( )
    {
        // Create empty arrays.
        self::$formatsByName = array( );
        self::$formatsByComplexity = array( );
        for ( $i = 0; $i <= 10; ++$i )
            self::$formatsByComplexity[$i] = array( );

        // Add well-known formats.
        self::addFormat( new CSVTableFormat( ) );
        self::addFormat( new TSVTableFormat( ) );
        self::addFormat( new HTMLTableFormat( ) );
        self::addFormat( new JSONTableFormat( ) );
        self::addFormat( new JSONTreeFormat( ) );
        self::addFormat( new JSONGraphFormat( ) );

        ksort( self::$formatsByName );
        self::$initialized = TRUE;
    }



    /**
     * Adds a new format object to the list.
     *
     * Example:
     * @code
     *   FormatRegistry::addFormat( new MyFormat( ) );
     * @endcode
     *
     * @param AbstractFormat $format  the format to add.
     *
     * @throw \InvalidArgumentException if $format is not a subclass
     * of AbstractFormat.
     */
    static public function addFormat( $format )
    {
        // Validate.
        if ( $format == NULL ||
            !($format instanceof AbstractFormat) )
            throw new \InvalidArgumentException(
                'Format argument must be an instance of AbstractFormat.' );

        // Add the format to the by-name array.
        $name = strtolower( $format->getName( ) );
        self::$formatsByName[$name] = $format;

        // Add the format to the by-complexity array.
        $c = $format->getComplexity( );
        if ( $c < 0 )
            $c = 0;
        else if ( $c > 10 )
            $c = 10;
        self::$formatsByComplexity[$c][$name] = $format;
    }
    // @}
    //----------------------------------------------------------------------
    // Format list methods
    //----------------------------------------------------------------------
    /**
     * @name Format list methods
     */
    // @{
    /**
     * Finds a list of format objects that support the given file name
     * extension.
     *
     * Example:
     * @code
     *   $formatArray = FormatRegistry::findFormatsByExtension( "csv" );
     * @endcode
     *
     * @param   string  $extension  the file name extension, without a dot
     *
     * @return  array               an array of format objects that support
     * encoding or decoding files with the file name extension
     *
     * @throw \InvalidArgumentException if $extension is not a scalar string,
     * or it is empty.
     */
    static public function findFormatsByExtension( $extension )
    {
        if ( !is_scalar( $extension ) )
            throw new \InvalidArgumentException(
                'File name extension argument must be a scalar string.' );
        if ( empty( $extension ) )
            throw new \InvalidArgumentException(
                'File name extension argument must not be empty.' );

        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        // Make sure there is no leading dot.
        $ext = pathinfo( $extension, PATHINFO_EXTENSION );
        if ( empty( $ext ) )
            $ext = $extension;

        // Search for formats that support this extension.
        $results = array( );
        foreach ( self::$formatsByName as $format )
        {
            if ( $format->isFileExtension( $ext ) )
                $results[] = $format;
        }
        return $results;
    }

    /**
     * Finds a format object by name.
     *
     * Example:
     * @code
     *   $format = FormatRegistry::findFormatByName( 'CSV' );
     * @endcode
     *
     * @param string $name  the name of a format to look for
     *
     * @return AbstractFormat  the format object with the given name,
     * or a NULL if the name is not found.
     *
     * @throw \InvalidArgumentException if $name is not a scalar string,
     * or it is empty.
     */
    static public function findFormatByName( $name )
    {
        if ( !is_scalar( $name ) )
            throw new \InvalidArgumentException(
                'Format name argument must be a scalar string.' );
        if ( empty( $name ) )
            throw new \InvalidArgumentException(
                'Format name argument must not be empty.' );

        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        $name = strtolower( $name );
        if ( isset( self::$formatsByName[(string)$name] ) )
            return self::$formatsByName[(string)$name];
        return NULL;
    }

    /**
     * Returns a list of the names of all formats.
     *
     * Example:
     * @code
     *   $formatNames = FormatRegistry::getAllFormats( );
     *   foreach ( $formatNames as $name )
     *   {
     *     $format = FormatRegistry::findFormatByName( $name );
     *     ...
     *   }
     * @endcode
     *
     * @return array  an array containing the names of all formats, in
     * an arbitrary order.
     */
    static public function getAllFormats( )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        return array_keys( self::$formatsByName );
    }



    /**
     * Returns a list of the names of all formats that can decode
     * a Graph.
     *
     * Example:
     * @code
     *   $formatNames = FormatRegistry::getAllGraphDecoders( );
     * @endcode
     *
     * @return array  an array containing the names of all format that
     * can decode a Graph, in arbitrary order.
     */
    static public function getAllGraphDecoders( )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        $results = array( );
        foreach ( self::$formatsByName as &$format )
        {
            if ( $format->canDecodeGraphs( ) )
                $results[] = $format->getName( );
        }
        return $results;
    }

    /**
     * Returns a list of the names of all formats that can decode
     * a Table.
     *
     * Example:
     * @code
     *   $formatNames = FormatRegistry::getAllTableDecoders( );
     * @endcode
     *
     * @return array  an array containing the names of all format that
     * can decode a Table, in arbitrary order.
     */
    static public function getAllTableDecoders( )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        $results = array( );
        foreach ( self::$formatsByName as &$format )
        {
            if ( $format->canDecodeTables( ) )
                $results[] = $format->getName( );
        }
        return $results;
    }

    /**
     * Returns a list of the names of all formats that can decode
     * a Tree.
     *
     * Example:
     * @code
     *   $formatNames = FormatRegistry::getAllTreeDecoders( );
     * @endcode
     *
     * @return array  an array containing the names of all format that
     * can decode a Tree, in arbitrary order.
     */
    static public function getAllTreeDecoders( )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        $results = array( );
        foreach ( self::$formatsByName as &$format )
        {
            if ( $format->canDecodeTrees( ) )
                $results[] = $format->getName( );
        }
        return $results;
    }

    /**
     * Returns a list of the names of all formats that can encode
     * a Graph.
     *
     * Example:
     * @code
     *   $formatNames = FormatRegistry::getAllGraphEncoders( );
     * @endcode
     *
     * @return array  an array containing the names of all format that
     * can encode a Graph, in arbitrary order.
     */
    static public function getAllGraphEncoders( )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        $results = array( );
        foreach ( self::$formatsByName as &$format )
        {
            if ( $format->canEncodeGraphs( ) )
                $results[] = $format->getName( );
        }
        return $results;
    }

    /**
     * Returns a list of the names of all formats that can encode
     * a Table.
     *
     * Example:
     * @code
     *   $formatNames = FormatRegistry::getAllTableEncoders( );
     * @endcode
     *
     * @return array  an array containing the names of all format that
     * can encode a Table, in arbitrary order.
     */
    static public function getAllTableEncoders( )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        $results = array( );
        foreach ( self::$formatsByName as &$format )
        {
            if ( $format->canEncodeTables( ) )
                $results[] = $format->getName( );
        }
        return $results;
    }

    /**
     * Returns a list of the names of all formats that can encode
     * a Tree.
     *
     * Example:
     * @code
     *   $formatNames = FormatRegistry::getAllTreeEncoders( );
     * @endcode
     *
     * @return array  an array containing the names of all format that
     * can encode a Tree, in arbitrary order.
     */
    static public function getAllTreeEncoders( )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd

        $results = array( );
        foreach ( self::$formatsByName as &$format )
        {
            if ( $format->canEncodeTrees( ) )
                $results[] = $format->getName( );
        }
        return $results;
    }
    // @}
    //----------------------------------------------------------------------
    // Decode methods
    //----------------------------------------------------------------------
    /**
     * @name Decode methods
     */
    // @{
    /**
     * Parses a text string containing data and returns an array
     * containing one or more data objects.
     *
     * If the text is empty, an empty array is returned.
     *
     * Registered formats are checked to see which one(s), if any,
     * can decode the given text. If no format recognizes the text,
     * an empty array is returned. If a format recognizes the text,
     * but finds errors in its content, an exception is thrown
     * reporting those errors. Otherwise if a format recognizes the
     * text and parses it without errors, then an array of objects
     * parsed from the text is returned.
     *
     * Some registered formats have higher syntactic complexity
     * than others (e.g. XML is more complex than CSV). Because low
     * complexity formats may accept virtually any input, they are
     * a fall-back after checking high complexity formats first.
     *
     * Decoding the text requires a search through the format registry
     * to find the proper decoder. When the second argument is a
     * file name extension (without the leading dot), only those formats
     * that support the extension are tested for decoding the text.
     * When the extension string is empty (default), all formats in
     * the registry are tested.
     *
     * Example:
     * @code
     *   $objects = FormatRegistry::decode( $text );
     * @endcode
     *
     * @param  string  $text   a text string containing data to decode
     * into an array of returned data objects.
     *
     * @param  string  $extension a text string containing a filename
     * extension (without the leading dot) used to constrain the set of
     * formats to test for decoding the file. When the string is empty
     * (default), all formats are searched.
     *
     * @return array           returns an array containing objects
     * parsed from the text, or an empty array if no parsable data
     * was found.
     *
     * @throws FormatException  if the text could not be parsed
     * properly due to a variety of format-specific syntax and content
     * errors (see SyntaxException and InvalidContentException).
     */
    static public function decode( &$text, $extension = '' )
    {
        // Since the registry is initialized only once per application run,
        // it is not possible for unit tests in a single application to test
        // every code path to initialize the registry.
        // @codeCoverageIgnoreStart
        if ( !self::$initialized )
            self::initialize( );
        // @codeCoverageIgnoreEnd


        // Return an empty array if there is no content.
        if ( $text == NULL || empty( $text ) )
            return array( );

        // Run through the registered formats in high-to-low complexity
        // order. Check each one to see if it can parse the text.
        //
        // We have four cases to watch for:
        //
        //  - Clear success. The format parsed the input and returned a
        //    complex object, such as a tree, graph, or multi-column
        //    and multi-row table.
        //
        //  - Suspicious success. The format parsed the input and returned
        //    a very simple object, such as a table with one column.
        //    This can happen with very low complexity formats
        //    that can turn just about anything into a one-column table
        //    (e.g. CSV and TSV).
        //
        //  - Clear failure. The format returned nothing or threw a
        //    SyntaxException on the input, indicating that the input
        //    in no way matched the format's expected syntax.
        //
        //  - Probable failure. The format recognized the input as having
        //    valid syntax, but then threw an InvalidContentException
        //    when something went wrong.
        //
        // On clear success, the format search ends and the complex
        // content is returned.
        //
        // On suspicious success, the returned very simple object is
        // saved and the format search continues to see if any other
        // format can do better. If none can, the simple object is
        // returned.
        //
        // On clear failure, the thrown exception is saved and the
        // format search continues. If no format recognizes the input,
        // the last save extension is thrown.
        //
        // On probable failure, the thrown exception is saved and
        // the format search continues. If no other format recognizes
        // the input, the saved exception is thrown.
        $savedException = NULL;
        $savedResult    = NULL;
        $numberDecodes  = 0;
        for ( $complexity = 10; $complexity >= 0; --$complexity )
        {
            foreach ( self::$formatsByComplexity[$complexity] as &$format )
            {
                // If a file name extension was given, skip the format
                // unless it supports the extension.
                if ( !empty( $extension ) &&
                    !in_array( $extension, $format->getFileExtensions( ) ) )
                    continue;

                ++$numberDecodes;

                try
                {
                    // Try to decode the text.
                    $result = $format->decode( $text );

                    // An exception was not thrown, so we have one of:
                    //  - Clear failure if the results are empty.
                    //  - Clear success if the results are complex.
                    //  - Suspicious success if the results are simplistic.
                    //
                    // Suspicious simplistic content tends to be a Table
                    // with one column and everything in that column.  If
                    // the results look like this, put it asside and try
                    // other formats. For everything else, accept it as
                    // a success.

                    if ( empty( $result ) )
                        continue;               // Clear failure

                    if ( count( $result ) > 1 )
                        return $result;         // Clear success

                    $object = $result[0];
                    if ( !($object instanceof Table) )
                        return $result;         // Clear success

                    if ( $object->getNumberOfColumns( ) > 1 )
                        return $result;         // Clear success

                    // Otherwise suspicious success. Save results and try
                    // another format.  Clear any saved exception since
                    // simplistic results are better than failure.
                    if ( $savedResult == NULL )
                    {
                        $savedResult    = $result;
                        $savedException = NULL;
                    }
                }
                catch ( SyntaxException $e )
                {
                    // A syntax exception was thrown. The input's
                    // syntax was so bad that it couldn't be parsed.
                    if ( $savedResult == NULL )
                        $savedException = $e;
                    continue;                   // Clear failure
                }
                catch ( InvalidContentException $e )
                {
                    // An exception was thrown that the input had
                    // problems. The syntax was apparently OK, but
                    // the content didn't make sense.  It is probable,
                    // but not guaranteed, that this is a clear failure.
                    // Save the exception and try other formats to
                    // be sure.
                    //
                    // But skip saving the exception if we have saved
                    // simplistic results from earlier. Those results
                    // are better than failure.
                    if ( $savedResult == NULL )
                        $savedException = $e;
                    continue;                   // Probable failure
                }
            }
        }

        // If we've reached this point, then we never got clear success.
        // We have one of:
        //  - No decoder found
        //  - Probable failure with a saved exception
        //  - Suspicious success with saved results
        //  - Clear failure with a saved exception
        //
        // If no formats got checked because of file name extension
        // mismatches, return an empty array.
        //
        // With clear failure, throw the saved exception.
        //
        // With suspicious success, return the saved results.
        //
        // With probable failure, throw the saved exception.
        if ( $numberDecodes == 0 )
            return array( );                    // No decoder found

        if ( $savedException != NULL )
            throw $savedException;              // Probable failure
        if ( $savedResult != NULL )
            return $savedResult;                // Suspicious success

        return array( );                        // Clear failure
    }

    /**
     * Parses a file containing data and returns an array containing
     * one or more data objects, or an empty array if the file cannot
     * be parsed.
     *
     * If the file name is empty, or the file is empty, or the
     * file does not containing any content recognized by this
     * format, the method returns an empty array.  Otherwise, the
     * returned array contains one or more objects built from the
     * parsed file contents.
     *
     * Decoding the file requires a search through the format registry
     * to find the proper decoder. When the second argument is true,
     * the file's extension is used to select only those formats that
     * support the extension. When the second argument is false (default),
     * all formats are tested to find a suitable decoder.
     *
     * Example:
     * @code
     *   $objects = FormatRegistry::decodeFile( $filename, true );
     * @endcode
     *
     * @param  string  $filename  a text string containing the name
     * of a file containing data to decode into an array of returned
     * data objects.
     *
     * @param  boolean $useExtension  when true, the decoder extracts
     * the file's extension and uses it to select appropriate formats to
     * decode the file. When false (the default), the extension is
     * ignored and all registered formats are tested.
     *
     * @return array           returns an array containing objects
     * parsed from the text, or an empty array if no parsable data
     * was found or the file could not be parsed.
     *
     * @throws FormatException  if the text could not be parsed
     * properly due to a variety of format-specific syntax and content
     * errors (see SyntaxException and InvalidContentException).
     *
     * @throws FileNotFoundException  if the file could not be opened
     * or read, such as when the file or some directory on the file path
     * does not exist or is not readable.
     *
     * @throws \InvalidArgumentException  if the file could not be read,
     * such as due to permissions problems or system level errors.
     */
    static public function decodeFile( $filename, $useExtension = false )
    {
        // Check if the file is readable.
        if ( is_readable( $filename ) === FALSE )
            throw new FileNotFoundException(
                'File or directory not found or not readable',
                0, 1, $filename );

        // Extract the file name extension, if needed.
        $extension = '';
        if ( $useExtension )
        {
            $extension = pathinfo( $filename, PATHINFO_EXTENSION );

            // If there is no extension, we have to revert to searching
            // the entire registry for a suitable decoder.
            if ( $extension == NULL )
                $extension = '';
        }


        // Read the file's contents, catching file system errors.
        // On failure, file_get_contents issues an E_WARNING, which
        // we catch in the error handler.
        //
        // Because we've already determined that the file exists and
        // is readable, the kind of file system errors we'd get here
        // are obscure, such as if existence or permissions changed
        // suddenly, a file system became unmounted, a network file
        // system had network problems, etc.
        //
        // Because file system errors cannot be tested for in unit
        // testing, we mark this code to ignore it in code coverage
        // reports.
        // @codeCoverageIgnoreStart
        set_error_handler(
            function( $severity, $message, $file, $line )
            {
                throw new \InvalidArgumentException( $message, $severity );
            }
        );
        // @codeCoverageIgnoreEnd
        try
        {
            $text = file_get_contents( $filename );
        }
            // @codeCoverageIgnoreStart
        catch ( \Exception $e )
        {
            restore_error_handler( );
            throw $e;
        }
        restore_error_handler( );
        // @codeCoverageIgnoreEnd


        // Decode the text.
        return self::decode( $text, $extension );
    }
    // @}
}
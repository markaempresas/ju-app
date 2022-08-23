<?php

/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\AbstractFormat with abstract methods to
 * parse and serialize data.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;

use Drupal\chart_suite\SDSC\StructuredData\AbstractData;
/**
 * @class AbstractFormat
 * AbstractFormat is an abstract base class that defines a framework for
 * data type descriptions, and decode and encode methods that map between
 * a file or stream format and small data container objects, such as
 * tables, trees, and graphs.
 *
 *
 * #### Decode and encode
 * Subclasses must implement methods to decode and encode using the format
 * for a file or string argument:
 * - decode( )
 * - decodeFile( )
 * - encode( )
 * - encodeFile( )
 *
 *
 * #### Data type metadata
 * Metadata describing a file format (a.k.a. "data type") is available
 * in three ways:
 * - get...( ) methods for well-known metadata (e.g. the format name)
 * - getAttributes( ) methods for arbitrary metadata
 * - JSON text conforming to the Research Data Alliance (RDA) specifications
 *
 * The RDA's Data Type Registries Working Group defines a set of metadata
 * that should be defined by all formats (data types) and a JSON schema
 * for this data. The AbstractFormat class returns this text from
 * - getDataTypeRegistration( )
 *
 * AbstractFormat builds this text using values retreived by calling:
 * - getAttributes( )
 * for well-known attribute names (see below).  Subclasses must implement
 * this method.
 *
 * AbstractFormat also provides a few methods to get specific attributes:
 * - getName( )
 * - getLongName( )
 * - getDescription( )
 * - getSyntax( )
 * - getFileExtensions( )
 * - getMIMEType( )
 *
 * These methods call getAttributes( ) with appropriate attribute names.
 *
 *
 * #### Metadata names
 * The following attributes have scalar string values:
 * - "name" - a short name for the format
 * - "longName" - a longer name for the format
 * - "description" - a 1-2 sentence description of the format
 * - "syntax" - a short name for the base syntax
 * - "identifier" - an RDA identifier for the format
 * - "creationDate" - the date the format was created, if known
 * - "lastModificationDate" - the last time the format was modified
 * - "MIMEType" - the MIME type for the format
 *
 * The date fields should be in date-time format
 * (e.g. "2015-10-12T11:58:04.566Z").
 *
 * The following attributes have values that are an array of strings:
 * - "expectedUses" - a list of 1-2 sentences for a few uses
 * - "fileExtensions" - a list of filename extensions (no leading dot)
 *
 * The following attributes have special array values:
 * - "standards" - the standards implemented
 * - "contributors" - the format's list of contributors
 *
 * The standards array is an array of associative arrays, where each
 * associative array has these scalar string values:
 * - "issuer" - one of "DTR", "ISO", "W3C", "ITU", or "RFC"
 * - "name" - the identifier in the above format
 * - "details" - a description of how the standard was used
 * - "natureOfApplicability" - one of "extends", "constrains",
 * "specifies", or "depends"
 *
 * The contributors array is an array of entries that list the contributors
 * to the format's specification. Each entry is an associative array with
 * these keys:
 * - "identifiedUsing" - one of "text", "URL", "ORCID", or "Handle"
 * - "name" - the identifier in the above format
 * - "details" - a description of how that previous version was used
 *
 *
 * #### Research Data Alliance (RDA) equivalents
 * All of the above, except "longName", have equivalents for RDA's
 * data type registries.
 *
 * RDA's "properties", "representationsAndSemantics", and "relationships"
 * attributes are not available here because RDA doesn't have firm
 * definitions yet.
 *
 *
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    1/27/2016
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 *
 * @version 0.0.2  Revised to manage format attributes per RDA.
 */
abstract class AbstractFormat
{
//----------------------------------------------------------------------
// Fields
//----------------------------------------------------------------------
    /**
     * @var  array $attributes
     * An associative array of named format attributes.
     *
     * The table's attributes array may contain additional format-specific
     * attributes.
     */
    protected $attributes;




//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs and returns a new format object that may be used to
     * decode and encode data.
     */
    protected function __construct( )
    {
        $this->attributes = array( );
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
     * Returns the format's relative complexity as a number between
     * 0 (low) and 10 (high).
     *
     * A high complexity format has specific syntactic elements it searches
     * for, and a high ability to reject content that doesn't have
     * those elements.  Formats like HTML and XML are high complexity.
     *
     * A low complexity format is relaxed in its parsing and may accept
     * almost any content as valid and come up with a default object to
     * return.  TEXT is the lowest complexity possible since empty
     * text or text with any characters at all is still valid text.
     * A format like CSV (comma-separated values) is very low complexity
     * since an input with just one non-whitespace character defines
     * a minimal 1-column 1-row table, making any input valid.
     *
     * A format's complexity rating is used by FormatRegistry to
     * prioritize checking high complexity formats before low complexity
     * formats when parsing an unknown input.  The high complexity formats
     * will reject the input if it doesn't match their syntactic rules,
     * leaving the low complexity formats as a fallback.
     */
    public function getComplexity( )
    {
        return 0;
    }

    /**
     * Returns true if the given object can be encoded into this format.
     *
     * @param   object  $object the object to test for encodability
     *
     * @return  boolean returns true if the object can be encoded, and
     * false otherwise.
     */
    public function canEncode( $object = NULL )
    {
        if ( $object == NULL )
            return false;

        if ( $object instanceof Drupal\chart_suite\SDSC\StructuredData\Table )
            return $this->canEncodeTables( );

        if ( $object instanceof Drupal\chart_suite\SDSC\StructuredData\Tree )
            return $this->canEncodeTrees( );

        if ( $object instanceof Drupal\chart_suite\SDSC\StructuredData\Graph )
            return $this->canEncodeGraphs( );

        return false;
    }

    /**
     * Returns true if the format can decode one or more graphs
     * described by an Drupal\chart_suite\SDSC\StructuredData\Graph.
     *
     * @return  boolean  returns true if the format supports decoding
     * one or more graphs.
     */
    public function canDecodeGraphs( )
    {
        return false;
    }

    /**
     * Returns true if the format can encode one or more graphs
     * described by an Drupal\chart_suite\SDSC\StructuredData\Graph.
     *
     * @return  boolean  returns true if the format supports encoding
     * one or more graphs.
     */
    public function canEncodeGraphs( )
    {
        return false;
    }

    /**
     * Returns true if the format can decode one or more tables
     * described by an Drupal\chart_suite\SDSC\StructuredData\Table.
     *
     * @return  boolean  returns true if the format supports decoding
     * one or more tables.
     */
    public function canDecodeTables( )
    {
        return false;
    }

    /**
     * Returns true if the format can encode one or more tables
     * described by an Drupal\chart_suite\SDSC\StructuredData\Table.
     *
     * @return  boolean  returns true if the format supports encoding
     * one or more tables.
     */
    public function canEncodeTables( )
    {
        return false;
    }

    /**
     * Returns true if the format can decode one or more trees
     * described by an Drupal\chart_suite\SDSC\StructuredData\Tree.
     *
     * @return  boolean  returns true if the format supports decoding
     * one or more trees.
     */
    public function canDecodeTrees( )
    {
        return false;
    }

    /**
     * Returns true if the format can encode one or more trees
     * described by an Drupal\chart_suite\SDSC\StructuredData\Tree.
     *
     * @return  boolean  returns true if the format supports encoding
     * one or more trees.
     */
    public function canEncodeTrees( )
    {
        return false;
    }
    // @}
    //----------------------------------------------------------------------
    // Attribute methods
    //----------------------------------------------------------------------
    /**
     * @name Attribute methods
     */
    // @{
    /**
     * Returns a copy of the named attribute for the format.
     *
     * Example:
     * @code
     *   $name = $format->getAttribute( 'name' );
     * @endcode
     *
     * @param   string  $key  the name of an attribute for the format.
     *
     * @return  varies  returns a string, array, or other type of value
     * associated with the named attribute.
     */
    public function getAttribute( $key )
    {
        if ( empty( $key ) )
            return NULL;                            // Request with no name
        if ( !isset( $this->attributes[$key] ) )
            return NULL;                            // No such attribute
        return $this->attributes[(string)$key];
    }

    /**
     * Returns an associative array containing all attributes for
     * the format.
     *
     * Example:
     * @code
     *   $attributes = $format->getAttributes( );
     * @endcode
     *
     * @return  array  returns an associative array containing all attributes
     * for the format.
     */
    public function getAttributes( )
    {
        return $this->attributes;
    }

    /**
     * Returns a "best" format name by checking for, in order, the long name,
     * short name, and syntax name, and returning the first non-empty value
     * found, or an empty string if all of those are empty.
     *
     * Example:
     * @code
     *   $bestName = $data->getBestName( );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * checking each of the long name, name, and syntax attributes
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
        if ( !empty( $this->attributes['syntax'] ) )
            return strval( $this->attributes['syntax'] );
        return '';
    }

    /**
     * Returns the RDA data type registration in JSON syntax based
     * upon the RDA JSON schema.
     *
     * Example:
     * @code
     *   $reg = $format->getDataTypeRegistration( );
     * @endcode
     *
     * @return  string  returns a JSON-formated string describing an
     * object with properties and nested objects and arrays that
     * describes the data type using RDA's JSON schema
     */
    public function getDataTypeRegistration( )
    {
        // Build up an object with the content to output.
        $out = array( );

        // Name (required)
        //  "name": "format-name"
        $out['name'] = 'unnamed';
        if ( isset( $this->attributes['name'] ) &&
            is_scalar( $this->attributes['name'] ) )
            $out['name'] = strval( $this->attributes['name'] );

        // Description (required)
        //  "description": "format-description"
        $out['description'] = '';
        if ( isset( $this->attributes['description'] ) &&
            is_scalar( $this->attributes['description'] ) )
            $out['description'] = strval( $this->attributes['description'] );

        // Expected uses (optional)
        //  "expectedUses": [ "use1", "use2", "use3", ... ]
        if ( isset( $this->attributes['expectedUses'] ) &&
            is_array( $this->attributes['expectedUses'] ) )
        {
            // "expectedUses" must be an array of strings.
            $eu = array( );
            foreach ( $this->attributes['expectedUses'] as &$entry )
            {
                if ( is_scalar( $entry ) )
                    $eu[] = strval( $entry );
            }

            if ( !empty( $eu ) )
                $out['expectedUses'] = $eu;
        }

        // Standards (optional)
        //  "standards": [
        //    {
        //      "name": "standard-name",
        //      "details": "standard-details",
        //      "issuesr": "standard-issuer",
        //      "natureOfApplicability": "standard-applicability"
        //    },
        //    { ... }
        //  ]
        $standards = NULL;
        if ( isset( $this->attributes['standards'] ) &&
            is_array( $this->attributes['standards'] ) )
        {
            // "standards" must be an array of associative arrays.
            // Each associative array provides attributes of a different
            // relevant standard.
            $standards = array( );

            foreach ( $this->attributes['standards'] as &$entry )
            {
                if ( !is_array( $entry ) )
                    continue;       // Bogus entry, skip it

                // Each standards entry is expected to have:
                //   "name" (required)
                //   "issuer" (required + fixed vocabulary)
                //   "details" (optional)
                //   "natureOfApplicability" (optional + fixed vocabulary)
                //
                // All of these must be strings. If they are not, they
                // are converted to strings.

                if ( !isset( $entry['name'] ) ||
                    !is_scalar( $entry['name'] ) )
                    continue;       // No name, skip it

                $e = new \stdClass( );
                $e->name = strval( $entry['name'] );

                if ( isset( $entry['issuer'] ) &&
                    is_scalar( $entry['issuer'] ) )
                    $e->issuer = strval( $entry['issuer'] );

                if ( isset( $entry['details'] ) &&
                    is_scalar( $entry['details'] ) )
                    $e->details = strval( $entry['details'] );

                if ( isset( $entry['natureOfApplicability'] ) &&
                    is_scalar( $entry['natureOfApplicability'] ) )
                    $e->natureOfApplicability = strval( $entry['natureOfApplicability'] );

                $standards[] = $e;
            }

            if ( !empty( $standards ) )
                $out['standards'] = $standards;
        }

        // Provenance (optional)
        $prov = new \stdClass;
        if ( isset( $this->attributes['creationDate'] ) &&
            is_scalar( $this->attributes['creationDate'] ) )
            $prov->creationDate = strval( $this->attributes['creationDate'] );

        if ( isset( $this->attributes['lastModificationDate'] ) &&
            is_scalar( $this->attributes['lastModificationDate'] ) )
            $prov->lastModificationDate = strval( $this->attributes['lastModificationDate'] );

        if ( isset( $this->attributes['contributors'] ) &&
            is_array( $this->attributes['contributors'] ) )
        {
            // "contributors" must be an array of associative arrays.
            // Each associative array provides attributes of a different
            // relevant contributor.
            $contributors = array( );

            foreach ( $this->attributes['contributors'] as &$entry )
            {
                if ( !is_array( $entry ) )
                    continue;       // Bogus entry, skip it

                // Each contributors entry is expected to have:
                //   "name" (required)
                //   "identifiedUsing" (required + fixed vocabulary)
                //   "details" (optional)
                // All of these must be strings.

                if ( !isset( $entry['name'] ) ||
                    !is_scalar( $entry['name'] ) )
                    continue;       // No name, skip it

                $e = new \stdClass;
                $e->name = strval( $entry['name'] );

                if ( isset( $entry['identifiedUsing'] ) &&
                    is_scalar( $entry['identifiedUsing'] ) )
                    $e->identifiedUsing = strval( $entry['identifiedUsing'] );

                if ( isset( $entry['details'] ) &&
                    is_scalar( $entry['details'] ) )
                    $e->details = strval( $entry['details'] );

                $contributors[] = $e;
            }

            if ( !empty( $contributors ) )
                $prov->contributors = $contributors;
        }
        if ( !empty( $prov ) )
            $out['provenance'] = $prov;


        // Generate JSON
        return json_encode( $out, JSON_PRETTY_PRINT );

    }

    /**
     * Returns a string containing a brief description of the format,
     * suitable for display has help information about the format.
     *
     * The string may be several sentences, with punctuation, but
     * without carriage returns or other formatting. The description
     * characterizes the type of data that may be encoded in the format,
     * without discussing specific syntax.
     *
     * Example:
     * @code
     *   $string = $format->getDescription( );
     * @endcode
     *
     * @return  string   returns a string containing a block of text
     * that describes the format in lay terms suitable for use within
     * a user interface, or an empty string if no description is available.
     */
    public function getDescription( )
    {
        if ( !isset( $this->attributes['description'] ) )
            return '';
        return $this->attributes['description'];
    }

    /**
     * Returns an array containing strings for file name extensions
     * commonly associated with the format.
     *
     * Array entries include extensions without a leading ".". All
     * extensions are case insensitive.
     *
     * The returned array is empty if there are no common extensions
     * for the format.
     *
     * Example:
     * @code
     *   $array = $format->getFileExtensions( );
     * @endcode
     *
     * @return  array  returns an array of strings with one entry for
     * each well-known file name extension associated with the format;
     * or an empty array if there are no common file name extensions.
     */
    public function getFileExtensions( )
    {
        if ( !isset( $this->attributes['fileExtensions'] ) )
            return array( );

        // Only return entries that are strings. All of them should be.
        $rfe = array( );
        foreach ( $this->attributes['fileExtensions'] as &$entry )
        {
            if ( is_string( $entry ) )
                $rfe[] = $entry;
        }
        return $rfe;
    }

    /**
     * Returns a string containing a long name for the format,
     * such as a multi-word name that spells out an acryonym.
     *
     * The string may be several words, separated by spaces or
     * punctuation, but without carriage returns or other formatting.
     *
     * Example:
     * @code
     *   $string = $format->getLongName( );
     * @endcode
     *
     * @return  string  returns a string containing a longer multi-word
     * name for the format that often spells out acronyms.
     */
    public function getLongName( )
    {
        if ( !isset( $this->attributes['longName'] ) )
            return NULL;
        return $this->attributes['longName'];
    }

    /**
     * Returns a string containing the MIME type for the format, if any.
     *
     * Example:
     * @code
     *   $string = $format->getMIMEType( );
     * @endcode
     *
     * @return  string   returns a string containing the MIME type,
     * or an empty string if there is none.
     */
    public function getMIMEType( )
    {
        if ( !isset( $this->attributes['MIMEType'] ) )
            return '';
        return $this->attributes['MIMEType'];
    }

    /**
     * Returns a string containing a short name for the format,
     * such as a single word or acronym.
     *
     * The string may be a word or two, separated by spaces or
     * punctuation, but without carriage returns or other formatting.
     *
     * Example:
     * @code
     *   $string = $format->getName( );
     * @endcode
     *
     * @return  string  returns a string containing a short name
     * for the format, such as brief word or two, an abbreviation,
     * or an acryonym.
     */
    public function getName( )
    {
        if ( !isset( $this->attributes['name'] ) )
            return NULL;
        return $this->attributes['name'];
    }

    /**
     * Returns a string containing the base syntax used by the format.
     *
     * The string may be a word or two, separated by spaces or
     * punctuation, but without carriage returns or other formatting.
     *
     * The syntax name is sometimes the same as the name of the format,
     * but it need not be. The "CSV" format for comma-separated values,
     * for instance, has a syntax named "CSV" too. But the "JSON Table"
     * format's syntax is simply "JSON". And an assortment of XML-based
     * formats may have different names, but a syntax of "XML".
     *
     * Example:
     * @code
     *   $string = $format->getSyntax( );
     * @endcode
     *
     * @return  string  returns a string containing the name of the syntax
     * used by the format, such as brief word or two, an abbreviation,
     * or an acryonym.
     */
    public function getSyntax( )
    {
        if ( !isset( $this->attributes['syntax'] ) )
            return NULL;
        return $this->attributes['syntax'];
    }

    /**
     * Returns true if the given file name extension is supported by
     * this format.
     *
     * Example:
     * @code
     *   if ( $format->isFileExtension( 'csv' ) )
     *   {
     *     ...
     *   }
     * @endcode
     *
     * @param   $extension  the file name extension, without a dot
     *
     * @return  boolean     true if the given extension is one supported
     * by this format.
     */
    public function isFileExtension( $extension )
    {
        if ( empty( $extension ) )
            return false;
        if ( !isset( $this->attributes['fileExtensions'] ) )
            return false;

        foreach ( $this->attributes['fileExtensions'] as &$entry )
        {
            if ( $entry === $extension )
                return true;
        }
        return false;
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
     * Parses a text string containing data in the format and returns
     * an array containing one or more data objects.
     *
     * If the text string is empty or does not containing any content
     * recognized by this format, the method returns an empty array.
     * Otherwise, the returned array contains one or more objects
     * built from the parsed text.
     *
     * If parsing encounters an unrecoverable problem, the method
     * throws an exception with a brief message that describes the
     * problem. Typical problems are syntax errors in the format
     * or semantic problems, such as empty column names in a table.
     *
     * Example:
     * @code
     *   $objects = $format->decode( $text );
     * @endcode
     *
     * @param  string  $text   a text string containing data to decode
     * into an array of returned data objects.
     *
     * @return array           returns an array containing objects
     * parsed from the text, or an empty array if no parsable data
     * was found.
     *
     * @throws FormatException  if the text could not be parsed
     * properly due to a variety of format-specific syntax and content
     * errors (see SyntaxException and InvalidContentException).
     */
    abstract public function decode( &$text );

    /**
     * Parses a file containing data in the format and returns an
     * array containing one or more data objects.
     *
     * If the file name is empty, or the file is empty, or the
     * file does not containing any content recognized by this
     * format, the method returns an empty array.
     * Otherwise, the returned array contains one or more objects
     * built from the parsed file contents.
     *
     * If parsing encounters an unrecoverable problem, the method
     * throws an exception with a brief message that describes the
     * problem. Typical problems are syntax errors in the format
     * or semantic problems, such as empty column names in a table.
     *
     * Example:
     * @code
     *   $objects = $format->decodeFile( $filename );
     * @endcode
     *
     * @param  string  $filename  a text string containing the name
     * of a file containing data to decode into an array of returned
     * data objects.
     *
     * @return array           returns an array containing objects
     * parsed from the text, or an empty array if no parsable data
     * was found.
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
    public function decodeFile( $filename )
    {
        // Check if the file is readable.
        if ( is_readable( $filename ) === FALSE )
            throw new FileNotFoundException(
                'File or directory not found or not readable',
                0, 1, $filename );


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


        // Return an empty array if there is no file content.
        if ( $text === false || empty( $text ) )
            return array( );


        // Decode. If there are problems, this will throw a number of
        // exceptions.
        $results = $this->decode( $text );


        // Add file source attribute to every returned object.
        if ( !empty( $results ) )
        {
            $addAttribute = array( 'sourceFileName' => $filename );
            foreach ( $results as &$obj )
            {
                if ( $obj instanceof AbstractData )
                {
                    $obj->setAttributes( $addAttribute );
                }
            }
        }

        return $results;
    }





    /**
     * Encodes one or more data objects into a returned text
     * string in the format.
     *
     * The method's parameter may be a single object or an array
     * of objects to encode. Most formats expect a single object.
     * If multiple objects are passed to a format that can only
     * encode one object, the method throws an exception.
     *
     * Example:
     * @code
     *   $text = $format->encode( $objects );
     * @endcode
     *
     * @param array    $objects  an array of data objects
     * to encode into the returned text string.
     *
     * @param mixed    $options  a set of encoding options used
     * by some formats to select among encoding variants.
     *
     * @throws \InvalidArgumentException   if multiple objects are passed
     * to the format, but the format only supports encoding a
     * single object.
     *
     * @throws \InvalidArgumentException   if the array of objects is NULL,
     * empty, or it contains NULL objects to encode.
     *
     * @throws \InvalidArgumentException   if an object in the array of
     * objects to encode is not compatible with the format.
     */
    abstract public function encode( &$objects, $options = 0 );

    /**
     * Encodes one or more data objects and writes them to
     * a file in the format.
     *
     * The method's parameter may be a single object or an array
     * of objects to encode. Most formats expect a single object.
     * If multiple objects are passed to a format that can only
     * encode one object, the method throws an exception.
     *
     * Example:
     * @code
     *   $format->encodeFile( $filename, $objects );
     * @endcode
     *
     * @param  string  $filename  a text string containing the name
     * of a file to create or overwrite with the encoded data
     * objects.
     *
     * @param array    $objects  an array of data objects
     * to encode into the returned text string.
     *
     * @param mixed    $options  a set of encoding options used
     * by some formats to select among encoding variants.
     *
     * @throws \InvalidArgumentException   if multiple objects are passed
     * to the format, but the format only supports encoding a
     * single object.
     *
     * @throws \InvalidArgumentException   if the array of objects is NULL,
     * empty, or it contains NULL objects to encode.
     *
     * @throws \InvalidArgumentException   if an object in the array of
     * objects to encode is not compatible with the format.
     *
     * @throws FileNotFoundException  if the file could not be opened
     * or written, such as when the file or some directory on the file path
     * does not exist or is not writable.
     *
     * @throws \InvalidArgumentException  if the file could not be written,
     * such as due to permissions problems or system level errors.
     *
     * @throws FormatException  if the array of objects could not be encoded
     * properly for this format.
     *
     */
    public function encodeFile( $filename, &$objects, $options = 0 )
    {
        // If the file already exists, make sure it is writable.
        if ( file_exists( $filename ) === TRUE &&
            is_writable( $filename ) === FALSE )
            throw new FileNotFoundException(
                'File or directory not found or not writable',
                0, 1, $filename );


        // Encode. An encoded object that is an empty string is stil
        // writable to an outut file... it just creates an empty file.
        // This is valid, though perhaps not what the caller intended.
        $text = $this->encode( $objects, $options );


        // Read the file's contents, catching file system errors.
        // On failure, file_get_contents issues an E_WARNING, which
        // we catch in the error handler.
        //
        // Because we've already determined that the file exists and
        // is writable, the kind of file system errors we'd get here
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
            $status = file_put_contents( $filename, $text );
        }
            // @codeCoverageIgnoreStart
        catch ( \Exception $e )
        {
            restore_error_handler( );
            throw $e;
        }
        restore_error_handler( );
        // @codeCoverageIgnoreEnd
    }
    // @}
}
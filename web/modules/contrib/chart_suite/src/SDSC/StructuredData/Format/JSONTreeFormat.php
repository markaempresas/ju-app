<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\JSONTreeFormat to parse and
 * serialize data in the JSON (JavaScript Object Notation) text syntax
 * for trees.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;


use Drupal\chart_suite\SDSC\StructuredData\Tree;





/**
 * @class JSONTreeFormat
 * JSONTreeFormat provides decode and encode functions that map
 * between JSON (JavaScript Object Notation) text and a
 * Drupal\chart_suite\SDSC\StructuredData\Tree.
 *
 * JSON is a general-purpose syntax for describing objects, arrays,
 * scalars, and arrays of objects, arrays, of scalars to an arbitrary
 * nesting depth. This class, however, focuses on a narrower subset of
 * JSON usage in order to build trees.
 *
 *
 * #### Tree syntax
 * A JSON tree is a hierarchy of nodes starting with a root node
 * with a name and list of children. Each of those children nodes
 * has a name and their own list of children, and so on to arbitrary
 * depth. Any node can have any number of named attributes with
 * arbitrary values.
 *
 *
 * ##### Tree object
 * JSON trees always start as an object. The object is expected to
 * have a "name" property and a "children" property, but both of
 * these are optional.  The name may be a scalar string or an array
 * with at least one scalar string value. Non-string values are
 * silently converted to strings.
 * <pre>
 *  {
 *      "name": "something",
 *      "children": [ ... ]
 *  }
 * </pre>
 * or
 * <pre>
 *  {
 *      "name": [ "something" ],
 *      "children": [ ... ]
 *  }
 * </pre>
 *
 * Each item in the "children" array is another node object with an
 * optional "name" property and a "children" property with another
 * nested array of node objects, and so on.
 *
 *
 * ##### Parent object
 * JSON trees can be included in a parent object within a "tree"
 * property (see ENCODE_AS_OBJECT):
 * <pre>
 *  {
 *      "tree": [ ... ]
 *  }
 * </pre>
 *
 *
 * ##### Tree names
 * JSON trees within a parent object may have additional properties
 * that give the tree's short name (name), long name (title), and
 * description.  The name, title, and description property values may
 * be a scalar string or an array with at least one scalar string value.
 * Non-string values are silently converted to strings.
 * <pre>
 *  {
 *      "name":  [ "tbl" ],
 *      "title": [ "Big tree" ],
 *      "description": [ "A big tree with lots of data" ],
 *      "tree":  [ ... ]
 *  }
 * </pre>
 *
 *
 * ##### Tree schema name
 * JSON trees can have a microformat schema name that refers to
 * a well-known schema by setting the "type" property of the parent
 * object.  The type property value may be an array or a scalar with a
 * single string value.
 * <pre>
 *  {
 *      "type": [ "json-tree" ],
 *      "tree": [ ... ]
 *  }
 * </pre>
 *
 *
 * #### Tree decode limitations
 * The amount of tree and node descriptive information available
 * in a JSON file depends upon how much of syntax above is used.
 * While trees and nodes should have names, for instance, these are
 * optional. Descriptions and other metadata are also optional.
 *
 *
 * #### Tree encode limitations
 * The encoder can output trees in several JSON syntax forms.
 *
 *
 * @see     Drupal\chart_suite\SDSC\StructuredData\Tree    the StructuredData Tree class
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
final class JSONTreeFormat
    extends AbstractFormat
{
//----------------------------------------------------------------------
// Constants
//----------------------------------------------------------------------
    /**
     * An encoding style that generates a single object that starts
     * immediately with the root node. This is the most basic form
     * of tree output and omits a tree name and other tree metadata.
     * Node names, long names, descriptions, and other metadata are
     * included.
     *
     * <pre>
     * {
     *   "name": "node short name",
     *   "title": "node long name"
     *   "description": "node description",
     *   "children": [ ... ]
     * }
     * </pre>
     */
    const ENCODE_AS_OBJECT = 1;

    /**
     * An encoding style identical to ENCODE_AS_OBJECT, but with
     * a parent object that includes the tree's metadata and schema.
     *
     * This is the default encoding.
     *
     * <pre>
     * {
     *   "name": "tree short name",
     *   "title": "tree long name"
     *   "description": "tree description",
     *   "type": "tree source schema name",
     *   "tree": [
     *     "name": "node short name",
     *     "title": "node long name"
     *     "description": "node description",
     *     "children": [ ... ]
     *   ]
     * }
     * </pre>
     */
    const ENCODE_AS_OBJECT_WITH_SCHEMA = 2;





//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs and returns a new format object that may be used to
     * decode and encode trees in JSON (JavaScript Object Notation).
     */
    public function __construct( )
    {
        parent::__construct( );

        $this->attributes['syntax']         = 'JSON';
        $this->attributes['name']           = 'json-tree';
        $this->attributes['longName']       = 'JavaScript Object Notation (JSON) Tree';
        $this->attributes['MIMEType']       = 'application/json';
        $this->attributes['fileExtensions'] = array( 'json' );
        $this->attributes['description'] =
            'The JSON (JavaScript Object Notation) format encodes ' .
            'a variety of data, including tables, trees, and graphs. '.
            'Tree data may have an unlimited number of nodes arranged ' .
            'in a hierarchy starting with a root node. Each node may have ' .
            'children, and those may have children. Every node may have a ' .
            'a short name, long name, and description, and any number and ' .
            'type of named values.';
        $this->attributes['expectedUses'] = array(
            'Trees with parent and child names with names and values'
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
                'name' => 'JSON Tree',
                'natureOfApplicability' => 'specifies',
                'details' => ''
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
     * @copydoc AbstractFormat::canDecodeTrees
     */
    public function canDecodeTrees( )
    {
        return true;
    }

    /**
     * @copydoc AbstractFormat::canEncodeTrees
     */
    public function canEncodeTrees( )
    {
        return true;
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
     * @copydoc AbstractFormat::decode
     *
     * #### Decode limitations
     * The JSON format always returns an array containing a single
     * Drupal\chart_suite\SDSC\StructuredData\Tree object.
     */
    public function decode( &$text )
    {
        // Parse JSON
        // -----------------------------------------------------
        //   Parse JSON text.
        if ( empty( $text ) )
            return array( );        // No tree

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
        // We could have a tree.


        // Determine content type
        // -----------------------------------------------------
        // If the content is an object, look for a few
        // tell-tale properties to see what we have.
        if ( is_object( $content ) )
        {
            // Possabilities:
            //  'children' - definitely a tree
            //  'tree'     - definitely a tree
            //
            // Reject anything that isn't a tree.
            if ( property_exists( $content, 'tree' ) )
            {
                // When there's a 'tree' property, we have a top-level
                // object that is a tree and it may have a schema.
                return $this->_decodeTreeObjectWithSchema( $content );
            }
            if ( property_exists( $content, 'children' ) )
            {
                // When there's a 'children' property, we have a top-level
                // object that is the root node and there is no schema.
                return $this->_decodeTreeObject( $content );
            }
        }

        // Otherwise we don't know what it is.
        throw new SyntaxException(
            'Unrecognized JSON content. Does not appear to be a tree.' );
    }

    /**
     * Decodes a tree object in the JSON Tree format used by the d3
     * visualization library with a header and schema giving
     * tree attributes.
     *
     * @param array $content  the content
     *
     * @throws InvalidContentException if the content cannot be parsed
     */
    private function _decodeTreeObjectWithSchema( &$content )
    {
        // Check the type
        // -----------------------------------------------------
        // The format has a schema type and a tree.  The schema
        // type must be recognized.
        //
        // Good example:
        //  {
        //      "type":  [ "json-tree" ],
        //      "name":  [ "my tree" ],
        //      "title": [ "Big tree" ],
        //      "description": [ "A big tree with lots of data" ],
        //      "tree":  { ... }
        //  }

        if ( property_exists( $content, 'type' ) )
        {
            $type = $content->type;
            if ( !is_scalar( $type ) || (string)$type != 'json-tree' )
                throw new InvalidContentException(
                    'JSON tree "type" must be "json-tree".' );
        }


        // Parse attributes
        // -----------------------------------------------------
        // Get all of a tree's top-level attributes. Confirm usage
        // for well-known attributes.
        $attributes = $this->_decodeAttributes( $content );

        // Add standard attributes, overriding anything in the input.
        $attributes['sourceMIMEType']   = $this->getMIMEType( );
        $attributes['sourceSyntax']     = $this->getSyntax( );
        $attributes['sourceSchemaName'] = 'json-tree';



        // Create tree
        // -----------------------------------------------------
        // Create the empty tree with the attributes, then find
        // and parse the root and all of its children.
        $tree = new Tree( $attributes );

        if ( property_exists( $content, 'tree' ) )
        {
            $root = $content->tree;
            if ( !is_object( $root ) )
                throw new InvalidContentException(
                    'JSON "tree" property must be an object for the tree root.' );
            $this->_decodeTreeRoot( $tree, $root );
        }

        return array( $tree );
    }

    /**
     * Decodes a tree object without a schema.
     *
     * @param array $content  the content
     *
     * @throws InvalidContentException if the content cannot be parsed
     */
    private function _decodeTreeObject( &$content )
    {
        // Create tree
        // -----------------------------------------------------
        // The format does not support a tree name or description
        // and starts immediately with the root node.
        //
        // Good example:
        //  {
        //      "name":  "root",
        //      "children":  [ ... ]
        //  }
        $attributes = array(
            // 'name' unknown
            // 'longName' unknown
            // 'description' unknown
            // 'sourceFileName' unknown
            'sourceMIMEType'   => $this->getMIMEType( ),
            'sourceSyntax'     => $this->getSyntax( ),
            'sourceSchemaName' => 'json-tree'
        );
        $tree = new Tree( $attributes );


        // Parse root and children
        // -----------------------------------------------------
        if ( !empty( $content ) )
            $this->_decodeTreeRoot( $tree, $content );

        return array( $tree );
    }

    /**
     * Decodes a tree node.
     *
     * @param Tree  $tree     the empty tree
     *
     * @param array $content  the content
     *
     * @throws InvalidContentException if the content cannot be parsed
     */
    private function _decodeTreeRoot( &$tree, &$content )
    {
        // Parse attributes
        // -----------------------------------------------------
        // The format has a node name, description, etc.
        //
        // Good example:
        //  {
        //      "name":        [ "root" ],
        //      "title":       [ "Root-er-iffic" ],
        //      "description": [ "A big root" ],
        //      "children":    [ ... ]
        //  }

        // Get all of a node's attributes. Confirm usage
        // for well-known attributes.
        $attributes = $this->_decodeAttributes( $content );


        // Create root
        // -----------------------------------------------------
        $rootNodeId = $tree->setRootNode( $attributes );


        // Create children
        // -----------------------------------------------------
        if ( property_exists( $content, 'children' ) )
        {
            $children = $content->children;
            if ( !is_array( $children ) )
                throw new InvalidContentException(
                    'JSON "children" property must be an array of child nodes.' );

            $this->_recursivelyDecodeTreeChildren( $tree,
                $rootNodeId, $children );
        }
    }

    /**
     * Recursively decodes the given array of children objects,
     * adding them as children to the selected parent node.
     *
     * @param Tree    $tree          the tree to add further nodes to.
     *
     * @param integer $parentNodeId  the unique positive numeric ID of
     * the parent node.
     *
     * @param array $childrenToAdd  an array of children objects to add to
     * the parent node.
     *
     * @throws InvalidContentException  if any 'children' property is
     * not an array of objects.
     *
     * @throws InvalidContentException  if any 'name' property is not
     * a scalar string.
     */
    private function _recursivelyDecodeTreeChildren( &$tree,
                                                     $parentNodeId, &$childrenToAdd )
    {
        foreach ( $childrenToAdd as &$child )
        {
            // Parse attributes
            // -------------------------------------------------
            // The format has a node name, description, etc.
            //
            // Good example:
            //  {
            //      "name":        [ "root" ],
            //      "title":       [ "Root-er-iffic" ],
            //      "description": [ "A big root" ],
            //      "children":    [ ... ]
            //  }

            // Get all of a node's attributes. Confirm usage
            // for well-known attributes.
            $attributes = $this->_decodeAttributes( $child );


            // Add child
            // -------------------------------------------------
            $childID = $tree->addNode( $parentNodeId, $attributes );


            // Create children
            // -------------------------------------------------
            if ( property_exists( $child, 'children' ) )
            {
                $children = $child->children;
                if ( !is_array( $children ) )
                    throw new InvalidContentException(
                        'JSON "children" property must be an array of child nodes.' );

                $this->_recursivelyDecodeTreeChildren( $tree,
                    $childID, $children );
            }
        }
    }

    /**
     * Decodes attributes for a tree or node and returns an associative
     * array containing those attributes.
     *
     * @param array $content  the content.
     *
     * @return array  the associative array of decoded attributes.
     *
     * @throws InvalidContentException if the content cannot be parsed.
     */
    private function _decodeAttributes( &$content )
    {
        // Create attributes
        // -----------------------------------------------------
        // The format supports "name", "title", and "description"
        // well-known attributes. Additional attributes may be
        // added by the user.
        //
        // Good example:
        //  {
        //      "name":        "Node123",
        //      "title":       "Cool node",
        //      "description": "This is a cool node",
        //      "whatever":    "something"
        //  }
        //
        // Well-known attribute values should be scalar strings,
        // but we'll accept an array with at least one entry and
        // use the first entry as the value.

        // Convert the object to an attributes array that initially
        // contains all properties. We'll type check and clean things
        // out below.
        $attributes = get_object_vars( $content );

        // Get rid of attributes we handle separately.
        if ( isset( $attributes['tree'] ) )
            unset( $attributes['tree'] );

        if ( isset( $attributes['children'] ) )
            unset( $attributes['children'] );


        // Name, Title, and Description
        // -----------------------------------------------------
        // If these exists, make sure they are a string.  Rename
        // attributes to use our internal attribute names.
        if ( isset( $attributes['name'] ) )
        {
            $value = $attributes['name'];
            if ( is_array( $value ) && count( $value ) > 0 )
                $attributes['name'] = (string)$value[0];
            else if ( is_scalar( $value ) )
                $attributes['name'] = (string)$value;
            else
                throw new InvalidContentException(
                    'JSON tree "name" property must be a scalar string.' );
        }
        if ( isset( $attributes['title'] ) )
        {
            // Rename 'title' to 'longName'
            $value = $attributes['title'];
            unset( $attributes['title'] );
            if ( is_array( $value ) && count( $value ) > 0 )
                $attributes['longName'] = (string)$value[0];
            else if ( is_scalar( $value ) )
                $attributes['longName'] = (string)$value;
            else
                throw new InvalidContentException(
                    'JSON tree "title" property must be a scalar string.' );
        }
        if ( isset( $attributes['description'] ) )
        {
            $value = $attributes['description'];
            if ( is_array( $value ) && count( $value ) > 0 )
                $attributes['description'] = (string)$value[0];
            else if ( is_scalar( $value ) )
                $attributes['description'] = (string)$value;
            else
                throw new InvalidContentException(
                    'JSON tree "description" property must be a scalar string.' );
        }

        return $attributes;
    }





//----------------------------------------------------------------------
    // Encode methods
    //----------------------------------------------------------------------
    /**
     * @name Encode methods
     */
    // @{
    /**
     * @copydoc AbstractFormat::encode
     *
     * #### Encode limitations
     * The JSON format only supports encoding a single
     * Drupal\chart_suite\SDSC\StructuredData\Tree to the format.
     */
    public function encode( &$objects, $options = 0 )
    {
        //
        // Validate arguments
        // -----------------------------------------------------
        if ( empty( $objects ) )
            return NULL;            // No tree to encode
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
        if ( is_a( $object, 'Drupal\chart_suite\SDSC\StructuredData\Tree', false ) )
            return $this->_encodeTree( $object, $options );
        else
            throw new \InvalidArgumentException(
                'JSON encode object must be a tree.' );
    }

    /**
     * Encodes the given tree as JSON text, controlled by the given
     * options.
     *
     * @param  Tree   $tree  the tree object to be encoded.
     *
     * @param integer  $options  encoding options to control how
     * JSON text is generated.
     *
     * @return  string        the JSON text that encodes the tree.
     */
    private function _encodeTree( &$tree, $options )
    {
        if ( $tree->getNumberOfNodes( ) == 0 )
            return '';              // Empty tree

        if ( $options == self::ENCODE_AS_OBJECT )
            return $this->_encodeAsObject( $tree );

        // Otherwise ENCODE_AS_OBJECT_WITH_SCHEMA (default)
        return $this->_encodeAsObjectWithSchema( $tree );
    }


    /**
     * Encodes the given tree as an object, starting immediately with
     * the root node and without including any tree attributes.
     *
     * @param  Tree    $tree    the tree object to be encoded.
     *
     * @return  string          the JSON text that encodes the tree.
     */
    private function _encodeAsObject( &$tree )
    {
        return $this->_recursivelyEncodeTree( $tree, '',
            $tree->getRootNodeID( ) );
        $text .= "\n";
        return $text;
    }

    /**
     * Encodes the given tree as an object, starting with a header
     * that includes the tree's attributes, followed by a "tree"
     * property that includes the root node and all of its children.
     *
     * @param  Tree    $tree    the tree object to be encoded.
     *
     * @return  string          the JSON text that encodes the tree.
     */
    private function _encodeAsObjectWithSchema( &$tree )
    {
        $attributes = $tree->getAttributes( );

        $name = NULL;
        if ( isset( $attributes['name'] ) )
            $name = $attributes['name'];

        $title = NULL;
        if ( isset( $attributes['longName'] ) )
            $title = $attributes['longName'];

        $description = NULL;
        if ( isset( $attributes['description'] ) )
            $description = $attributes['description'];

        $type = NULL;
        if ( isset( $attributes['sourceSchemaName'] ) )
            $type = $attributes['sourceSchemaName'];

        $indent = '  ';
        $text   = "{\n";

        // Header
        if ( !empty( $name ) )
            $text .= $indent . '"name": "' . $name . '",' . "\n";
        if ( !empty( $title ) )
            $text .= $indent . '"title": "' . $title . '",' . "\n";
        if ( !empty( $description ) )
            $text .= $indent . '"description": "' . $description . '",' . "\n";
        if ( !empty( $type ) )
            $text .= $indent . '"type": "' . $type . '",' . "\n";

        // Tree
        $text .= $indent . '"tree":';
        $text .= $this->_recursivelyEncodeTree( $tree, $indent,
            $tree->getRootNodeID( ) );
        $text .= "\n}\n";
        return $text;
    }

    /**
     * Recursively encodes the given tree, starting at the selected node,
     * and indenting each line with the given string.
     *
     * @param  Tree    $tree    the tree object to be encoded.
     *
     * @param  string  $indent  the text string to prepend to every line
     * of encoded text.
     *
     * @param  integer $nodeId  the unique positive numeric ID of the tree
     * node to encode, along with all of its children.
     */
    private function _recursivelyEncodeTree( &$tree, $indent, $nodeId )
    {
        // Add all attributes.
        $text = $indent . "{\n";
        $endofline = '';
        foreach ( $tree->getNodeAttributes( $nodeId ) as $key => $value )
        {
            $text .= $endofline;

            if ( is_int( $value ) || is_float( $value ) || is_bool( $value ) )
                $text .= "$indent  \"$key\": $value";

            else if ( is_null( $value ) )
                $text .= "$indent  \"$key\": null";

            else if ( is_string( $value ) )
                $text .= "$indent  \"$key\": \"$value\"";

            else if ( is_object( $value ) || is_array( $value ) )
            {
                // Don't know what this is, so encode it blind.
                $text .= "$indent  \"$key\": " . json_encode( $value );
            }

            $endofline = ",\n";
        }

        // Add children.
        $children = $tree->getNodeChildren( $nodeId );
        if ( !empty( $children ) )
        {
            $text .= $endofline;
            $text .= "$indent  \"children\": [\n";
            $indent2 = $indent . '    ';
            for ( $i = 0; $i < count( $children ); $i++ )
            {
                if ( $i != 0 )
                    $text .= ",\n";
                $text .= $this->_recursivelyEncodeTree(
                    $tree, $indent2, $children[$i] );
            }
            $text .= "\n$indent  ]\n";
        }
        else if ( $nodeId == $tree->getRootNodeID( ) )
        {
            // Include an empty 'children' array for the root node
            // because it helps identify the text as in the tree format.
            $text .= $endofline;
            $text .= "$indent  \"children\": [ ]\n";
        }
        else
            $text .= "\n";
        $text .= $indent . '}';

        return $text;
    }
    // @}
}



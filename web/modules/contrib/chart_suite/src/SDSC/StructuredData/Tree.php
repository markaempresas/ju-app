<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Tree to manage a tree with a root node and
 * children that may, in turn, have children.
 */

namespace Drupal\chart_suite\SDSC\StructuredData;


/**
 * @class Tree
 * Tree manages a named hierarchy of named nodes that each contain a
 * list of named values and metadata.
 *
 * #### Tree attributes
 * Trees have an associative array of attributes that
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
 * 'json-tree').
 *
 *
 * #### Node attributes
 * Trees have zero or more nodes. Each node has an associative array
 * of attributes that provide descriptive metadata for the node. Applications
 * may add any number and type of attributes, but this class, and others in
 * this package, recognize a few well-known attributes:
 *
 *  - 'name' (string) is a brief name of the data
 *  - 'longName' (string) is a longer more human-friendly name of the data
 *  - 'description' (string) is a block of text describing the data
 *
 * All attributes are optional.
 *
 * The 'name' may be an abbreviation or acronym, while the 'longName' may
 * spell out the abbreviation or acronym.  The node 'name' is optional
 * but strongly encouraged.  If abscent, classes that format nodes for a
 * specific output syntax (e.g. CSV or JSON) will create numbered node
 * names (e.g. '1', '2', etc.).
 *
 * The 'description' may be a block of text containing several unformatted
 * sentences describing the data.
 *
 *
 * #### Nodes
 * A tree may have zero or more nodes with values. Values have names and
 * any data type.  Value names must be strings.
 *
 * The tree starts with a root node that has an arbitrary number of
 * child nodes. Each of those children may have an arbitrary number
 * of further child nodes, and so on recursively.
 *
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    2/8/2016
 *
 * @since   0.0.1  Initial development.
 *
 * @version 0.0.1  Initial development.
 *
 * @version 0.0.2  Revised to generalize tree and node attributes into
 *   associative arrays instead of explicit attributes.
 *
 * @version 0.0.3  Revised to make the node array, children arrays, and
 *   the name map's entries all associative arrays where keys are node IDs.
 *
 * @version 0.0.4  Revised to subclass AbstractData and throw standard
 *   SPL exceptions.
 *
 * @version 0.0.5  Revised to move node values (using attributes instead),
 *   and to move them into a sub-array so that user keys cannot collide
 *   with internal keys for parents and children.
 */
final class Tree
    extends AbstractData
{
//----------------------------------------------------------------------
// Fields
//----------------------------------------------------------------------
    /**
     * @var  object $rootID
     * The root node id. The id is -1 if there is no root.
     */
    private $rootID;



    /**
     * @var  array $nodes
     * An array of nodes with numeric node ID keys and associative
     * array values.  The order of nodes is irrelevant.  Deletion
     * of a node unsets the array entry, causing array keys to *not*
     * be consecutive integers.
     *
     * Validation of a node ID checks if the ID is a valid key for
     * the array.
     *
     * The number of nodes equals count( $nodes ).
     *
     * Each node in the array is an associative array with keys for:
     *      - 'attributes'  - associative array of named attributes
     *      - 'children'    - array of children node IDs as keys
     *      - 'parent'      - parent's node ID
     *
     * The 'attributes' key selects an associative array containing
     * named attributes/values. Well-known attributes include:
     *
     *      - 'name'        - short name
     *      - 'longName'    - long name
     *      - 'description' - description
     *
     * The 'parent' key selects a scalar value that always exists and
     * is initialized to -1 for the root node, and an integer node ID
     * for all other nodes.
     *
     * The 'children' key selects an associative array that always
     * exists and is initially empty.  This array is associative
     * where keys are node IDs for the children, and values are always
     * 0 (they are not used).
     */
    private $nodes;

    /**
     * @var  array $nodeNameMap
     * An associative array with node name string keys. An entry
     * exists if a particular name is used by one or more nodes.
     *
     * Each entry is an associative array where array keys are numeric
     * node IDs, and values are always '0' - the value is not used and
     * is merely there to fill an entry. The array keys are what are
     * used to provide a list of node IDs with the same name.
     */
    private $nodeNameMap;

    /**
     * @var  integer $nextNodeID
     * The next available unique non-negative integer node ID.
     * Node IDs start at 0 for an empty tree, then increment each
     * time a node is added. On deletion, the IDs of deleted nodes
     * are *not* reused. Node IDs are monotonicaly increasing.
     */
    private $nextNodeID;





//----------------------------------------------------------------------
// Constants
//----------------------------------------------------------------------
    /**
     * @var array WELL_KNOWN_NODE_ATTRIBUTES
     * An associative array where the keys are the names of well-known
     * node attributes.
     */
    public static $WELL_KNOWN_NODE_ATTRIBUTES = array(
        'name'        => 1,
        'longName'    => 1,
        'description' => 1
    );

    private static $ERROR_tree_node_id_invalid =
        'Tree node ID is out of bounds.';
    private static $ERROR_node_attributes_invalid =
        'Node attributes must be an array or object.';
    private static $ERROR_node_values_invalid =
        'Node values must be an array or object.';

    private static $ERROR_node_attribute_key_invalid =
        'Node attribute keys must be non-empty strings.';
    private static $ERROR_node_attribute_wellknown_key_value_invalid =
        'Node attribute values for well-known keys must be strings.';





//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs an empty tree with no nodes and the provided
     * list of attributes, if any.
     *
     *
     * @param   array $attributes  an optional associatve array of named
     * attributes associated with the tree
     *
     * @return  Tree             returns a new empty tree with the
     * provided tree attributes
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function __construct( $attributes = NULL )
    {
        parent::__construct( $attributes );

        // Initialize empty node arrays.
        $this->nodes       = array( );
        $this->nodeNameMap = array( );
        $this->rootID      = -1;
        $this->nextNodeID  = 0;
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
     * Destroys the previously constructed tree.
     */
    public function __destruct( )
    {
        parent::__destruct( );
    }
    // @}
    //----------------------------------------------------------------------
    // Utility methods
    //----------------------------------------------------------------------
    /**
     * @name Utility methods
     */
    // @{
    /**
     * Adds the selected node's ID to the name table with the given name.
     *
     * The given node ID is assumed to be valid.
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     *
     * @param string   $name    a string containing the name name
     * of the node, or an empty string if there is no name name.
     */
    private function _addNodeToNameMap( $nodeID, $name )
    {
        // The $nodeNameMap is an associative array where names are
        // the keys. Entry values are associative arrays where the
        // keys are node IDs, and the values are irrelevant.
        if ( $name === '' || $name === NULL )
            return;

        // If the map has no current entry for the name, add one.
        // Otherwise, add a key for the new node ID. Values are
        // not used and are always 0.
        if ( !isset( $this->nodeNameMap[$name] ) )
            $this->nodeNameMap[$name]          = array( $nodeID => 0 );
        else
            $this->nodeNameMap[$name][$nodeID] = 0;
    }

    /**
     * Removes the selected node's ID from the name table entry with
     * the given name.
     *
     * The given node ID is assumed to be valid.
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     *
     * @param string   $name    a string containing the name
     * of the node, or an empty string if there is no name.
     */
    private function _deleteNodeFromNameMap( $nodeID, $name )
    {
        // The $nodeNameMap is an associative array where names are
        // the keys. Entry values are associative arrays where the
        // keys are node IDs, and the values are irrelevant.
        if ( $name === '' || $name === NULL )
            return;

        // If the map has no entry for the name, then the name was not
        // in use and we're done.  This should never happen since all
        // nodes with names are added to the name map.
        //
        // Since this should never happen, there is no way to test this.
        // @codeCoverageIgnoreStart
        if ( !isset( $this->nodeNameMap[$name] ) )
            return;                         // Name is not in use
        // @codeCoverageIgnoreEnd

        // If the map entry has no key for the node, then the node was
        // not registered as using this name and we're done.  Again,
        // this should never happen since all entries have nodes.
        //
        // Since this should never happen, there is no way to test this.
        // @codeCoverageIgnoreStart
        if ( !isset( $this->nodeNameMap[$name][$nodeID] ) )
            return;                         // Node isn't registered for name
        // @codeCoverageIgnoreEnd

        // Unset the map entry's key for the node.
        unset( $this->nodeNameMap[$name][$nodeID] );

        // If that makes the map entry empty, unset it.
        if ( empty( $this->nodeNameMap[$name] ) )
            unset( $this->nodeNameMap[$name] );
    }

    /**
     * Recursively deletes the selected node and all of its children.
     *
     * The given node ID, and all of the children node IDs, are assumed
     * to be valid.
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     */
    private function _recursiveDeleteNode( $nodeID )
    {
        // Save the node's name and list of children.
        $name = NULL;
        if ( isset( $this->nodes[$nodeID]['attributes']['name'] ) )
            $name = $this->nodes[$nodeID]['attributes']['name'];

        $children = array( );
        if ( isset( $this->nodes[$nodeID]['children'] ) )
            $children = $this->nodes[$nodeID]['children'];

        // Delete the node from the node table.
        unset( $this->nodes[$nodeID] );

        // Delete the node from the associative array of node names.
        $this->_deleteNodeFromNameMap( $nodeID, $name );

        // Recurse to delete all of the node's children.  Keys for the
        // children array are node IDs, while values are not used.
        foreach ( $children as $childID => $unusedValue )
            $this->_recursiveDeleteNode( $childID );
    }

    /**
     * Validates a nodeID and throws an exception if the ID is out of
     * range.
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    private function _validateNodeID( $nodeID )
    {
        // The $nodes array is an associative array where node IDs are
        // the keys. IDs are always non-negative. If an ID is negative
        // or if there is no entry for the ID, then the ID is not valid.
        if ( $nodeID < 0 || !isset( $this->nodes[$nodeID] ) )
            throw new \OutOfBoundsException(
                self::$ERROR_tree_node_id_invalid );
    }
    // @}
    //----------------------------------------------------------------------
    // Node attributes methods
    //----------------------------------------------------------------------
    /**
     * @name Node attributes methods
     */
    // @{
    /**
     * Clears attributes for the selected node, while retaining its
     * links to its parent and children, if any.
     *
     * Example:
     * @code
     *   $tree->clearNodeAttributes( $id );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function clearNodeAttributes( $nodeID )
    {
        // Validate the node ID.
        $this->_validateNodeID( $nodeID );

        // Clear attributes. If there is a node name, remove the node
        // from the name map.
        if ( !isset( $this->nodes[$nodeID]['attributes']['name'] ) )
            $this->nodes[$nodeID]['attributes'] = array( );
        else
        {
            $name = $this->nodes[$nodeID]['attributes']['name'];
            $this->nodes[$nodeID]['attributes'] = array( );
            $this->_deleteNodeFromNameMap( $nodeID, $name );
        }
    }

    /**
     * Returns an array of node IDs for nodes with the selected
     * name, or an empty array if there are no nodes with the name.
     *
     * Example:
     * @code
     *   $ids = $tree->findNodesByName( 'abc' );
     *   foreach ( $ids as $id )
     *   {
     *     print( "Node $id\n" );
     *   }
     * @endcode
     *
     * @return  array  returns an array of node IDs for nodes with
     * the given name, or an empty array if no nodes were found.
     *
     * @throws \InvalidArgumentException  if $name is not a non-empty string.
     */
    public function findNodesByName( $name )
    {
        // Validate.
        if ( !is_string( $name ) || $name === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attribute_key_invalid );

        // The name map is an associative array where the keys are names
        // and the values are arrays. Those arrays are each associative
        // where the keys are node IDs and the values are unused.

        // If the map has no entry for the name, there are no nodes with
        // that name.
        if ( !isset( $this->nodeNameMap[$name] ) )
            return array( );

        // Otherwise return the keys for that name's array. These are
        // node IDs.
        return array_keys( $this->nodeNameMap[$name] );
    }

    /**
     * Returns a copy of the selected attribute for the selected node,
     * or a NULL if the attribute does not exist.
     *
     * Example:
     * @code
     *   $tree->getNodeAttribute( $id, 'name' );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @param  string  $key     the name of an attribute to query
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     *
     * @throws \InvalidArgumentException  if $key is not a non-empty string.
     */
    public function getNodeAttribute( $nodeID, $key )
    {
        // Validate the node ID.
        $this->_validateNodeID( $nodeID );
        if ( !is_string( $key ) || $key === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attribute_key_invalid );

        // Get.
        if ( isset( $this->nodes[$nodeID]['attributes'][$key] ) )
            return $this->nodes[$nodeID]['attributes'][$key];
        return NULL;                        // No such key
    }

    /**
     * Returns a copy of all attributes for the selected node.
     *
     * Example:
     * @code
     *   $tree->getNodeAttributes( $id );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeAttributes( $nodeID )
    {
        // Validate the node ID.
        $this->_validateNodeID( $nodeID );

        // Get.
        return $this->nodes[$nodeID]['attributes'];
    }

    /**
     * Returns a "best" node name by checking for, in order, the long name
     * and short name, and returning the first non-empty value
     * found, or the node id if all of those are empty.
     *
     * Example:
     * @code
     *   $bestName = $data->getNodeBestName( $id );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * checking each of the long name and name attributes in order.
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @return  the best name
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeBestName( $nodeID )
    {
        $v = $this->getNodeAttribute( $nodeID, 'longName' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        $v = $this->getNodeAttribute( $nodeID, 'name' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return strval( $nodeID );
    }

    /**
     * Returns the description of the selected node, or an empty string if it
     * has no description.
     *
     * Example:
     * @code
     *   $description = $tree->getNodeDescription( $id );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @return string  the description for the selected node, or an empty
     * string if the node has no description.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeDescription( $nodeID )
    {
        $v = $this->getNodeAttribute( $nodeID, 'description' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns the long name of the selected node, or an empty string if it
     * has no long name.
     *
     * Example:
     * @code
     *   $longName = $tree->getNodeLongName( $id );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @return string  the long name for the selected node, or an empty
     * string if the node has no long name.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeLongName( $nodeID )
    {
        $v = $this->getNodeAttribute( $nodeID, 'longName' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns the name of the selected node, or an empty string if it
     * has no name.
     *
     * Example:
     * @code
     *   $name = $tree->getNodeName( $id );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @return string  the name for the selected node, or an empty string if
     * the node has no name.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeName( $nodeID )
    {
        $v = $this->getNodeAttribute( $nodeID, 'name' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns an array of keywords found in the node's attributes,
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
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @return array  returns an array of keywords.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeKeywords( $nodeID )
    {
        // Add all node attribute keys and values for one node.
        $text = '';
        foreach ( $this->nodes[$nodeID]['attributes'] as $key => &$value )
        {
            // Add the key. Skip well-known key names.  Intelligently
            // convert to text.
            if ( !isset( self::$WELL_KNOWN_NODE_ATTRIBUTES[$key] ) )
                $text .= ' ' . $this->valueToText( $key );

            // Add the value.  Intelligently convert to text.
            $text .= ' ' . $this->valueToText( $value );
        }

        // Clean the text of numbers and punctuation, and return
        // an array of keywords.
        return $this->textToKeywords( $text );
    }

    /**
     * Returns an array of keywords found in all node attributes,
     * including the names, long names, descriptions, and other attributes.
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
    public function getAllNodeKeywords( )
    {
        // Add all node attribute keys and values for all nodes.
        $text = '';
        foreach ( $this->nodes as &$node )
        {
            foreach ( $node['attributes'] as $key => &$value )
            {
                // Add the key. Skip well-known key names.  Intelligently
                // convert to text.
                if ( !isset( self::$WELL_KNOWN_NODE_ATTRIBUTES[$key] ) )
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
     * Merges the given named attribute with the selected node's
     * existing attributes.
     *
     * New attributes overwrite existing attributes with the same name.
     *
     * Example:
     * @code
     *   $table->setNodeAttribute( $id, 'name', 'Total' );
     * @endcode
     *
     * @param integer $nodeID  the non-negative numeric index of the node.
     *
     * @param string  $key  the key of a node attribute.
     *
     * @param mixed   $value  the value of a node attribute.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     *
     * @throws \InvalidArgumentException  if $key is not a string or is empty,
     * or if $value is not a string when $key is one of the well-known
     * attributes.
     */
    public function setNodeAttribute( $nodeID, $key, $value )
    {
        // Validate. Insure the key is a string, and the value for
        // well-known attributes is a string.
        $this->_validateNodeID( $nodeID );

        if ( !is_string( $key ) || $key === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attribute_key_invalid );

        if ( isset( self::$WELL_KNOWN_NODE_ATTRIBUTES[$key] ) &&
            !is_string( $value ) )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attribute_wellknown_key_value_invalid );

        // Set. If the name changes, remove the old entry from the
        // name map and add the new name, if any.
        if ( (string)$key == 'name' )
        {
            $oldName = $this->nodes[$nodeID]['attributes']['name'];
            $this->_deleteNodeFromNameMap( $nodeID, $oldName );
            $this->nodes[$nodeID]['attributes']['name'] = $value;
            $this->_addNodeToNameMap( $nodeID, 'name' );
        }
        else
            $this->nodes[$nodeID]['attributes'][$key] = $value;
    }

    /**
     * Merges the given associative array of named attributes with the
     * selected node's existing attributes, if any.
     *
     * New attributes overwrite existing attributes with the same name.
     *
     * The node's attributes array may contain additional application-
     * or file format-specific attributes.
     *
     * Example:
     * @code
     *   $attributes = array( 'name' => 'Total' );
     *   $table->setNodeAttributes( $id, $attributes );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node.
     *
     * @param   array $attributes  an associatve array of named
     * attributes associated with the node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function setNodeAttributes( $nodeID, $attributes )
    {
        // Validate
        $this->_validateNodeID( $nodeID );
        if ( $attributes == NULL )
            return;
        if ( !is_array( $attributes ) && !is_object( $attributes ) )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attributes_invalid );

        // Convert object argument to an array, if needed.
        $a = (array)$attributes;
        if ( empty( $a ) )
            return;                     // Request to set with nothing

        // Insure keys are all strings and all well-known key values
        // are strings.
        foreach ( $a as $key => $value )
        {
            if ( !is_string( $key ) || $key === '' )
                throw new \InvalidArgumentException(
                    self::$ERROR_node_attribute_key_invalid );

            if ( isset( self::$WELL_KNOWN_NODE_ATTRIBUTES[$key] ) &&
                !is_string( $value ) )
                throw new \InvalidArgumentException(
                    self::$ERROR_node_attribute_wellknown_key_value_invalid );
        }

        // Get the old name, if any.
        if ( isset( $this->nodes[$nodeID]['attributes']['name'] ) )
            $oldName = $this->nodes[$nodeID]['attributes']['name'];
        else
            $oldName = NULL;

        // Set attributes.
        $this->nodes[$nodeID]['attributes'] =
            array_merge( $this->nodes[$nodeID]['attributes'], $a );

        // If the name changed, update the node-to-ID map.
        $newName = $this->nodes[$nodeID]['attributes']['name'];
        if ( $oldName != $newName )
        {
            $this->_deleteNodeFromNameMap( $nodeID, $oldName );
            $this->_addNodeToNameMap( $nodeID, $newName );
        }
    }
    // @}
    //----------------------------------------------------------------------
    // Node operations
    //----------------------------------------------------------------------
    /**
     * @name Node operations
     */
    // @{
    /**
     * Adds a new node as a child of the selected node, initialized
     * with the given attributes and values.
     *
     * Example:
     * @code
     *   $nodeID = $tree->addNode( $parentNodeID, $attributes );
     * @endcode
     *
     * @param integer  $parentNodeID  the unique non-negative numeric ID of
     * the parent node.
     *
     * @param array    $attributes    an associative array of named attributes
     * for the node, or an empty array or NULL if there are no attributes.
     *
     * @return integer               the unique non-negative numeric ID of
     * the new node.
     *
     * @throws \OutOfBoundsException  if $parentNodeID is out of bounds.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL.
     */
    public function addNode( $parentNodeID, $attributes = NULL )
    {
        // Validate the parent node ID.
        $this->_validateNodeID( $parentNodeID );

        // Validate the attributes and values arrays.
        if ( !is_array( $attributes ) && !is_object( $attributes ) &&
            $attributes != NULL )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attributes_invalid );

        // Create a node entry with attributes, values, a parent,
        // and no children. A node entry is an associative array containing
        // a few specific internal attributes (values, parent, children)
        // and an arbitrary list of well-known and application-specific
        // attributes.
        $node = array( );
        $node['parent']   = $parentNodeID;
        $node['children'] = array( );
        if ( empty( $attributes ) )
            $node['attributes'] = array( );
        else
            $node['attributes'] = (array)$attributes;

        // Use the next available node ID and add the node to the
        // nodes array using that node ID.
        $nodeID = $this->nextNodeID;
        ++$this->nextNodeID;
        $this->nodes[$nodeID] = $node;

        // Add to the parent.  The 'children' array is associative where
        // the keys are node IDs and the values are 0 and unused.
        $this->nodes[$parentNodeID]['children'][$nodeID] = 0;

        // Add to the name-to-ID table.
        if ( isset( $node['attributes']['name'] ) )
            $this->_addNodeToNameMap( $nodeID, $node['attributes']['name'] );

        return $nodeID;
    }

    /**
     * Clears the entire tree, removing all nodes and tree attributes,
     * leaving an entirely empty tree.
     *
     * This method is equivalent to clearing all tree attributes, then
     * deleting all nodes:
     * @code
     *   $tree->clearAttributes( );
     *   $tree->deleteNodes( 0, $tree->getNumberOfNodes( ) );
     * @endcode
     *
     * Example:
     * @code
     *   $tree->clear( );
     * @endcode
     *
     * @see clearAttributes( ) to clear tree attributes while retaining nodes.
     *
     * @see deleteNodes( ) to delete nodes in the tree, while
     *   retaining tree attributes.
     */
    public function clear( )
    {
        // Initialize all arrays to be empty.
        $this->clearAttributes( );
        $this->nodes       = array( );  // Delete nodes
        $this->nodeNameMap = array( );
        $this->rootID      = -1;
        $this->nextNodeID  = 0;
    }

    /**
     * Deletes a selected node and all of its children nodes, if any.
     *
     * If the selected node is the root node of the tree, the entire
     * tree is deleted.
     *
     * Example:
     * @code
     *   $tree->deleteNode( $nodeID );
     * @endcode
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function deleteNode( $nodeID )
    {
        // Validate the node ID.
        $this->_validateNodeID( $nodeID );

        if ( $nodeID == $this->rootID )
        {
            // Node to delete is root. Clear entire tree.
            $this->nodes       = array( );
            $this->nodeNameMap = array( );
            $this->rootID      = -1;
            $this->nextNodeID  = 0;
        }
        else
        {
            // Recursively delete the subtree at the node.
            $parentID = $this->nodes[$nodeID]['parent'];
            $this->_recursiveDeleteNode( $nodeID );

            // Get the parent's list of children. The array is associative
            // where keys are children node IDs and values are unused.
            // To delete the child, unset the entry for the child's node ID.
            unset( $this->nodes[$parentID]['children'][$nodeID] );
        }
    }

    /**
     * Returns the node IDs of all nodes in the tree, or an empty array
     * if the tree is empty.
     *
     * Example:
     * @code
     *   $nodes = $tree->getAllNodes( );
     *   foreach ( $nodes as $nodeID )
     *   {
     *     $name = $tree->getNodeName( $nodeID );
     *     print( "Node $nodeID = $name\n" );
     *   }
     * @endcode
     *
     * @return array  an array of unique non-negative numeric IDs for the
     * nodes in the tree.
     */
    public function getAllNodes( )
    {
        // The $nodes array is associative where the keys are node IDs.
        // Return an array of those keys.
        return array_keys( $this->nodes );
    }

    /**
     * Returns a array of node IDs for direct children of the node, or an
     * empty array if the node has no children.
     *
     * Example:
     * @code
     *   $children = $tree->getNodeChildren( $nodeID );
     *   foreach ( $children as $id )
     *   {
     *     print( "Node $id\n" );
     *   }
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node
     *
     * @return array  returns an array of node IDs of children of
     * the node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeChildren( $nodeID )
    {
        // Validate the node ID.
        $this->_validateNodeID( $nodeID );

        // The node's 'children' array is associative where the keys are
        // node IDs and the values are unused. Return an array of the keys.
        return array_keys( $this->nodes[$nodeID]['children'] );
    }

    /**
     * Returns the unique non-negative numeric ID of the parent node
     * of the selected node, or a -1 if the node is the root node
     * of the tree.
     *
     * Example:
     * @code
     *   $parentID = $tree->getNodeParent( $nodeID );
     * @endcode
     *
     * @param  integer $nodeID  the unique non-negative numeric ID of the node
     *
     * @return integer          the unique non-negative numeric ID of the
     * parent node of the selected node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeParent( $nodeID )
    {
        // Validate the node ID.
        $this->_validateNodeID( $nodeID );

        return $this->nodes[$nodeID]['parent'];
    }

    /**
     * Returns the depth of the tree.
     *
     * If the tree has no root node, the depth is 0.
     *
     * If there is only a root node, the depth is 1.
     *
     * Otherwise, the number of levels is the maximum depth to any node,
     * found by searching the tree.
     *
     * Example:
     * @code
     *   $levels = $tree->getDepth( );
     * @endcode
     *
     * @return  integer returns the depth of the tree.
     */
    public function getDepth( )
    {
        if ( $this->rootID == -1 )
            return 0;
        return $this->findMaxDepth( $this->rootID );
    }

    /**
     * Recursively searches the tree, starting at the given node, to find
     * the maximum depth from that node downward.
     *
     * If the node has no children, the return depth is 1.  Otherwise it
     * is the maximum of the depths for all subtrees rooted at the
     * node's children.
     *
     * @param   integer $nodeID the unique non-negative numeric ID of the
     * node to start the search on.
     *
     * @return  integer         the maximum depth of the tree, starting at
     * the gien node.
     */
    private function findMaxDepth( $nodeID )
    {
        // Get the node's children.
        $children = array_keys( $this->nodes[$nodeID]['children'] );
        $max = 1;
        foreach ( $children as $childID )
        {
            $d = 1 + $this->findMaxDepth( $childID );
            if ( $d > $max )
                $max = $d;
        }
        return $max;
    }

    /**
     * Returns the total number of nodes in the tree.
     *
     * Example:
     * @code
     *   $number = $tree->getNumberOfNodes( );
     * @endcode
     *
     * @return integer  returns the number of nodes in the tree.
     */
    public function getNumberOfNodes( )
    {
        return count( $this->nodes );
    }

    /**
     * Returns the unique ID of the root node.
     *
     * The node ID may be used with getNode( ) to return attributes
     * and values for the node, and a list of the node's children.
     *
     * Example:
     * @code
     *   $rootID = $tree->getRootID( );
     *   $rootName = $tree->getNodeShortName( $rootID );
     * @endcode
     *
     * @return  integer  returns a unique integer ID for the root node,
     * or a -1 if there is no root node.
     */
    public function getRootNodeID( )
    {
        return $this->rootID;
    }

    /**
     * Sets the root node, initializing it with the given attributes
     * and values.
     *
     * If the tree already has a root node, the tree is cleared first,
     * deleting all of its nodes.
     *
     * Example:
     * @code
     *   $rootID = $tree->setRootNode( $attributes );
     * @endcode
     *
     * @param array    $attributes    an associative array of named attributes
     * for the node, or an empty array or NULL if there are no attributes.
     *
     * @return integer               the unique non-negative numeric ID of
     * the new root node.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL.
     */
    public function setRootNode( $attributes = NULL )
    {
        // Validate attributes and values arrays.
        if ( !is_array( $attributes ) && !is_object( $attributes ) &&
            $attributes != NULL )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attributes_invalid );

        // Delete the current tree's nodes first.
        if ( $this->rootID != -1 )
        {
            $this->nodes       = array( );      // Delete nodes
            $this->nodeNameMap = array( );
            $this->rootID      = -1;
            $this->nextNodeID  = 0;
        }

        // Create a node entry with attributes, values, a parent,
        // and no children. A node entry is an associative array containing
        // a few specific internal attributes (values, parent, children)
        // and an arbitrary list of well-known and application-specific
        // attributes.
        $node = array( );
        $node['parent']   = -1;                 // Root has no parent
        $node['children'] = array( );
        if ( empty( $attributes ) )
            $node['attributes'] = array( );
        else
            $node['attributes'] = $attributes;

        // Use the next available node ID and add the node to the
        // nodes array using that node ID.
        $this->rootID = $this->nextNodeID;
        ++$this->nextNodeID;
        $this->nodes[$this->rootID] = $node;

        // Add to the name-to-ID table.
        if ( isset( $node['attributes']['name'] ) )
            $this->_addNodeToNameMap( $this->rootID, $node['attributes']['name'] );

        return $this->rootID;
    }
    // @}
}

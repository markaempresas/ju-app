<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Graph to manage a graph with a root node and
 * children that may, in turn, have children.
 */

namespace Drupal\chart_suite\SDSC\StructuredData;

/**
 * @class Graph
 * Graph manages a named directed or undirected graph of named nodes
 * and named edges connecting nodes together, where each node and each edge
 * contains list of named values and metadata.
 *
 *
 * #### Graph attributes
 * Graphs have an associative array of attributes that
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
 * 'json-graph').
 *
 *
 * #### Node and Edge attributes
 * Graphs have zero or more nodes and zero or more edges between them.
 * Each node and edge has an associative array of attributes that provide
 * descriptive metadata for the node or edge. Applications may add any
 * number and type of attributes, but this class, and others in
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
 * #### Nodes and edges
 * Unlike a tree, a graph has no starting point - no root node. Instead,
 * a graph is an unprioritized collection of nodes, where each node may
 * connect to zero or more other nodes in the graph by way of an edge.
 *
 * An edge always connects two nodes. Typically those are different
 * nodes, but it is possible for an edge to connect from and to the
 * same node to create a circular self reference.
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
 * @version 0.0.2  Revised to subclass AbstractData and throw standard
 *   SPL exceptions.
 */
final class Graph
    extends AbstractData
{
//----------------------------------------------------------------------
// Fields
//----------------------------------------------------------------------
    /**
     * @var  array $nodes
     * An array of nodes with numeric keys. Node "IDs" are keys
     * into this array. The order of nodes is irrelevant.  Deletion
     * of a node unsets the entry, which causes gaps in the array
     * key sequence.
     *
     * Validation of a node ID checks if the ID is a valid key for
     * the array.
     *
     * The number of nodes equals count( $nodes ).
     *
     * Each node in the array is an associative array with keys for:
     *      - 'attributes'  - associative array of named attributes
     *      - 'edges'       - array of edge node IDs as keys
     *
     * The 'attributes' key selects an associative array containing
     * named attributes/values. Well-known attributes include:
     *
     *      - 'name'        - short name
     *      - 'longName'    - long name
     *      - 'description' - description
     *
     * The 'edges' key selects an associative array that always
     * exists and is initially empty.  This array is associative
     * where keys are edge IDs for the edges connecting this node to
     * other nodes, and values are always 0 (they are not used).
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
     * Node IDs start at 0 for an empty graph, then increment each
     * time a node is added. On deletion, the IDs of deleted nodes
     * are *not* reused. Node IDs are monotonicaly increasing.
     */
    private $nextNodeID;



    /**
     * @var  array $edges
     * An array of edges with numeric edge ID keys and associative
     * array values.  The order of edges is irrelevant.  Deletion
     * of a edge unsets the array entry, causing array keys to *not*
     * be consecutive integers.
     *
     * Validation of a edge ID checks if the ID is a valid key for
     * the array.
     *
     * The number of edges equals count( $edges ).
     *
     * Each edge in the array is an associative array with keys for:
     *      - 'attributes'  - associative array of named attributes
     *      - 'node1'       - the first node for the edge
     *      - 'node2'       - the second node for the edge
     *
     * The 'attributes' key selects an associative array containing
     * named attributes/values. Well-known attributes include:
     *
     *      - 'name'        - short name
     *      - 'longName'    - long name
     *      - 'description' - description
     *
     * The 'node1' and 'node2' keys select scalars containing node IDs,
     * and both are always present for every edge. The two nodes may be
     * the same. The order of the nodes matches the order given when the
     * edge was constructed.
     */
    private $edges;

    /**
     * @var  array $edgeNameMap
     * An associative array with edge name string keys. An entry
     * exists if a particular name is used by one or more edges.
     *
     * Each entry is an associative array where array keys are numeric
     * edge IDs, and values are always '0' - the value is not used and
     * is merely there to fill an entry. The array keys are what are
     * used to provide a list of edge IDs with the same name.
     */
    private $edgeNameMap;

    /**
     * @var  integer $nextNodeID
     * The next available unique non-negative integer edge ID.
     * Edge IDs start at 0 for an empty graph, then increment each
     * time a edge is added. On deletion, the IDs of deleted edges
     * are *not* reused. Edge IDs are monotonicaly increasing.
     */
    private $nextEdgeID;




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

    /**
     * @var array WELL_KNOWN_EDGE_ATTRIBUTES
     * An associative array where the keys are the names of well-known
     * edge attributes.
     */
    public static $WELL_KNOWN_EDGE_ATTRIBUTES = array(
        'name'        => 1,
        'longName'    => 1,
        'description' => 1
    );

    private static $ERROR_graph_node_id_invalid =
        'Node ID is out of bounds.';

    private static $ERROR_node_attributes_invalid =
        'Node attributes must be an array or object.';
    private static $ERROR_node_values_invalid =
        'Node values must be an array or object.';
    private static $ERROR_node_attribute_key_invalid =
        'Node attribute keys must be non-empty strings.';
    private static $ERROR_node_attribute_wellknown_key_value_invalid =
        'Node attribute values for well-known keys must be strings.';

    private static $ERROR_graph_edge_id_invalid =
        'Edge ID is out of bounds.';

    private static $ERROR_edge_attributes_invalid =
        'Edge attributes must be an array or object.';
    private static $ERROR_edge_values_invalid =
        'Edge values must be an array or object.';
    private static $ERROR_edge_attribute_key_invalid =
        'Edge attribute keys must be non-empty strings.';
    private static $ERROR_edge_attribute_wellknown_key_value_invalid =
        'Edge attribute values for well-known keys must be strings.';





//----------------------------------------------------------------------
    // Constructors & Destructors
    //----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs an empty graph with no nodes or edges, and the provided
     * list of attributes, if any.
     *
     *
     * @param   array $attributes  an optional associatve array of named
     * attributes associated with the graph.
     *
     * @return  Graph             returns a new empty graph with the
     * provided graph attributes.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function __construct( $attributes = NULL )
    {
        parent::__construct( $attributes );

        // Initialize empty node and edge arrays.
        $this->nodes       = array( );
        $this->nodeNameMap = array( );
        $this->nextNodeID  = 0;

        $this->edges       = array( );
        $this->edgeNameMap = array( );
        $this->nextEdgeID  = 0;
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
     * Destroys the previously constructed graph.
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
                self::$ERROR_graph_node_id_invalid );
    }





    /**
     * Adds an edge to a node.
     *
     * The given node ID and edge ID are assumed to be valid.
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     *
     * @param integer $edgeID  the unique non-negative numeric ID of a edge.
     */
    private function _addEdgeToNode( $nodeID, $edgeID )
    {
        // A node's edge list is an associative array where the keys are
        // edge IDs and the values are always 0 (unused).  This should
        // not be possible since we check for this before trying to
        // add the edge to the node.
        // Since this should never happen, there is no way to test this.
        // @codeCoverageIgnoreStart
        if ( isset( $this->nodes[$nodeID]['edges'][$edgeID] ) )
            return;                                 // Already in edge array
        // @codeCoverageIgnoreEnd
        $this->nodes[$nodeID]['edges'][$edgeID] = 0;
    }

    /**
     * Deletes an edge from a node.
     *
     * The given node ID and edge ID are assumed to be valid.
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     *
     * @param integer $edgeID  the unique non-negative numeric ID of a edge.
     */
    private function _deleteEdgeFromNode( $nodeID, $edgeID )
    {
        // A node's edge list is an associative array where the keys are
        // edge IDs and the values are always 0 (unused).
        unset( $this->nodes[$nodeID]['edges'][$edgeID] );
    }




    /**
     * Adds the selected edge's ID to the name table with the given name.
     *
     * The given edge ID is assumed to be valid.
     *
     * @param integer $edgeID  the unique non-negative numeric ID of a edge.
     *
     * @param string   $name    a string containing the name name
     * of the edge, or an empty string if there is no name name.
     */
    private function _addEdgeToNameMap( $edgeID, $name )
    {
        // The $edgeNameMap is an associative array where names are
        // the keys. Entry values are associative arrays where the
        // keys are edge IDs, and the values are irrelevant.
        if ( $name === '' || $name === NULL )
            return;

        // If the map has no current entry for the name, add one.
        // Otherwise, add a key for the new edge ID. Values are
        // not used and are always 0.
        if ( !isset( $this->edgeNameMap[$name] ) )
            $this->edgeNameMap[$name]          = array( $edgeID => 0 );
        else
            $this->edgeNameMap[$name][$edgeID] = 0;
    }

    /**
     * Removes the selected edge's ID from the name table entry with
     * the given name.
     *
     * The given edge ID is assumed to be valid.
     *
     * @param integer $edgeID  the unique non-negative numeric ID of a edge.
     *
     * @param string   $name    a string containing the name
     * of the edge, or an empty string if there is no name.
     */
    private function _deleteEdgeFromNameMap( $edgeID, $name )
    {
        // The $edgeNameMap is an associative array where names are
        // the keys. Entry values are associative arrays where the
        // keys are edge IDs, and the values are irrelevant.
        if ( $name === '' || $name === NULL )
            return;

        // If the map has no entry for the name, then the name was not
        // in use and we're done.  This should never happen since all
        // nodes with names are added to the name map.
        //
        // Since this should never happen, there is no way to test this.
        // @codeCoverageIgnoreStart
        if ( !isset( $this->edgeNameMap[$name] ) )
            return;                         // Name is not in use
        // @codeCoverageIgnoreEnd

        // If the map entry has no key for the edge, then the edge was
        // not registered as using this name and we're done.  Again,
        // this should never happen since all entries have edge.
        //
        // Since this should never happen, there is no way to test this.
        // @codeCoverageIgnoreStart
        if ( !isset( $this->edgeNameMap[$name][$edgeID] ) )
            return;                         // Node isn't registered for name
        // @codeCoverageIgnoreEnd

        // Unset the map entry's key for the edge.
        unset( $this->edgeNameMap[$name][$edgeID] );

        // If that makes the map entry empty, unset it.
        if ( empty( $this->edgeNameMap[$name] ) )
            unset( $this->edgeNameMap[$name] );
    }

    /**
     * Validates a edgeID and throws an exception if the ID is out of
     * range.
     *
     * @param integer $edgeID  the unique non-negative numeric ID of a edge.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    private function _validateEdgeID( $edgeID )
    {
        // The $edges array is an associative array where edge IDs are
        // the keys. IDs are always non-negative. If an ID is negative
        // or if there is no entry for the ID, then the ID is not valid.
        if ( $edgeID < 0 || !isset( $this->edges[$edgeID] ) )
            throw new \OutOfBoundsException(
                self::$ERROR_graph_edge_id_invalid );
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
     * values and edges to other nodes, if any.
     *
     * Example:
     * @code
     *   $graph->clearNodeAttributes( $id );
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
     *   $ids = $graph->findNodesByName( 'abc' );
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
     *   $graph->getNodeAttribute( $id, 'name' );
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
     *   $graph->getNodeAttributes( $id );
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
        // Validate. Insure the key is a string and the value for
        // well-known attributes is a string.
        $this->_validateNodeID( $nodeID );
        if ( !is_string( $key ) || $key === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attribute_key_invalid );

        if ( isset( self::$WELL_KNOWN_NODE_ATTRIBUTES[$key] ) &&
            !is_string( $value ) )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attribute_wellknown_key_value_invalid );

        // Set. If the name changes, update the name map.
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
            return;                     // Request to set with nothing
        if ( !is_array( $attributes ) && !is_object( $attributes ) )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attributes_invalid );

        // Convert object argument to an array, if needed.
        $a = (array)$attributes;
        if ( empty( $a ) )
            return;

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
    // Edge attributes methods
    //----------------------------------------------------------------------
    /**
     * @name Edge attributes methods
     */
    // @{
    /**
     * Clears attributes for the selected edge, while retaining its
     * values and nodes on either end of the edge.
     *
     * Example:
     * @code
     *   $graph->clearEdgeAttributes( $id );
     * @endcode
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function clearEdgeAttributes( $edgeID )
    {
        $this->_validateEdgeID( $edgeID );

        // Clear attributes. If there is an edge name, remove the edge
        // from the name map.
        if ( !isset( $this->edges[$edgeID]['attributes']['name'] ) )
            $this->edges[$edgeID]['attributes'] = array( );
        else
        {
            $name = $this->edges[$edgeID]['attributes']['name'];
            $this->edges[$edgeID]['attributes'] = array( );
            $this->_deleteEdgeFromNameMap( $edgeID, $name );
        }
    }

    /**
     * Returns an array of edge IDs for edges with the selected
     * name, or an empty array if there are no edges with the name.
     *
     * Example:
     * @code
     *   $ids = $graph->findEdgesByName( 'abc' );
     *   foreach ( $ids as $id )
     *   {
     *     print( "Edge $id\n" );
     *   }
     * @endcode
     *
     * @return  array  returns an array of edge IDs for edges with
     * the given name, or an empty array if no edges were found.
     *
     * @throws \InvalidArgumentException  if $name is not a non-empty string.
     */
    public function findEdgesByName( $name )
    {
        // Validate.
        if ( !is_string( $name ) || $name === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_edge_attribute_key_invalid );

        // The name map is an associative array where the keys are names
        // and the values are arrays. Those arrays are each associative
        // where the keys are edge IDs and the values are unused.

        // If the map has no entry for the name, there are no edges with
        // that name.
        if ( !isset( $this->edgeNameMap[$name] ) )
            return array( );

        // Otherwise return the keys for that name's array. These are
        // edge IDs.
        return array_keys( $this->edgeNameMap[$name] );
    }

    /**
     * Returns a copy of the selected attribute for the selected edge,
     * or a NULL if the attribute does not exist.
     *
     * Example:
     * @code
     *   $graph->getEdgeAttribute( $id, 'name' );
     * @endcode
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @param  string  $key     the name of an attribute to query
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     *
     * @throws \InvalidArgumentException  if $key is not a non-empty string.
     */
    public function getEdgeAttribute( $edgeID, $key )
    {
        // Validate the node ID.
        $this->_validateEdgeID( $edgeID );
        if ( !is_string( $key ) || $key === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_edge_attribute_key_invalid );

        // Get.
        if ( isset( $this->edges[$edgeID]['attributes'][$key] ) )
            return $this->edges[$edgeID]['attributes'][$key];
        return NULL;                        // No such key
    }

    /**
     * Returns a copy of all attributes for the selected edge.
     *
     * Example:
     * @code
     *   $graph->getEdgeAttributes( $id );
     * @endcode
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function getEdgeAttributes( $edgeID )
    {
        // Validate the edge ID.
        $this->_validateEdgeID( $edgeID );

        // Get.
        return $this->edges[$edgeID]['attributes'];
    }

    /**
     * Returns a "best" edge name by checking for, in order, the long name
     * and short name, and returning the first non-empty value
     * found, or the edge id if all of those are empty.
     *
     * Example:
     * @code
     *   $bestName = $data->getEdgeBestName( $id );
     * @endcode
     *
     * This method is a convenience function that is the equivalent of
     * checking each of the long name and name attributes in order.
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @return  the best name
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function getEdgeBestName( $edgeID )
    {
        $v = $this->getEdgeAttribute( $edgeID, 'longName' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        $v = $this->getEdgeAttribute( $edgeID, 'name' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return strval( $edgeID );
    }

    /**
     * Returns the description of the selected edge, or an empty string if it
     * has no description.
     *
     * Example:
     * @code
     *   $description = $graph->getEdgeDescription( $id );
     * @endcode
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @return string  the description for the selected edge, or an empty
     * string if the edge has no description.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function getEdgeDescription( $edgeID )
    {
        $v = $this->getEdgeAttribute( $edgeID, 'description' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns the long name of the selected edge, or an empty string if it
     * has no long name.
     *
     * Example:
     * @code
     *   $longName = $graph->getEdgeLongName( $id );
     * @endcode
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @return string  the long name for the selected edge, or an empty
     * string if the edge has no long name.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function getEdgeLongName( $edgeID )
    {
        $v = $this->getEdgeAttribute( $edgeID, 'longName' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns the name of the selected edge, or an empty string if it
     * has no name.
     *
     * Example:
     * @code
     *   $name = $graph->getEdgeName( $id );
     * @endcode
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @return string  the name for the selected edge, or an empty string if
     * the edge has no name.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function getEdgeName( $edgeID )
    {
        $v = $this->getEdgeAttribute( $edgeID, 'name' );
        if ( $v !== '' && $v !== NULL )
            return strval( $v );
        return '';
    }

    /**
     * Returns an array of keywords found in the edge's attributes,
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
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @return array  returns an array of keywords.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function getEdgeKeywords( $edgeID )
    {
        // Add all edge attribute keys and values for one edge.
        $text = '';
        foreach ( $this->edges[$edgeID]['attributes'] as $key => &$value )
        {
            // Add the key. Skip well-known key names.  Intelligently
            // convert to text.
            if ( !isset( self::$WELL_KNOWN_EDGE_ATTRIBUTES[$key] ) )
                $text .= ' ' . $this->valueToText( $key );

            // Add the value.  Intelligently convert to text.
            $text .= ' ' . $this->valueToText( $value );
        }

        // Clean the text of numbers and punctuation, and return
        // an array of keywords.
        return $this->textToKeywords( $text );
    }

    /**
     * Returns an array of keywords found in all edge attributes,
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
    public function getAllEdgeKeywords( )
    {
        // Add all edge attribute keys and values for all edges.
        $text = '';
        foreach ( $this->edges as &$edge )
        {
            foreach ( $edge['attributes'] as $key => &$value )
            {
                // Add the key. Skip well-known key names.  Intelligently
                // convert to text.
                if ( !isset( self::$WELL_KNOWN_EDGE_ATTRIBUTES[$key] ) )
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
     * Merges the given named attribute with the selected edge's
     * existing attributes.
     *
     * New attributes overwrite existing attributes with the same name.
     *
     * Example:
     * @code
     *   $table->setEdgeAttribute( $id, 'name', 'Total' );
     * @endcode
     *
     * @param integer $edgeID  the non-negative numeric index of the edge.
     *
     * @param string  $key  the key of a edge attribute.
     *
     * @param mixed   $value  the value of a edge attribute.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     *
     * @throws \InvalidArgumentException  if $key is not a string or is empty,
     * or if $value is not a string when $key is one of the well-known
     * attributes.
     */
    public function setEdgeAttribute( $edgeID, $key, $value )
    {
        // Validate. Insure the key is a string and the value for
        // well-known attributes is a string.
        $this->_validateEdgeID( $edgeID );
        if ( !is_string( $key ) || $key === '' )
            throw new \InvalidArgumentException(
                self::$ERROR_edge_attribute_key_invalid );

        if ( isset( self::$WELL_KNOWN_EDGE_ATTRIBUTES[$key] ) &&
            !is_string( $value ) )
            throw new \InvalidArgumentException(
                self::$ERROR_edge_attribute_wellknown_key_value_invalid );

        // Set. If the name changes, update the name map.
        if ( (string)$key == 'name' )
        {
            $oldName = $this->edges[$edgeID]['attributes']['name'];
            $this->_deleteEdgeFromNameMap( $edgeID, $oldName );
            $this->edges[$edgeID]['attributes']['name'] = $value;
            $this->_addEdgeToNameMap( $edgeID, 'name' );
        }
        else
            $this->edges[$edgeID]['attributes'][$key] = $value;
    }

    /**
     * Merges the given associative array of named attributes with the
     * selected edge's existing attributes, if any.
     *
     * New attributes overwrite existing attributes with the same name.
     *
     * The edge's attributes array may contain additional application-
     * or file format-specific attributes.
     *
     * Example:
     * @code
     *   $attributes = array( 'name' => 'Total' );
     *   $table->setEdgeAttributes( $id, $attributes );
     * @endcode
     *
     * @param  integer $edgeID  the unique non-negative numeric ID of the edge.
     *
     * @param   array $attributes  an associatve array of named
     * attributes associated with the edge.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL, or if any of its attributes have invalid keys or
     * values.
     */
    public function setEdgeAttributes( $edgeID, $attributes )
    {
        // Validate
        $this->_validateEdgeID( $edgeID );
        if ( $attributes == NULL )
            return;                     // Request to set with nothing
        if ( !is_array( $attributes ) && !is_object( $attributes ) )
            throw new \InvalidArgumentException(
                self::$ERROR_edge_attributes_invalid );

        // Convert object argument to an array, if needed.
        $a = (array)$attributes;
        if ( empty( $a ) )
            return;

        // Insure keys are all strings and all well-known key values
        // are strings.
        foreach ( $a as $key => $value )
        {
            if ( !is_string( $key ) || $key === '' )
                throw new \InvalidArgumentException(
                    self::$ERROR_edge_attribute_key_invalid );

            if ( isset( self::$WELL_KNOWN_NODE_ATTRIBUTES[$key] ) &&
                !is_string( $value ) )
                throw new \InvalidArgumentException(
                    self::$ERROR_edge_attribute_wellknown_key_value_invalid );
        }

        // Get the old name, if any.
        if ( isset( $this->edges[$edgeID]['attributes']['name'] ) )
            $oldName = $this->edges[$edgeID]['attributes']['name'];
        else
            $oldName = NULL;

        // Set attributes.
        $this->edges[$edgeID]['attributes'] =
            array_merge( $this->edges[$edgeID]['attributes'], $a );

        // If the name changed, update the edge-to-ID map.
        $newName = $this->edges[$edgeID]['attributes']['name'];
        if ( $oldName != $newName )
        {
            $this->_deleteEdgeFromNameMap( $edgeID, $oldName );
            $this->_addEdgeToNameMap( $edgeID, $newName );
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
     * Adds a new node initialized with the given attributes and values.
     *
     * Example:
     * @code
     *   $nodeID = $graph->addNode( $attributes );
     * @endcode
     *
     * @param array    $attributes    an associative array of named attributes
     * for the node, or an empty array or NULL if there are no attributes.
     *
     * @return integer               the unique non-negative numeric ID of
     * the new node.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL.
     */
    public function addNode( $attributes = NULL )
    {
        // Validate the attributes and values arrays.
        if ( !is_array( $attributes ) && !is_object( $attributes ) &&
            $attributes != NULL )
            throw new \InvalidArgumentException(
                self::$ERROR_node_attributes_invalid );

        // Create a node entry with attributes, values, and no edges.
        // A node entry is an associative array containing
        // a few specific internal attributes (values, edges)
        // and an arbitrary list of well-known and application-specific
        // attributes.
        $node = array( );
        $node['edges']  = array( );
        if ( empty( $attributes ) )
            $node['attributes'] = array( );
        else
            $node['attributes'] = (array)$attributes;

        // Use the next available node ID and add the node to the
        // nodes array using that node ID.
        $nodeID = $this->nextNodeID;
        ++$this->nextNodeID;
        $this->nodes[$nodeID] = $node;

        // Add to the name-to-ID table.
        if ( isset( $node['attributes']['name'] ) )
            $this->_addNodeToNameMap( $nodeID, $node['attributes']['name'] );

        return $nodeID;
    }

    /**
     * Clears the entire graph, removing all nodes, edges, and
     * graph attributes, leaving an entirely empty graph.
     *
     * This method is equivalent to clearing all graph attributes, then
     * deleting all nodes:
     * @code
     *   $graph->clearAttributes( );
     *   $graph->deleteNodes( 0, $graph->getNumberOfNodes( ) );
     * @endcode
     *
     * Example:
     * @code
     *   $graph->clear( );
     * @endcode
     *
     * @see clearAttributes( ) to clear graph attributes while retaining nodes.
     *
     * @see deleteNodes( ) to delete nodes in the graph, while
     *   retaining graph attributes.
     */
    public function clear( )
    {
        // Initialize all arrays to be empty.
        $this->clearAttributes( );

        $this->nodes       = array( );  // Delete nodes
        $this->nodeNameMap = array( );
        $this->nextNodeID  = 0;

        $this->edges       = array( );  // Delete edges
        $this->edgeNameMap = array( );
        $this->nextEdgeID  = 0;
    }

    /**
     * Deletes a selected node and all edges between it and other nodes.
     *
     * Example:
     * @code
     *   $graph->deleteNode( $nodeID );
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

        // Copy the node's edges, if any.
        $name = NULL;
        if ( isset( $this->nodes[$nodeID]['attributes']['name'] ) )
            $name  = $this->nodes[$nodeID]['attributes']['name'];
        $edges = array( );
        if ( isset( $this->nodes[$nodeID]['edges'] ) )
            $edges = $this->nodes[$nodeID]['edges'];

        // Delete edges to and from the node. Edges cannot exist if
        // one of the nodes for the edge doesn't exist.
        foreach ( $edges as $key => $edgeID )
            $this->deleteEdge( $edgeID );

        // Delete the node.
        unset( $this->nodes[$nodeID] );
        $this->_deleteNodeFromNameMap( $nodeID, $name );
    }

    /**
     * Returns an array of nodeIDs for all nodes in the graph.
     *
     * @return  array  an array of unique non-negative numeric IDs for all
     * nodes in the graph.
     */
    public function getAllNodes( )
    {
        // The $nodes array is associative where the keys are node IDs.
        // Return an array of those keys.
        return array_keys( $this->nodes );
    }

    /**
     * Returns an array of the edge IDs for all edges to or from the
     * selected node, or an empty array if there are no edges for
     * the node.
     *
     * Example:
     * @code
     *   $edges = $graph->getNodeEdges( $nodeID );
     *   foreach ( $edges as $edgeID )
     *   {
     *     $edgeAttributes = $graph->getEdgeAttributes( $edgeID );
     *   }
     * @endcode
     *
     * @param integer $nodeID  the unique non-negative numeric ID of a node.
     *
     * @return  array   an array of unique non-negative numeric IDs for
     * all edges for the node.
     *
     * @throws \OutOfBoundsException  if $nodeID is out of bounds.
     */
    public function getNodeEdges( $nodeID )
    {
        // Validate node ID.
        $this->_validateNodeID( $nodeID );

        // The 'edges' attribute of a node is an associative array with
        // edge IDs as keys and values that are 0 (not used).  Return
        // that array's keys.
        return array_keys( $this->nodes[$nodeID]['edges'] );
    }

    /**
     * Returns the total number of nodes in the graph.
     *
     * Example:
     * @code
     *   $number = $graph->getNumberOfNodes( );
     * @endcode
     *
     * @return integer  returns the number of nodes in the graph.
     */
    public function getNumberOfNodes( )
    {
        return count( $this->nodes );
    }
    // @}
    //----------------------------------------------------------------------
    // Edge operations
    //----------------------------------------------------------------------
    /**
     * @name Edge operations
     */
    // @{
    /**
     * Adds a new edge initialized with the given attributes and values
     * and connecting together the two indicated nodes.
     *
     * While typically an edge connects two different nodes, an edge
     * may connect from and to the same node to create a circular
     * self-referencing connection in the graph.
     *
     * Edges may have a direction by setting the 'direction' attribute
     * for the edge. Typical attribute values are:
     *
     * - 'nondirectional' for an edge that has no direction
     * - 'directional' for an edge directed from the first node to the second
     * - 'bidirectional' for an edge directed both from the first node to
     * the second an from the second node back to the first
     *
     * Example:
     * @code
     *   $edgeID = $graph->addEdge( $nodeID1, $nodeID2, $attributes );
     * @endcode
     *
     * @param  integer $nodeID1  the unique non-negative numeric ID of the
     * first node at one end of the edge.
     *
     * @param  integer $nodeID2  the unique non-negative numeric ID of the
     * second node at one end of the edge.
     *
     * @param array    $attributes    an associative array of named attributes
     * for the edge, or an empty array or NULL if there are no attributes.
     *
     * @return integer               the unique non-negative numeric ID of
     * the new edge.
     *
     * @throws \InvalidArgumentException  if $attributes is not an array,
     * object, or NULL.
     */
    public function addEdge( $nodeID1, $nodeID2, $attributes = NULL )
    {
        // Validate node IDs.
        $this->_validateNodeId( $nodeID1 );
        if ( $nodeID2 != $nodeID1 )
            $this->_validateNodeId( $nodeID2 );

        // Validate attributes and values arrays.
        if ( !is_array( $attributes ) && !is_object( $attributes ) &&
            $attributes != NULL )
            throw new \InvalidArgumentException(
                self::$ERROR_edge_attributes_invalid );

        // Create a edge entry with attributes, values, and end nodes.
        // A edge entry is an associative array containing
        // a few specific internal attributes (values, node1, node2)
        // and an arbitrary list of well-known and application-specific
        // attributes.
        $edge = array( );
        $edge['node1']  = $nodeID1;
        $edge['node2']  = $nodeID2;
        if ( empty( $attributes ) )
            $edge['attributes'] = array( );
        else
            $edge['attributes'] = (array)$attributes;

        // Use the next available edge ID and add the edge to the
        // edges array using that edge ID.
        $edgeID = $this->nextEdgeID;
        ++$this->nextEdgeID;
        $this->edges[$edgeID] = $edge;

        // Add to the name-to-ID table.
        if ( isset( $edge['attributes']['name'] ) )
            $this->_addEdgeToNameMap( $edgeID, $edge['attributes']['name'] );

        // Add edge to both nodes.
        $this->_addEdgeToNode( $nodeID1, $edgeID );
        if ( $nodeID2 != $nodeID1 )
            $this->_addEdgeToNode( $nodeID2, $edgeID );

        return $edgeID;
    }

    /**
     * Deletes a selected edge.
     *
     * Example:
     * @code
     *   $graph->deleteEdge( $edgeID );
     * @endcode
     *
     * @param integer $edgeID  the unique non-negative numeric ID of a edge.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function deleteEdge( $edgeID )
    {
        // Validate edge ID.
        $this->_validateEdgeID( $edgeID );

        // Note the nodes on either end of the edge.
        $nodeID1 = $this->edges[$edgeID]['node1'];
        $nodeID2 = $this->edges[$edgeID]['node2'];

        // Delete the edge from tables.
        $name = NULL;
        if ( isset( $this->edges[$edgeID]['attributes']['name'] ) )
            $name = $this->edges[$edgeID]['attributes']['name'];
        unset( $this->edges[$edgeID] );
        $this->_deleteEdgeFromNameMap( $edgeID, $name );

        // Delete edges from the node's edge lists.
        $this->_deleteEdgeFromNode( $nodeID1, $edgeID );
        if ( $nodeID2 != $nodeID1 )
            $this->_deleteEdgeFromNode( $nodeID2, $edgeID );
    }

    /**
     * Returns an array of edgeIDs for all edges in the graph.
     *
     * @return  array  an array of unique non-negative numeric IDs for all
     * edges in the graph.
     */
    public function getAllEdges( )
    {
        // The $edges array is associative where the keys are edge IDs.
        // Return an array of those keys.
        return array_keys( $this->edges );
    }

    /**
     * Returns an array of the two node IDs for either end of the
     * selected edge.
     *
     * Example:
     * @code
     *   $nodes = $graph->getEdgeNodes( $edgeID );
     *   $node1 = $nodes[0];
     *   $node2 = $nodes[1];
     * @endcode
     *
     * @param integer $edgeID  the unique non-negative numeric ID of a edge.
     *
     * @return  array  an array of unique non-negative numeric IDs for the
     * two nodes on either end of the edge.
     *
     * @throws \OutOfBoundsException  if $edgeID is out of bounds.
     */
    public function getEdgeNodes( $edgeID )
    {
        // Validate edge ID.
        $this->_validateEdgeID( $edgeID );

        return array(
            $this->edges[$edgeID]['node1'],
            $this->edges[$edgeID]['node2']
        );
    }

    /**
     * Returns the total number of edges in the graph.
     *
     * Example:
     * @code
     *   $number = $graph->getNumberOfEdges( );
     * @endcode
     *
     * @return integer  returns the number of edges in the graph.
     */
    public function getNumberOfEdges( )
    {
        return count( $this->edges );
    }
    // @}
}

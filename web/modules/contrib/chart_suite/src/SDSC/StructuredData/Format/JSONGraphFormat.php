<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\JSONGraphFormat to parse and
 * serialize data in the JSON (JavaScript Object Notation) text syntax
 * for graphs.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;


use Drupal\chart_suite\SDSC\StructuredData\Graph;





/**
 * @class JSONGraphFormat
 * JSONGraphFormat provides decode and encode functions that map
 * between JSON (JavaScript Object Notation) text and a
 * Drupal\chart_suite\SDSC\StructuredData\Graph.
 *
 * JSON is a general-purpose syntax for describing objects, arrays,
 * scalars, and arrays of objects, arrays, of scalars to an arbitrary
 * nesting depth. This class, however, focuses on a narrower subset of
 * JSON usage in order to build graphs.
 *
 *
 * #### Graph syntax
 * A JSON graph is a hierarchy of nodes starting with a root node
 * with a name and list of children. Each of those children nodes
 * has a name and their own list of children, and so on to arbitrary
 * depth. Any node can have any number of named attributes with
 * arbitrary values.
 *
 *
 * ##### Graph object
 * JSON graphs always start as one of two types of object:
 * - A single graph
 * - An array of graphs
 *
 * For an array of graphs, the top-level object is expected to have
 * a "label" attribute that names the array, a "type" that characterizes
 * the array of graphs, and a "metadata" object of additional attributes.
 * A "graphs" attribute contains an array of the individual graphs.
 * <pre>
 *   {
 *     "label": "My list of graphs",
 *     "type":  "Supercool",
 *     "metadata": { ... },
 *     "graphs": [ ... ]
 *   }
 * </pre>
 *
 * A top-level single graph, or a graph in a graph array, is an object
 * that is expected to have a "label" attribute that names the graph,
 * a "type" attribute that characterizes the graph, a "directed" attribute
 * indicates if the graph is directed, and a "metadata" object
 * of additional attributes. The graph then has two arrays named "nodes"
 * and "edges":
 * <pre>
 *   {
 *     "label": "My graph",
 *     "type":  "Exciting!",
 *     "directed": true,
 *     "metadata": { ... },
 *     "nodes": [ ... ],
 *     "edges": [ ... ]
 *   }
 * </pre>
 *
 * Each entry in the "nodes" array describes a single node. Each node
 * has a "label" that names the node, an "id" that gives the node's
 * unique ID (typically a number), and a "metadata" object of additional
 * attributes.
 * <pre>
 *   {
 *     "label": "My node",
 *     "id": "1",
 *     "metadata": { ... }
 *   }
 * </pre>
 *
 * Each entry in the "edges" array describes a single edge between two
 * nodes. Each edge has a "label" that names the edge, a "relation"
 * that characterizes the edge, a boolean "directed" flag, a "metadata"
 * object of additional attributes, and "source" and "target" properties that
 * give the unique IDs of the nodes on either end of the edge.
 * <pre>
 *   {
 *     "label": "My edge",
 *     "relation": "connects-to",
 *     "directed": true,
 *     "metadata": { ... },
 *     "source": "1",
 *     "target": "2"
 *   }
 * </pre>
 *
 * ##### Graph types
 * The "type" attribute for graph arrays and individual graphs has no
 * defined vocabulary.
 *
 * ##### Edge relations
 * The "relation" attribute for edges has no defined vocabulary.
 *
 * ##### Metadata
 * The "metadata" attribute for graph arrays, graphs, nodes, and edges,
 * is an object with named values, but the names have no defined vocabulary.
 *
 * ##### Node IDs
 * The "id" attribute for nodes must have a unique value for every node,
 * but the structure of that value is not defined.
 *
 *
 * ##### Graph schema name
 * JSON graphs can have a microformat schema name that refers to
 * a well-known schema by setting the "type" attribute of the parent
 * object.  The type attribute value may be an array or a scalar with a
 * single string value.
 * <pre>
 *  {
 *      "type": [ "json-graph" ],
 *      "graph": [ ... ]
 *  }
 * </pre>
 *
 * This "type" attribute is semi-standard for microformat schemas, but
 * it collides with the "type" attribute for arrays of graphs and graphs.
 * However, since the vocabulary for "type" is not defined anyway for
 * graphs, this overload is acceptable.
 *
 *
 * #### Graph decode limitations
 * The amount of graph and node descriptive information available
 * in a JSON file depends upon how much of syntax above is used.
 * While graphs and nodes should have names, for instance, these are
 * optional. Descriptions and other metadata are also optional.
 *
 * When an array of graphs is read, each of the individual graphs are
 * returned by the decode method. Attributes for the array itself are
 * ignored. Only attributes for individual graphs are returned.
 *
 *
 * #### Graph encode limitations
 * The encoder can output a single graph or an array of graphs.
 *
 *
 * @see     Drupal\chart_suite\SDSC\StructuredData\Graph    the StructuredData Graph class
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    2/15/2016
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 */
final class JSONGraphFormat
    extends AbstractFormat
{
//----------------------------------------------------------------------
// Constants
//----------------------------------------------------------------------
    /**
     * An encoding style that generates a single object that starts
     * immediately with the graph. This is the most basic form
     * of graph output and omits the schema name, but includes all
     * nodes and edges and their attributes.
     *
     * <pre>
     *   {
     *     "label": "My graph",
     *     "type":  "Exciting!",
     *     "metadata": { ... },
     *     "nodes": [ ... ],
     *     "edges": [ ... ]
     *   }
     * </pre>
     */
    const ENCODE_AS_OBJECT = 1;

    /**
     * An encoding style identical to ENCODE_AS_OBJECT, but with
     * a parent object that includes an array of individual graphs
     * and a schema type.
     *
     * This is the default encoding.
     *
     * <pre>
     * {
     *   "type": "json-graph",
     *   "graphs": [
     *     {
     *       "label": "My graph",
     *       "type":  "Exciting!",
     *       "metadata": { ... },
     *       "nodes": [ ... ],
     *       "edges": [ ... ]
     *     }
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
     * decode and encode graphs in JSON (JavaScript Object Notation).
     */
    public function __construct( )
    {
        parent::__construct( );

        $this->attributes['syntax']         = 'JSON';
        $this->attributes['name']           = 'json-graph';
        $this->attributes['longName']       = 'JavaScript Object Notation (JSON) Graph';
        $this->attributes['MIMEType']       = 'application/json';
        $this->attributes['fileExtensions'] = array( 'json' );
        $this->attributes['description'] =
            'The JSON (JavaScript Object Notation) format encodes ' .
            'a variety of data, including tables, graphs, and graphs. '.
            'Graph data may have an unlimited number of nodes connected ' .
            'by edges to create an arbitrarily complex structure.  Each ' .
            'node and edge may have a short name, long name, and ' .
            'description.';
        $this->attributes['expectedUses'] = array(
            'Graphs with nodes and edges with names and values'
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
                'name' => 'JSON Graph',
                'natureOfApplicability' => 'specifies',
                'details' => ''
            )
        );

        // Unknown:
        //  identifier
        //  creationDate
        //  lastModificationDate
        //  contributors
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
     * @copydoc AbstractFormat::canDecodeGraphs
     */
    public function canDecodeGraphs( )
    {
        return true;
    }

    /**
     * @copydoc AbstractFormat::canEncodeGraphs
     */
    public function canEncodeGraphs( )
    {
        return true;
    }
    // @}
    //----------------------------------------------------------------------
    // Encode methods
    //----------------------------------------------------------------------
    /**
     * @name Encode methods
     */
    // @{
    /**
     * @copydoc AbstractFormat::decode
     *
     * #### Decode limitations
     * The JSON format always returns an array containing a single
     * Drupal\chart_suite\SDSC\StructuredData\Graph object.
     */
    public function decode( &$text )
    {
        if ( empty( $text ) )
            return array( );        // No graph


        // Parse JSON
        // -----------------------------------------------------
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
            // error is.
            throw new SyntaxException(
                'Malformed JSON.' );
        }
        // At this point we don't know what type of content we have.
        // We could have a graph.


        // Determine content type
        // -----------------------------------------------------
        // If the content is an object, look for a few
        // tell-tale properties to see what we have.
        if ( is_object( $content ) )
        {
            if ( property_exists( $content, 'graphs' ) )
            {
                // When there's a 'graphs' property, we have a top-level
                // object with an array of graphs. The top-level object
                // may have a schema.
                return $this->_decodeGraphObjectsWithSchema( $content );
            }
            if ( property_exists( $content, 'nodes' ) )
            {
                // When there's a 'nodes' property, we have a top-level
                // object that is a single graph and there is no schema.
                return array( $this->_decodeGraphObject( $content ) );
            }
        }

        // Otherwise we don't know what it is.
        throw new SyntaxException(
            'Unrecognized JSON content. Does not appear to be a graph.' );
    }

    /**
     * Decodes an array of graph objects with a schema.
     *
     * @param array $content  the content
     *
     * @throws InvalidContentException if the content cannot be parsed
     */
    private function _decodeGraphObjectsWithSchema( &$content )
    {
        // Check the type
        // -----------------------------------------------------
        // The format has a schema type and a list of graphs.
        // The schema type must be recognized.
        //
        // Good example:
        //  {
        //      "type":   [ "json-graph" ],
        //      "graphs": [ ... ]
        //  }

        if ( property_exists( $content, 'type' ) )
        {
            $type = $content->type;
            if ( !is_scalar( $type ) || (string)$type != 'json-graph' )
                throw new InvalidContentException(
                    'JSON graph "type" must be "json-graph".' );
        }


        // Parse, but ignore attributes
        // -----------------------------------------------------
        // A list of graphs can have a label and metadata. These
        // have a specific syntax, but we have no way to store them.
        // Nevertheless, enforce the syntax.
        $unusedAttributes = $this->_decodeAttributes( $content );


        // Create graphs
        // -----------------------------------------------------
        // Each entry in the "graphs" property (which has already
        // been checked and confirmed to exist) must be a valid
        // graph.
        $graphs = $content->graphs;
        if ( !is_array( $graphs ) )
            throw new InvalidContentException(
                'JSON "graphs" property must be an array of graph objects.' );

        $results = array( );
        foreach ( $graphs as &$graph )
        {
            if ( !is_object( $graph ) )
                throw new InvalidContentException(
                    'JSON "graphs" property must contain graph objects.' );
            $results[] = $this->_decodeGraphObject( $graph );
        }

        return $results;
    }

    /**
     * Decodes a graph object in the JSON Graph format.
     *
     * @param array $content  the content
     *
     * @throws InvalidContentException if the content cannot be parsed
     */
    private function _decodeGraphObject( &$content )
    {
        // Create graph
        // -----------------------------------------------------
        // The format supports a "label", which we use as the
        // graph "name". We also parse optional "metadata" for
        // additional well-known and custom graph attributes.
        //
        // Good example:
        //  {
        //      "label":  "my graph",
        //      "metadata": {
        //        "this": "that"
        //      }
        //      "nodes":  [ ... ],
        //      "edges":  [ ... ]
        //  }
        $attributes = array(
            // 'name' unknown
            // 'longName' unknown
            // 'description' unknown
            // 'sourceFileName' unknown
            'sourceMIMEType'   => $this->getMIMEType( ),
            'sourceSyntax'     => $this->getSyntax( ),
            'sourceSchemaName' => 'json-graph'
        );
        $graph = new Graph( $attributes );


        // Parse attributes
        // -----------------------------------------------------
        // Get more graph attributes, which may include the graph's
        // name and other metadata.
        //
        // The returned array also includes 'nodes' and 'edges',
        // if any.
        $moreAttributes = $this->_decodeAttributes( $content );


        // Parse nodes
        // -----------------------------------------------------
        // The node IDs in the file are needed to identify source
        // and target nodes for edges, but the file's node IDs
        // are not our internal IDs. So we need to maintain a mapping.
        $nodeIDMap = array( );
        if ( isset( $moreAttributes['nodes'] ) )
        {
            $nodes = $moreAttributes['nodes'];
            unset( $moreAttributes['nodes'] );
            if ( !is_array( $nodes ) )
                throw new InvalidContentException(
                    'JSON "nodes" property must be an array of nodes.' );
            foreach ( $nodes as &$node )
            {
                // Parse the node's attributes. This must include
                // an 'id' attribute.
                $attr = $this->_decodeAttributes( $node );
                if ( !isset( $attr['id'] ) )
                    throw new InvalidContentException(
                        'JSON nodes must have an "id" property.' );

                // Get the node's ID in the file, then remove it from
                // the attributes we'll be saving for the new node.
                $fileID = $attr['id'];
                if ( !is_scalar( $fileID ) )
                    throw new InvalidContentException(
                        'JSON node "id" property must be a scalar string.' );
                unset( $attr['id'] );

                // Create the new node with the remaining attriutes.
                $memoryID = $graph->addNode( $attr );

                // Add to the ID map for when we handle edges.
                $nodeIDMap[(string)$fileID] = $memoryID;
            }
        }


        // Parse edges
        // -----------------------------------------------------
        // The edges reference nodes by their IDs in the file.
        // Since the file IDs are not the same as our internal IDs,
        // we need to map them as we process each edge.
        if ( isset( $moreAttributes['edges'] ) )
        {
            $edges = $moreAttributes['edges'];
            unset( $moreAttributes['edges'] );
            if ( !is_array( $edges ) )
                throw new InvalidContentException(
                    'JSON "edges" property must be an array of nodes.' );
            foreach ( $edges as &$edge )
            {
                // Parse the edge's attributes. This must include
                // 'source' and 'target' attributes.
                $attr = $this->_decodeAttributes( $edge );
                $fileNode1 = -1;
                $fileNode2 = -1;
                if ( isset( $attr['source'] ) )
                {
                    if ( !is_scalar( $attr['source'] ) )
                        throw new InvalidContentException(
                            'JSON edge "source" must be a scalar string.' );
                    $fileNode1 = (integer)$attr['source'];
                    unset( $attr['source'] );
                }
                if ( isset( $attr['target'] ) )
                {
                    if ( !is_scalar( $attr['target'] ) )
                        throw new InvalidContentException(
                            'JSON edge "source" must be a scalar string.' );
                    $fileNode2 = (integer)$attr['target'];
                    unset( $attr['target'] );
                }
                if ( $fileNode1 == -1 || $fileNode2 == -1 )
                    throw new InvalidContentException(
                        'JSON edges must have "source" and "target" properties.' );

                // Map the file's node IDs into internal node IDs.
                if ( !isset( $nodeIDMap[$fileNode1] ) ||
                    !isset( $nodeIDMap[$fileNode2] ) )
                    throw new InvalidContentException(
                        'JSON edges source/target IDs do not match any nodes.' );
                $memoryNode1 = $nodeIDMap[$fileNode1];
                $memoryNode2 = $nodeIDMap[$fileNode2];

                // Add the edge.
                $graph->addEdge( $memoryNode1, $memoryNode2, $attr );
            }
        }

        $graph->setAttributes( $moreAttributes );

        return $graph;
    }

    /**
     * Decodes attributes for a graph, node, or edge, and returns an
     * associative array containing those attributes.
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
        // The format supports a "label", which we use as the
        // graph "name". We also parse optional "metadata" for
        // additional well-known and custom graph attributes.
        //
        // Good example:
        //  "label":  "my graph",
        //  "metadata": {
        //    "longName": "long name",
        //    "description": "description",
        //    "whatever": "something"
        //  }

        // Convert the object to an attributes array that initially
        // contains all properties. We'll type check and clean things
        // out below.
        $attributes = get_object_vars( $content );


        // Label
        // -----------------------------------------------------
        // If 'label' exists, make sure it is a string, then
        // rename it as 'name'.
        if ( isset( $attributes['label'] ) )
        {
            // Label's value must be a string.
            $value = $attributes['label'];
            if ( !is_scalar( $value ) )
                throw new InvalidContentException(
                    'JSON graph "label" property must be a scalar string.' );

            $attributes['name'] = (string)$value;
            unset( $attributes['label'] );
        }


        // Metadata
        // -----------------------------------------------------
        // If 'metadata' exists, pull it out and move its values
        // up into the attributes array.
        if ( isset( $attributes['metadata'] ) )
        {
            // Metadata's value must be an object.
            $value = $attributes['metadata'];
            if ( !is_object( $value ) )
                throw new InvalidContentException(
                    'JSON graph "metadata" property must be an object.' );

            $metaAttributes = (array)$value;
            unset( $attributes['metadata'] );
            $attributes = array_merge( $attributes, $metaAttributes );
        }

        // Make sure well-known attributes 'longName' and 'description'
        // have scalar string values, if they are provided.
        if ( isset( $attributes['longName'] ) )
        {
            if ( !is_scalar( $attributes['longName'] ) )
                throw new InvalidContentException(
                    'JSON graph "longName" property must be a scalar string.' );
        }
        if ( isset( $attributes['description'] ) )
        {
            if ( !is_scalar( $attributes['description'] ) )
                throw new InvalidContentException(
                    'JSON graph "description" property must be a scalar string.' );
        }

        return $attributes;
    }
    // @}
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
     * The JSON format supports encoding one or more
     * Drupal\chart_suite\SDSC\StructuredData\Graph objects to the format. When multiple graphs
     * are provided, encoding always uses ENCODE_AS_OBJECT_WITH_SCHEMA,
     * regardless of the value of the $options argument.
     */
    public function encode( &$objects, $options = 0 )
    {
        //
        // Validate arguments
        // -----------------------------------------------------
        // Check that we have an array and that all array entries
        // are Graph objects.
        if ( empty( $objects ) )
            return NULL;            // No graph to encode

        if ( !is_array( $objects ) )
            throw new \InvalidArgumentException(
                'JSON encode requires an array of objects.' );

        foreach ( $objects as &$object )
        {
            if ( !is_a( $object, 'Drupal\chart_suite\SDSC\StructuredData\Graph', false ) )
                throw new \InvalidArgumentException(
                    'JSON encode objects must be graphs.' );
        }

        //
        // Encode
        // -----------------------------------------------------
        // When there is only one graph, use the $options argument
        // to decide to encode it as a single object, or as an
        // array of graphs (with just one) with the schema header.
        //
        // Otherwise, when there are multiple graphs, ignore $options
        // and always encode with the schema header since that's
        // the only syntax that supports multiple graphs.
        if ( count( $objects ) == 1 && $options == self::ENCODE_AS_OBJECT )
            return $this->_encodeGraph( $objects[0], '', '' );

        // Otherwise ENCODE_AS_OBJECT_WITH_SCHEMA (default)
        return $this->_encodeAsObjectWithSchema( $objects );
    }

    /**
     * Encodes the given array of graphs, starting with a header
     * that includes the graph's attributes, followed by a "graphs"
     * property that includes the graphs.
     *
     * @param  array    $graphs the array of graphs to be encoded.
     *
     * @return  string          the JSON text that encodes the graph.
     */
    private function _encodeAsObjectWithSchema( &$graphs )
    {
        // Sample output:
        //
        // {
        //   "type": "json-graph",
        //   "graphs": [ ... ]
        // }
        //
        // Open
        // -------------------------------------------------
        $text   = "{\n";
        $indent = '  ';
        $indent2 = '    ';

        // Header
        // -------------------------------------------------
        $text .= "$indent\"type\": \"json-graph\",\n";


        // Graphs
        // -------------------------------------------------
        $text .= "$indent\"graphs\": [\n";
        $n = count( $graphs );
        for ( $i = 0; $i < $n; ++$i )
        {
            if ( $i != $n - 1 )
                $text .= $this->_encodeGraph( $graphs[$i], $indent2, ',' );
            else
                $text .= $this->_encodeGraph( $graphs[$i], $indent2, '' );
        }
        $text .= "$indent]\n";


        // Close
        // -------------------------------------------------
        $text .= "}\n";
        return $text;
    }

    /**
     * Encodes the given graph, indenting each line with the given string,
     * and ending the last graph with the given "comma" string.
     *
     * @param  Graph   $graph   the graph object to be encoded.
     *
     * @param  string  $indent  the text string to prepend to every line
     * of encoded text.
     *
     * @param  string  $comma   a comma or empty string to add after the
     * graph.
     */
    private function _encodeGraph( &$graph, $indent, $comma )
    {
        // The incoming graph cannot be a NULL. But this should never
        // happen since the calling code has already checked for this.
        // But, we can be paranoid anyway.
        // @codeCoverageIgnoreStart
        if ( $graph == NULL )
            return;
        // @codeCoverageIgnoreEnd

        // Sample output:
        //
        // {
        //   "label": "my graph",
        //   "metadata": {
        //     "longName": "long name",
        //     "description": "description",
        //     "whatever": "something"
        //   }
        //   "nodes": [ ... ],
        //   "edges": [ ... ]
        // }
        //
        // Open
        // -------------------------------------------------
        $text = "$indent{\n";
        $indent2 = $indent . '  ';
        $indent3 = $indent . '    ';

        $nodeIDs = $graph->getAllNodes( );
        $nNodes = count( $nodeIDs );

        $edgeIDs = $graph->getAllEdges( );
        $nEdges = count( $edgeIDs );


        // Header
        // -------------------------------------------------
        $name = $graph->getName( );
        if ( !empty( $name ) )
            $text .= "$indent2\"label\": \"$name\",\n";
        $text .= $this->_encodeMetadata( $graph->getAttributes( ), $indent2, ',' );



        // Nodes
        // -------------------------------------------------
        $text .= "$indent2\"nodes\": [\n";
        for ( $i = 0; $i < $nNodes; ++$i )
        {
            if ( $i != $nNodes - 1 )
                $text .= $this->_encodeNode( $graph, $nodeIDs[$i], $indent3, ',' );
            else
                $text .= $this->_encodeNode( $graph, $nodeIDs[$i], $indent3, '' );
        }
        $text .= "$indent2],\n";


        // Edges
        // -------------------------------------------------
        $text .= "$indent2\"edges\": [\n";
        for ( $i = 0; $i < $nEdges; ++$i )
        {
            if ( $i != $nEdges - 1 )
                $text .= $this->_encodeEdge( $graph, $edgeIDs[$i], $indent3, ',' );
            else
                $text .= $this->_encodeEdge( $graph, $edgeIDs[$i], $indent3, '' );
        }
        $text .= "$indent2]\n";


        // Close
        // -------------------------------------------------
        $text .= "$indent}$comma\n";
        return $text;
    }

    /**
     * Encodes the given node, indenting each line with the given string,
     * and ending the last node with the given "comma" string.
     *
     * @param  Graph   $graph   the graph object to be encoded.
     *
     * @param  integer $nodeID  the ID of the node to be encoded.
     *
     * @param  string  $indent  the text string to prepend to every line
     * of encoded text.
     *
     * @param  string  $comma   a comma or empty string to add after the
     * graph.
     */
    private function _encodeNode( &$graph, $nodeID, $indent, $comma )
    {
        // Sample output:
        //
        // {
        //   "label": "my node",
        //   "id": "123",
        //   "metadata": {
        //     "longName": "long name",
        //     "description": "description",
        //     "whatever": "something"
        //   }
        // }
        //
        // Open
        // -------------------------------------------------
        $text = "$indent{\n";
        $indent2 = "$indent  ";


        // Content
        // -------------------------------------------------
        $attr = $graph->getNodeAttributes( $nodeID );
        $name = $graph->getNodeName( $nodeID );
        if ( !empty( $name ) )
            $text .= "$indent2\"label\": \"$name\",\n";

        if ( isset( $attr['name'] ) )
            unset( $attr['name'] );       // Name already handled

        if ( count( $attr ) != 0 )
        {
            $text .= "$indent2\"id\": \"$nodeID\",\n";
            $text .= $this->_encodeMetadata( $attr, $indent2, '' );
        }
        else
            $text .= "$indent2\"id\": \"$nodeID\"\n";


        // Close
        // -------------------------------------------------
        $text .= "$indent}$comma\n";
        return $text;
    }

    /**
     * Encodes the given edge, indenting each line with the given string,
     * and ending the last edge with the given "comma" string.
     *
     * @param  Graph   $graph   the graph object to be encoded.
     *
     * @param  integer $edgeID  the ID of the edge to be encoded.
     *
     * @param  string  $indent  the text string to prepend to every line
     * of encoded text.
     *
     * @param  string  $comma   a comma or empty string to add after the
     * graph.
     */
    private function _encodeEdge( &$graph, $edgeID, $indent, $comma )
    {
        // Sample output:
        //
        // {
        //   "label": "my edge",
        //   "source": "1",
        //   "target": "2",
        //   "metadata": {
        //     "longName": "long name",
        //     "description": "description",
        //     "whatever": "something"
        //   }
        // }
        //
        // Open
        // -------------------------------------------------
        $text = "$indent{\n";
        $indent2 = "$indent  ";


        // Content
        // -------------------------------------------------
        $attr = $graph->getEdgeAttributes( $edgeID );
        $name = $graph->getEdgeName( $edgeID );
        if ( !empty( $name ) )
            $text .= "$indent2\"label\": \"$name\",\n";

        if ( isset( $attr['name'] ) )
            unset( $attr['name'] );         // Name already handled

        $nodeIDs = $graph->getEdgeNodes( $edgeID );
        $node1 = $nodeIDs[0];
        $node2 = $nodeIDs[1];

        $text .= "$indent2\"source\": \"$node1\",\n";
        if ( count( $attr ) != 0 )
        {
            $text .= "$indent2\"target\": \"$node2\",\n";
            $text .= $this->_encodeMetadata( $attr, $indent2, '' );
        }
        else
            $text .= "$indent2\"target\": \"$node2\"\n";


        // Close
        // -------------------------------------------------
        $text .= "$indent}$comma\n";
        return $text;
    }

    /**
     * Encodes the given attributes array as metadata, indenting each
     * line with the given string, and ending the last syntax with the
     * given "comma" string.
     *
     * @param  array $attributes the attributes to be encoded.
     *
     * @param  string  $indent  the text string to prepend to every line
     * of encoded text.
     *
     * @param  string  $comma   a comma or empty string to add after the
     * graph.
     */
    private function _encodeMetadata( $attributes, $indent, $comma )
    {
        // Sample output:
        //
        // "metadata": {
        //   "longName": "long name",
        //   "description": "description",
        //   "whatever": "something"
        // }
        //


        $keys = array_keys( $attributes );
        $n = count( $keys );

        if ( $n == 0 )
            return '';                      // No attributes


        // Open
        // -------------------------------------------------
        $text    = "$indent\"metadata\": {\n";
        $indent2 = "$indent  ";


        // Content
        // -------------------------------------------------
        for ( $i = 0; $i < $n; ++$i )
        {
            $key = $keys[$i];
            $value = $attributes[$key];

            if ( is_int( $value ) || is_float( $value ) || is_bool( $value ) )
                $text .= "$indent2\"$key\": $value";

            else if ( is_null( $value ) )
                $text .= "$indent2\"$key\": null";

            else if ( is_string( $value ) )
                $text .= "$indent2\"$key\": \"$value\"";

            else if ( is_object( $value ) || is_array( $value ) )
            {
                // Don't know what this is, so encode it blind.
                $text .= "$indent2\"$key\": " . json_encode( $value );
            }

            if ( $i != $n - 1 )
                $text .= ",\n";
            else
                $text .= "\n";

        }

        // Close
        // -------------------------------------------------
        $text .= "$indent}$comma\n";
        return $text;
    }
    // @}
}

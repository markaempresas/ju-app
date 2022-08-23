<?php
/**
 * @file
 * Defines Drupal\chart_suite\SDSC\StructuredData\Format\FormatException to report format
 * encode and decode problems.
 */

namespace Drupal\chart_suite\SDSC\StructuredData\Format;


/**
 * @class FormatException
 * FormatException describes an exception associated with format encode
 * or decode, such as for syntax errors and problems with content types
 * or organization.
 *
 *
 * @author  David R. Nadeau / University of California, San Diego
 *
 * @date    2/10/2016
 *
 * @since   0.0.1
 *
 * @version 0.0.1  Initial development.
 */
class FormatException
    extends \ErrorException
{
//----------------------------------------------------------------------
// Constructors & Destructors
//----------------------------------------------------------------------
    /**
     * @name Constructors
     */
    // @{
    /**
     * Constructs and returns a new exception object.
     *
     * @param string $message  the exception message
     *
     * @param int $code        the exception code
     *
     * @param int $severity    the severity level
     *
     * @param string $filename the filename where the exception was created
     *
     * @param int $lineno      the line where the exception was created
     *
     * @param Exception $previous the previous exception, if any
     */
    public function __construct(
        $message  = "",
        $code     = 0,
        $severity = 1,
        $filename = __FILE__,
        $lineno   = __LINE__,
        \Exception $previous = NULL )
    {
        parent::__construct( $message, $code, $severity,
            $filename, $lineno, $previous );
    }
    // @}

    /**
     * @name Destructors
     */
    // @{
    /**
     * Destroys a previously-constructed object.
     */
    public function __destruct( )
    {
        // Parent class has no __destruct method
    }
    // @}
}

<?php
/**
 * @file
 * Define the StructuredData API version and copyright, then include each
 * of the class definitions in dependency order, with base classes
 * first, followed by those that depend upon them.
 */
namespace Drupal\chart_suite\SDSC\StructuredData;





/**
 * Defines the StructuredData class to hold package globals that define the
 * name, version, author, and copyright message for the API.
 *
 *
 * @author	David R. Nadeau / University of California at San Diego
 *
 * @date    9/10/2018
 */
final class StructuredData
{
    /**
     * The name of the API.
     */
    const Name    = 'SDSC Structured Data API';

    /**
     * The current version of the API.
     */
    const Version = 'Version 1.0.1, September 24, 2018';

    /**
     * The author(s) of the API.
     */
    const Author  = 'David R. Nadeau / San Diego Supercomputer Center (SDSC)';

    /**
     * A copyright message for the API.
     */
    const Copyright = 'Copyright (c) Regents of the University of California';

    /**
     * A license message for the API.
     */
    const License = 'See https://opensource.org/licenses/BSD-2-Clause';
}
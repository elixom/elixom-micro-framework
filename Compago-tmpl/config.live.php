<?php
/**
 * @author Edwards
 * @copyright 2010
 */
unset($CFG);  // Ignore this line
global $CFG;  // This is necessary here for PHPUnit execution
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';       // only mysql
$CFG->dbhost    = 'localhost';   // eg localhost or db.isp.com
$CFG->dbname    = '';      // database name, eg moodle
$CFG->dbuser    = '';    // your database username
$CFG->dbpass    = '';    // your database password
$CFG->dbtimezone  =  '-5:00';
$CFG->timezone  =  'America/Jamaica';
$CFG->sessionKeyPrefix = 'APP';
date_default_timezone_set('America/Jamaica');

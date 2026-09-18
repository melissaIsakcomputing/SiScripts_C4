<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');
define("ENVIRONMENT","development");

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR') || define('HOUR', 3600);
defined('DAY') || define('DAY', 86400);
defined('WEEK') || define('WEEK', 604800);
defined('MONTH') || define('MONTH', 2_592_000);
defined('YEAR') || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS') || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR') || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG') || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE') || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS') || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE') || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN') || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code

const SureScriptURL = 'https://smr.surescripts.net';
const SureScriptDirectoryURL = 'https://dir.surescripts.net/directory/Directory6dot1/v6_1';

/* staging */
const ScriptExchange = 'https://smx.script.exchange/message';
/* production */
//const ScriptExchange = 'https://pmx.script.exchange/message';

const DataServer = "data-dev.sicompound.cloud";
const DataServerUsername = "icapi";
const DataServerPass = "1c4p1";
const DataFile = "SiScripts.fmp12";
const HostFile = "SiScripts.fmp12";
const HostUsername = "1c4p1";
const HostPass= "1c4p1";


define("SureScriptCA", dirname(__FILE__, 4) . "etc/pki/ca-trust/extracted/pem/tls-ca-bundle.pem");
define("SureScriptPem", dirname(__FILE__, 2) . "/ssl/cert/certkey.pem");


const Pharmacies = [
    '1071492' => 'fmserver.infuserveamerica.com',
    '5750662' => 'magnum.sicompound.cloud',
    '4237435' => 'bioclinical.sicompound.cloud',
    '1617169' => 'olivetree.sicompounding.cloud',
    '5712876' => 'pharmalabs.sicompounding.cloud',
    '5707104' => 'baylife.sicompounding.cloud',
    '5685271' => 'generation.sicompounding.cloud',
    '2991366' => 'sierrafamily.sicompounding.cloud',
    '5758492' => 'stluke.sicompounding.cloud',
    '5845550' => 'uscfactory.sicompounding.cloud',
    '0358021' => 'prescott.sicompounding.cloud',
    '0356065' => 'pottershouse.sicompounding.cloud',
    '3690939' => 'impact.sicompounding.cloud',
    '5925396' => 'texas.sicompounding.cloud',
    '1311276' => 'peachy.sicompounding.cloud',
    '4451667' => 'ascend.sicompounding.cloud',
    '2010116' => 'classic.sicompounding.cloud',
    '0325351' => 'rxformulations.sicompounding.cloud',
    '5929279' => 'key.sicompounding.cloud',
    '0628581' => 'alpine.sicompounding.cloud',
    '0363945' => 'synergis.sicompounding.cloud',
    '4442810' => 'hillstone.sicompounding.cloud',
    '4454613' => 'hillstone.sicompounding.cloud',
    '0302389' => 'customedico.sicompounding.cloud',
    '5718006' => 'olympiac.sicompounding.cloud',
    '5744001' => 'olympiam.sicompounding.cloud',
    '5938646' => 'ivy.sicompounding.cloud',
    '5760764' => 'sollab.sicompounding.cloud',
    '4300226' => 'rxpsi.sicompounding.cloud',
    '5802714' => 'towntotal.sicompounding.cloud',
    '5745407' => 'everwell.sicompounding.cloud',
    '4402195' => 'solutions.sicompounding.cloud',
    '3159541' => 'rpc2b.sicompound.cloud',
];


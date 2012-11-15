<?php
/* A function call to clear active patient in session
 * @author       visolve <services@visolve.com>
 * @copyright    visolve 2012
 * @license      http://opensource.org/licenses/gpl-license.php GNU General Public License, Version 3 
 */
$fake_register_globals=false;
$sanitize_all_escapes=true;
require_once("../../interface/globals.php");
require_once("../pid.inc");
if(($_POST['func']=="unset_pid"))
{
	setpid(0);
}
?> 

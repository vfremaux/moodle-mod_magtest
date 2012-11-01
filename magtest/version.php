<?php // $Id: version.php,v 1.1 2011-09-16 09:23:32 vf Exp $
/**
 * Code fragment to define the version of magtest
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author 
 * @version $Id: version.php,v 1.1 2011-09-16 09:23:32 vf Exp $
 * @package magtest
 **/

$module->version  = 2008053100;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2007101532;  // Requires this Moodle version
$module->component = 'mod_magtest';   // Full name of the plugin (used for diagnostics)
$module->cron     = 0;           // Period for cron to check this module (secs)
$module->maturity = MATURITY_RC;
$module->release = '1.9.0 (Build 2008053100)';


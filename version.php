<?php
/**
 * Code fragment to define the version of magtest
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author Valery Fremaux
 * @version $Id: version.php,v 1.2 2012-11-01 18:05:14 vf Exp $
 * @package mod-magtest
 */

$plugin->version  = 2014012802;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2013111800;  // Requires this Moodle version
$plugin->cron     = 0;           // Period for cron to check this module (secs)
$plugin->component = 'mod_magtest';   // Full name of the plugin (used for diagnostics)
$plugin->maturity = MATURITY_RC;
$plugin->release = '2.6.0 (Build 2014012802)';

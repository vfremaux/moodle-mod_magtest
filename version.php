<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Code fragment to define the version of magtest
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author Valery Fremaux
 * @package mod_magtest
 * @category mod
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2016060100;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2014110400;  // Requires this Moodle version
$plugin->cron     = 0;           // Period for cron to check this module (secs)
$plugin->component = 'mod_magtest';   // Full name of the plugin (used for diagnostics)
$plugin->maturity = MATURITY_RC;
$plugin->release = '2.8.0 (Build 2016060100)';



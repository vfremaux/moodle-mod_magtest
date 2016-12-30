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
 * @package     mod_magtest
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @author      Etienne Roze
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 2005 Valery Fremaux (http://www.mylearningfactory.com)
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $key = 'magtest/showmymoodle';
    $label = get_string('configshowmymoodle', 'mod_magtest');
    $desc = get_string('configshowmymoodledesc', 'mod_magtest');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1));
}
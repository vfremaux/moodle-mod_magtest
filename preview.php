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
 * Allows previewing the test before playing it
 *
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

if (!has_capability('mod/magtest:manage', $context)) {
    die('You cannot see this page with your role');
}

$renderer = $PAGE->get_renderer('mod_magtest');

echo $OUTPUT->heading(get_string('preview', 'magtest'));

// Categories.

echo $OUTPUT->heading(get_string('categories', 'magtest'), 3);
$categories = magtest_get_categories($magtest->id);
echo $renderer->categories_preview($categories);

// Get questions.

echo $OUTPUT->heading(get_string('questions', 'magtest'), 3);
$questions = magtest_get_questions($magtest->id);
echo $renderer->preview($questions, $magtest);


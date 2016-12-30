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
 * Allows managing categories
 *
 * @package     mod_magtest
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @author      Etienne Roze
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 2005 Valery Fremaux (http://www.mylearningfactory.com)
 * @see         categories.controller.php for associated controller.
 */

defined('MOODLE_INTERNAL') || die();

if ($action) {
    require($CFG->dirroot.'/mod/magtest/categories.controller.php');
}

$categories = magtest_get_categories($magtest->id);
echo $OUTPUT->heading('categories');
echo "<center>";
echo $OUTPUT->box_start();

if (!empty($categories)) {
    $symbolstr = get_string('symbol', 'magtest');
    $namestr = get_string('name');
    $descriptionstr = get_string('description');
    $resultstr = get_string('categoryresult', 'magtest');
    $commandstr = get_string('commands', 'magtest');
    $table = new html_table();

    $table->head = array(
        "<b>$symbolstr</b>",
        "<b>$namestr</b>",
        "<b>$descriptionstr</b>",
        "<b>$resultstr</b>",
        "<b>$commandstr</b>"
    );

    $table->align = array(
        'center',
        'left',
        'left',
        'left',
        'left'
    );

    $table->size = array(
        '5%',
        '15%',
        '30%',
        '30%',
        '15%'
    );

$table->width = '100%';

foreach ($categories as $category) {
    $commands = '<div class="categorycommands">';
    $params = array('id' => $cm->id, 'catid' => $category->id);
    $cmdurl = new moodle_url('/mod/magtest/editcategories.php', $params);
    $commands .= '<a href="'.$cmdurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'"</a>';
    $params = array('id' => $cm->id, 'what' => 'deletecategory', 'catid' => $category->id);
    $cmdurl = new moodle_url('/mod/magtest/view.php', $params);
    $commands .=' <a id="delete" href="'.$cmdurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';

    if ($category->sortorder > 1) {
        $params = array('id' => $cm->id, 'view' => 'categories', 'what' => 'raisecategory', 'catid' => $category->id);
        $cmdurl = new moodle_url('/mod/magtest/view.php', $params);
        $commands .= '&nbsp;<a href="'.$cmdurl.'"><img src="'.$OUTPUT->pix_url('t/up').'"></a>';
    } else {
        $commands.='&nbsp;<img src="'.$OUTPUT->pix_url('up_shadow', 'magtest').'">';
    }

    if ($category->sortorder < count($categories)) {
        $params = array('id' => $cm->id, 'view' => 'categories', 'what' => 'lowercategory', 'catid' => $category->id);
        $cmdurl = new moodle_url('/mod/magtest/view.php', $params);
        $commands .= '&nbsp;<a href="'.$cmdurl.'"><img src="'.$OUTPUT->pix_url('t/down').'"></a>';
    } else {
        $commands.='&nbsp;<img src="'.$OUTPUT->pix_url('down_shadow', 'magtest').'">';
    }

    $commands .= '</div>';
    $symbolurl = magtest_get_symbols_baseurl($magtest) . $category->symbol;
    $symbolimage = "<img src=\"{$symbolurl}\" />";
    $category->format = 1;

    $table->data[] = array(
            $symbolimage,
            format_string($category->name),
            format_string(format_text($category->description, $category->format)),
            format_string(format_text($category->result, $category->format)),
            $commands
        );
    }

    echo html_writer::table($table);
} else {
    print_string('nocategories', 'magtest');
}

echo $OUTPUT->box_end();

$params = array('id' => $cm->id, 'catid' => -1, 'what' => 'addcategories', 'howmany' => 1);

echo '<p>';
echo $OUTPUT->single_button(new moodle_url('/mod/magtest/editcategories.php', $params), get_string('addone', 'magtest'), 'get');
$options['howmany'] = 3;
echo $OUTPUT->single_button(new moodle_url('/mod/magtest/editcategories.php', $params), get_string('addthree', 'magtest'), 'get');
echo '</center>';
echo '</p>';

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

class magtest_renderer extends core_renderer {

    public function __construct(){
        global $PAGE;
        parent::__construct($PAGE, null);
    }

    /**
     * Implementation of special help rendering.
     * @param help_icon $helpicon
     * @return string HTML fragment
     */
    public function answer_help_icon($answerid) {
        global $CFG;

        // first get the help image icon
        $src = $this->pix_url('help');

        $title = get_string('helper', 'magtest');
        $alt = get_string('helper', 'magtest');

        $attributes = array('src' => $src, 'alt' => $alt, 'class' => 'iconhelp');
        $output = html_writer::empty_tag('img', $attributes);

        // now create the link around it - we need https on loginhttps pages
        $url = new moodle_url($CFG->httpswwwroot.'/mod/magtest/help.php', array('answerid' => $answerid));

        $attributes = array('href' => $url, 'title' => $title);
        $id = html_writer::random_id('helpicon');
        $attributes['id'] = $id;
        $output = html_writer::tag('a', $output, $attributes);
        
        $this->page->requires->js_init_call('M.util.help_icon.add', array(array('id' => $id, 'url' => $url->out(false))));

        // And finally span.
        return html_writer::tag('span', $output, array('class' => 'helplink'));
    }

    public function print_magtest_quiz(&$questions, &$categories, $context, $return = true) {

        $str = '';

        foreach ($questions as $question) {
            $str .= '<tr align="top">';
              $str .= '<td width="20%" align="right"><b>'.get_string('question', 'magtest').':</b></td>';
              $str .= '<td align="left" colspan="2">';
            $str .= $question->questiontext = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php',$context->id, 'mod_magtest', 'question', 0);
            $question->questiontext = format_string($question->questiontext);
    
            $str .= '</td>';
            $str .= '</tr>';
            $i = 0;
            shuffle($question->answers);

            foreach($question->answers as $answer) {
                $str .= '<tr align="middle">';
                $str .= '<td width="20%" align="right">&nbsp;</td>';
                $str .= '<td align="left" class="magtest-answerline">';
                $catsymbol = $categories[$answer->categoryid]->symbol;
                $symbolurl = magtest_get_symbols_baseurl($magtest).$catsymbol;
                $symbolimage = "<img class=\"magtest-qsymbol\" src=\"{$symbolurl}\" align=\"bottom\" />&nbsp;&nbsp;";
                $str .= $symbolimage;
                $answer->answertext  = file_rewrite_pluginfile_urls( $answer->answertext, 'pluginfile.php',$context->id, 'mod_magtest', 'questionanswer', $answer->id);            
                $answertext = preg_replace('/^<p>(.*)<\/p>$/', '\\1', $answer->answertext);
                $str .= ($answertext).' ';
                if (!empty($answer->helper)) {
                    $str .= $this->answer_help_icon($answer->id);
                }
                $str .= '<br/>';
                $str .= '</td>';
                $str .= '<td class="magtest-answerline">';
                $str .= '<input type="radio" name="answer'.$question->id.'" value="'.$answer->id.'" /><br/> ';
                $str .= '</td>';
                $str .= '</tr>';
            }
        }
        
        if ($return) {
            return $str;
        }
        echo $str;
    }

    function print_magtest_singlechoice(&$questions, $context, $return = true) {

        $str = '';

        foreach ($questions as $question) {
            $str .= '<tr align="top">';
            $str .= '<td align="left">';
            $str .= $question->questiontext = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php',$context->id, 'mod_magtest', 'question', 0);
            $question->questiontext = format_string($question->questiontext);    
            $str .= '</td>';
            $i = 0;

            $str .= '<td class="magtest-answerline">';
            $str .= '<input type="checkbox" name="answers[]" value="'.$question->id.'" />';
            $str .= '<input type="hidden" name="qids[]" value="'.$question->id.'" />';
            $str .= '</td>';
            $str .= '</tr>';
        }
        
        if ($return) {
            return $str;
        }
        echo $str;
    }
}

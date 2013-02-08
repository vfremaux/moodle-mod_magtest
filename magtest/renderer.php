<?php

class magtest_renderer extends core_renderer{
	
	function __construct(){
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

        // and finally span
        return html_writer::tag('span', $output, array('class' => 'helplink'));
    }

}

?>
<?php

/**
* @package magtest
* @category mods
*
*/

require_once $CFG->libdir.'/formslib.php';

class magtest_categories_form extends moodleform {

	function magtest_categories_form($magtest) { 
		parent::moodleform(null, (array)$magtest); 
	}

	function definition(){
		global $CFG, $COURSE, $cm, $magtest;

        $magtestid = $this->_customdata['id'];
	}

    function definition_after_data(){
        global $CFG, $COURSE, $cm, $magtest;

        $magtestid = $this->_customdata['id'];
        $mform    = &$this->_form;

        if (isset($mform->_submitValues['submitbutton'])){
            if (isset($mform->_submitValues['category'])){
	            //if ($data = $this->get_data()) {
				$cats = $mform->_submitValues['category'];
	
	            if (!empty($cats)){
	                foreach ($cats as $category){
	                    $DB->update_record('magtest_category', (object)$category);
	                }
	            }
	        }
	
	        if (!strcmp($mform->_submitValues['submitbutton'], get_string('addcategory', 'magtest'))){
	            $newcategory->categorytext = '';
	            $newcategory->magtestid = $magtest->id;
	            $DB->insert_record('magtest_category', $newcategory);
	        } elseif (!strcmp($mform->_submitValues['submitbutton'], get_string('delcategory', 'magtest'))) {
	        	$max_cat = $DB->get_record_select('magtest_category',' categorytext = \'\' and categoryshortname = \'\' and magtestid = '. $magtestid, 'max(id) as m ');
	
	            if (isset($max_cat->m)){
	                // Does a question exist in this category ? If not we can delete it.
	                if (!$DB->record_exists('magtest_question', array('categoryid' => $max_cat->m))){
	                    $DB->delete_records_select('magtest_category', 'magtestid = ' . $magtestid . ' and id = ' . $max_cat->m);
	                }
	            }
	        }
	    }
	
	    $categories = get_magtest_categories($magtestid);
	    //-------------------------------------------------------------------------------
	    $mform->addElement('header', 'general', get_string('categories', 'magtest'));
	    $mform->addElement('hidden', 'id', $cm->id);
	    $mform->addElement('hidden', 'view', 'categories');
	    $i = 0;
	
	    if (!empty($categories)){
	        foreach ($categories as $category){
	            $i++;
	            $mform->addElement('hidden', 'category['.$category->id.'][id]', $category->id);
	            $mform->addElement('text','category['.$category->id.'][categoryshortname]',get_string('categoryshortname', 'magtest') . " $i",array('size' => '10'));
	            $mform->setType('category['.$category->id.'][categoryshortname]', PARAM_CLEANHTML);
	            $mform->setDefault('category['.$category->id.'][categoryshortname]', $category->categoryshortname);
	            $mform->addElement('text','category['.$category->id.'][categorytext]',get_string('category', 'magtest') . " $i",array('size' => '64'));
	            $mform->setType('category['.$category->id.'][categorytext]', PARAM_CLEANHTML);
	            $mform->setDefault('category['.$category->id.'][categorytext]', $category->categorytext);
	            //$mform->addRule('category['.$category->id.'][categorytext]', null, 'required', null, 'client');
	        }
	
	        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('save'));
	        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('delcategory', 'magtest'));
		} else {
	        $mform->addElement('static', 'message', get_string('nocategories', 'magtest'));
		}

        $buttonarray[]=&$mform->createElement('submit', 'submitbutton', get_string('addcategory', 'magtest'));
        $buttonarray[]=&$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
        }
    }

    $categories_form = new magtest_categories_form($magtest);

    if ($categories_form->is_cancelled()) {
    	redirect ($CFG->wwwroot . '/mod/magtest/view.php?id=' . $cm->id);
    }

    $categories_form->display();
?>
<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_nlrsbook_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $PAGE, $DB, $USER;

        $mform = $this->_form;

        $PAGE->requires->js_call_amd('nlrsbook/modal_search_handle', 'init');

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'nlrsbook_id', 'nlrsbook_id');
        $mform->setType('nlrsbook_id', PARAM_TEXT);
        $mform->addRule('nlrsbook_id', null, 'required', null, 'client');

        $mform->addElement('text', 'name', 'name');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('button', 'modal_show_button', get_string('button_desc', 'mod_nlrsbook'));
        $mform->addHelpButton('modal_show_button', 'nlrsbookbutton', 'mod_nlrsbook');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_nlrsbook_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $PAGE, $DB, $USER;
        $mform = $this->_form;

        // 'ecsbEpsEfed_': 'https://new.nlrs.ru/ecsb-example/dist/script'
        // 'ecsbEpsEfed_': 'http://127.0.0.1:8000/dist/script'
        $requirejs = "require.config(
            {
                paths: {
                    'ecsbEpsEfed_': 'https://new.nlrs.ru/ecsb-example/dist/script'
                },
                shim: {
                    'ecsbEpsEfed_': {exports: 'ecsbEpsEfed'}
                },
                attributes: {
                    'data-id': 'ecsb-eps-efed-script',
                    'data-mode': 'edu_select_to_attach',
                    'data-partner-id': '1',
                    'data-eps-search-results-url': '/search',
                    'data-efed-viewer-url': '/open',
                    'data-efed-viewer-url-book-id-placement': 'path',
                    'data-ui-primary-color': '#0f6cbf'
                },
                onNodeCreated: function(node, config, name, url){
                    if(config.attributes){
                      Object.keys(config.attributes).forEach(attribute => {
                        node.setAttribute(attribute, config.attributes[attribute]);
                      });
                    }
                  }
            }
        )";
        $PAGE->requires->js_amd_inline($requirejs);

        $PAGE->requires->js_call_amd('mod_nlrsbook/modal_search_handle', 'init');

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
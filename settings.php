<?php

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('nlrsbook/org_token', get_string('org_token', 'mod_nlrsbook'), "", null));
}
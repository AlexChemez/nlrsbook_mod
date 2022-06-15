<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_nlrsbook_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    return true;
}

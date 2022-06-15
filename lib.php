<?php

defined('MOODLE_INTERNAL') || die();

function nlrsbook_add_instance($moduleinstance, $mform = null)
{
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('nlrsbook', $moduleinstance);

    return $id;
}

function nlrsbook_update_instance($moduleinstance, $mform = null)
{
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('nlrsbook', $moduleinstance);
}

function nlrsbook_delete_instance($id)
{
    global $DB;

    $exists = $DB->get_record('nlrsbook', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('nlrsbook', array('id' => $id));

    return true;
}

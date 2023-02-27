<?php

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $CFG;
global $DB;

require_once($CFG->dirroot . "/blocks/nlrsbook_auth/Query.php");

use App\Querys\Query;

$id = optional_param('id', 0, PARAM_INT);
$i = optional_param('i', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('nlrsbook', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('nlrsbook', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($i) {
    $moduleinstance = $DB->get_record('nlrsbook', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('nlrsbook', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_nlrsbook'));
}

require_login($course, true, $cm);

$nlrsbook_id = $moduleinstance->nlrsbook_id;

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/nlrsbook/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('standard');

$setting = get_config('nlrsbook_auth', 'org_private_key'); // Секретный ключ организации
$auth_msg = file_get_contents($CFG->dirroot . "/blocks/nlrsbook_auth/message/auth.php");
$setting_msg = file_get_contents($CFG->dirroot . "/blocks/nlrsbook_auth/message/setting.php");
        
if ($setting) {
    if (Query::getToken()) {
        $bookUrl = Query::getUrl("online2/${nlrsbook_id}");
        $bookdata = Query::getBook($nlrsbook_id);

        if ($bookdata['pubPlace']) { 
            $pubPlace = '<p><b>Место издания:</b> ' . $bookdata['pubPlace'] . '</p>';
        } else {
            $pubPlace = null;
        }
        if ($bookdata['publisher']) { 
            $publisher = '<p><b>Издательство:</b> ' . $bookdata['publisher'] . '</p>';
        } else {
            $publisher = null;
        }
        if ($bookdata['pubDate']) { 
            $pubDate = '<p><b>Год:</b> ' . $bookdata['pubDate'] . '</p>';
        } else {
            $pubDate = null;
        }
        if ($bookdata['innerPagesCount']) { 
            $innerPagesCount = '<p><b>Количество страниц:</b> ' . $bookdata['innerPagesCount'] . '</p>';
        } else {
            $innerPagesCount = null;
        }
        if ($bookdata['annotation']) { 
            $annotation = '<p><b>Аннотация:</b></p><p>' . $bookdata['annotation'] . '</p>';
        } else {
            $annotation = null;
        }
        if ($bookdata['shortBibl']) { 
            $shortBibl = '<p><b>Библиографическая запись:</b></p><p>' . $bookdata['shortBibl'] . '</p>';
        } else {
            $shortBibl = null;
        }
        $js = file_get_contents($CFG->dirroot . "/mod/nlrsbook/js/nlrsbook_shelf.js");

        $template = '
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <div class="main-inner">
            <div class="row">
                <div class="col-sm-2 mb-4">
                    <img class="rounded shadow" src="' . $bookdata['coverThumbImage']['url'] . '" width="100%">
                    <a class="mt-3 btn btn-primary btn-block" href="'.$bookUrl.'" target="_blank">Читать</a>
                    <a class="mt-2 btn btn-primary btn-block" id="shelf" data-id="'.$nlrsbook_id.'"></a>
                </div>
                <div class="col-sm-10">
                    '.$pubPlace.''.$publisher.''.$pubDate.''.$innerPagesCount.''.$annotation.''.$shortBibl.' 
                </div>
            </div>
        </div>
        <script type="text/javascript">'.$js.'</script>';
    } else {
        $template = $auth_msg;
    }
} else {
    $template = $setting_msg;
}

echo $OUTPUT->header();

echo $template;

echo $OUTPUT->footer();

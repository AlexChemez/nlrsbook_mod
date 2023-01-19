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

$seamlessAuthUserId = $USER->id; // Идентицикатор пользователя
$seamlessAuthOrgId = 1; // Идентификатор организации

$secret = get_config('nlrsbook_auth', 'org_private_key'); // Секретный ключ организации
$seamlessAuthSignature = Query::generateServerApiRequestSignature([
    'orgId' => $seamlessAuthOrgId,
    'userIdInEduPlatform' => $seamlessAuthUserId,
], $secret);

$getToken = Query::getToken($seamlessAuthUserId, $seamlessAuthSignature); // получение токена пользователя
$nlrsUserId = Query::getSub($USER->id); // TODO: получать из токена

$seamlessAuthSignatureBase64 = Query::generateServerApiRequestSignatureBase64([
    "orgId" => $seamlessAuthOrgId,
    "userIdInEduPlatform" => $nlrsUserId,
], $secret);

$bookUrl = "https://e.nlrs.ru/seamless-auth-redirect?seamlessAuthOrgId=${seamlessAuthOrgId}&seamlessAuthUserId=${nlrsUserId}&seamlessAuthSignature=${seamlessAuthSignatureBase64}&override_redirect=online2/${nlrsbook_id}";

$modulecontext = context_module::instance($cm->id);
$bookdata = Query::getBook($nlrsbook_id, $getToken);
$PAGE->set_url('/mod/nlrsbook/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('standard');

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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<div class="main-inner">
<div class="row">
<div class="col-sm-3 mb-4">
<img class="rounded shadow" src="' . $bookdata['coverThumbImage']['url'] . '" width="100%">
<a class="mt-3 btn btn-primary btn-block" href="'.$bookUrl.'" target="_blank">Читать</a>
<a class="mt-2 btn btn-primary btn-block" id="shelf" data-id="'.$nlrsbook_id.'"></a>
</div>
<div class="col-sm-9">
    '.$pubPlace.''.$publisher.''.$pubDate.''.$innerPagesCount.''.$annotation.''.$shortBibl.' 
</div>
</div
</div>
<script type="text/javascript">'.$js.'</script>';

echo $OUTPUT->header();

echo $template;

echo $OUTPUT->footer();

<?php

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $CFG;
global $DB;

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

$host = 'https://e.nlrs.ru/graphql';
$user_id = $USER->id;
$instance = $DB->get_record('nlrsbook_shelf', array('user_id' => $USER->id), '*', IGNORE_MISSING );

$nlrsbook_id = $moduleinstance->nlrsbook_id;

if ($instance->token) {
    $token = $instance->token;
} else {
    $getToken = checkToken($user_id, $host);
    $row = new stdClass();
    $row->user_id = $user_id;
    $row->token = $getToken;
    $row->datetime = '1';
    $DB->insert_record('nlrsbook_shelf', $row);
    $token = $getToken;
}

function checkToken($user_id, $host) {
    $query = 'mutation {
      eduCheckIfLinkedNlrsAccountExistsAndGetToken(
        input: { 
            orgId: 1, 
            userIdInEduPlatform: "'.$user_id.'" 
        }
      ) {
        token
      }
    }';

    $data = array ('query' => $query);
    $data = http_build_query($data);

    $options = array(
      'http' => array(
        'method'  => 'POST',  
        'content' => $data
      )
    );

    $context  = stream_context_create($options);
    $getContents = file_get_contents(sprintf($host), false, $context);
    $json = json_decode($getContents, true);
    if ($getContents === FALSE) { }
    return $json['data']['eduCheckIfLinkedNlrsAccountExistsAndGetToken']['token'];
}

function getShelf($nlrsbook_id, $token) 
{
    $query = '{ 
        book(id: '.$nlrsbook_id.') {
          id
          isOnShelf
          access
          coverThumbImage {
            url
            width
            height
          }
          title
          authors {
            id
            fullName
          }
          annotation
          shortBibl
          innerPagesCount
          releaseDate
          serialN
          editionN
          reprintInfo
          seriesName
          num
          multimediatype {
            id
            title
          }
          doctype {
            id
            title
          }
          videotype
          multimediatypeTitle
          doctypeTitle
          scitypeTitle
          genreTitle
          langsTitles
          keywordsPub
          annotationParallel
          bibliographyNotes
          resumeReferat
          userNote
          toc
          udk
          bbk
          grnti
          isbn
          issn
          eissn
          doi
          pubPlace
          publisher
          pubDate
      }
    }';

    $data = array ('query' => $query);
    $data = http_build_query($data);

    $options = array(
      'http' => array(
        'header'  => sprintf("Authorization: Bearer %s", $token),
        'method'  => 'POST',  
        'content' => $data
      )
    );

    $context  = stream_context_create($options);
    $getContents = file_get_contents(sprintf('https://e.nlrs.ru/graphql'), false, $context);
    $json = json_decode($getContents, true);
    if ($getContents === FALSE) { /* Handle error */ }
    return $json['data']['book'];
}

function addBookToShelf($book_id, $token) {
    $query = 'mutation {
      addBookToShelf(bookId: '.$book_id.') {
        title
        is_on_shelf
      }
    }';

    $data = array ('query' => $query);
    $data = http_build_query($data);

    $options = array(
      'http' => array(
        'header'  => sprintf("Authorization: Bearer %s", $token),
        'method'  => 'POST',  
        'content' => $data
      )
    );

    $context  = stream_context_create($options);
    $getContents = file_get_contents(sprintf('https://e.nlrs.ru/graphql'), false, $context);
    $json = json_decode($getContents, true);
    if ($getContents === FALSE) { }
    return $json;
}

function removeBookToShelf($book_id, $token) {
    $query = 'mutation {
      removeBookFromShelf(bookId: '.$book_id.') {
        title
        is_on_shelf
      }
    }';

    $data = array ('query' => $query);
    $data = http_build_query($data);

    $options = array(
      'http' => array(
        'header'  => sprintf("Authorization: Bearer %s", $token),
        'method'  => 'POST',  
        'content' => $data
      )
    );

    $context  = stream_context_create($options);
    $getContents = file_get_contents(sprintf('https://e.nlrs.ru/graphql'), false, $context);
    $json = json_decode($getContents, true);
    if ($getContents === FALSE) { }
    return $json;
};

$modulecontext = context_module::instance($cm->id);
$bookdata = getShelf($nlrsbook_id, $token);
$PAGE->set_url('/mod/nlrsbook/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

if ($bookdata['title']) { 
    $title = '<h4 class="mb-3">' . $bookdata['title'] . '</h4>';
} else {
    $title = null;
}
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

$template = '<div class="row">
<div class="col-sm-2 mb-4">
<img class="rounded shadow" src="' . $bookdata['coverThumbImage']['url'] . '" width="100%">
<a class="mt-3 btn btn-primary btn-block" href="https://e.nlrs.ru/online2/'.$nlrsbook_id.'" target="_blank">Читать</a>
</div>
<div class="col-sm-10">
    '.$title.''.$pubPlace.''.$publisher.''.$pubDate.''.$innerPagesCount.''.$annotation.''.$shortBibl.' 
</div>
</div>';

echo $OUTPUT->header();

echo $template;

echo $OUTPUT->footer();
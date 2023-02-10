<?php

define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');

require_once($CFG->dirroot . "/blocks/nlrsbook_auth/Query.php");

use App\Querys\Query;

$book_id = optional_param('book_id', null, PARAM_INT);
$shelf = optional_param('shelf', null, PARAM_INT);

if ($shelf == 1) {
    Query::addBookToShelf($book_id);
}
if ($shelf == 2) {
    Query::removeBookToShelf($book_id);
}

$getBook = Query::getBook($book_id); // получение токена пользователя

if ($getBook['isOnShelf'] == true) {
    $shelfBtnText = 'Убрать из полки';
} else {
    $shelfBtnText = 'Добавить на полку';
}

echo json_encode(['shelf' => $shelfBtnText, 'isOnShelf' => $getBook['is_on_shelf']]);
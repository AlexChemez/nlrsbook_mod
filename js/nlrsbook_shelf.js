$(document).ready(function () {
    $id = $("#shelf").data('id');
    send_request_nlrsbook($id, null);
});

function send_request_nlrsbook(book_id, shelf) {
    $.ajax({
        url: M.cfg.wwwroot + "/mod/nlrsbook/ajax.php?book_id=" + book_id + "&shelf=" + shelf,
    }).done(function (data) {
        console.log(data.isOnShelf);
        $("#shelf").unbind("click").click(function () {
            if(data.isOnShelf == true) {
                send_request_nlrsbook($(this).data('id'), 2);
            } else {
                send_request_nlrsbook($(this).data('id'), 1);
            }
        });
        $("#shelf").html(data.shelf);
    });
}
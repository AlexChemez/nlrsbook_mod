define(["exports", "jquery", "core/modal_factory", "core/modal_events", "ecsbEpsEfed_"],
    function (exports, $, ModalFactory, ModalEvents, ecsbEpsEfed) {
        return {
            init: function () {
                var trigger = $('#id_modal_show_button');
                ModalFactory.create({
                    title: 'Выберите документ:',
                    body: '',
                    footer: '',
                }, trigger)
                    .then(function (modal) {
                        $(modal.getRoot()).attr('id', 'nlrsbook-modal');
                        $(modal.getRoot()).find('.modal-dialog').css('max-width', '1500px');
                        $(modal.getRoot()).find('.modal-body').css('height', '770px');
                        $(modal.getRoot()).find('.modal-body').css('overflow-y', 'auto');
                        $(modal.getRoot()).find('.modal-body').html('');

                        modal.show();

                        $(modal.getRoot()).find('.modal-body').attr('id', 'ecsb-eps-container');
                        ecsbEpsEfed.renderSearchUI();
                    });
            }
        };
    });
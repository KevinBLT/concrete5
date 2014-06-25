<div class="preview-frame-container">
    <iframe
        style="display:none"
        src="<?= URL::to('/ccm/system/panels/page/preview_as_user/render') . '?&cID=' . Request::request('cID') ?>"
        data-src="<?= URL::to('/ccm/system/panels/page/preview_as_user/render') ?>">></iframe>
    <div class="cover"></div>
    <div class="loader">
        <div class="icon">
            <i class="fa fa-cog fa-spin"></i>
        </div>
    </div>
</div>
<script type="application/javascript">
    (function (window, $, _) {
        'use strict';

        var container = $('div.preview-frame-container'),
            frame = container.children('iframe'),
            loader = container.children('div.loader');

        Concrete.event.bind('PanelOpenDetail', function(e) {
            Concrete.event.unsubscribe(e);

            frame.load(function() {
                _.defer(function() {
                    var frame_elem = frame.get(0),
                        frame_window = frame_elem.contentWindow;
                    frame.height($(frame_window.document).height()).width('100%');
                });
                frame.fadeIn();
                loader.fadeOut();
            });

        });

        function handleChange() {

            var guest_button = form.find('button.guest-button'),
                user_input = form.find('input.custom-user'),
                date = form.find('input[type="datetime"]').val(),
                time = form.find('select.hour').val() + ':' + form.find('select.minute').val(),
                portion = form.find('select.ampm').val(),
                now = [date, time, portion].join(' '),
                now_string;

            try {
                now_string = (new Date(now)).toISOString();
            } catch (e) {
                now_string = (new Date()).toISOString();
            }

            var query = {
                cID: CCM_CID,
                date: now_string,
                customUser: guest_button.hasClass('active') ? 0 : user_input.val()
            };

            var src = frame.data('src') + "?" + $.param(query);

            loader.fadeIn(250, function() {
                frame.attr('src', src);
            });
        };
        var form = $('form.preview-panel-form');
        form.change(function() {
            handleChange();
        });
        form.find('button').click(function() {
            handleChange();
        });
        form.find('input').keydown(_.debounce(function() {
            handleChange();
        }, 1000));

    }(window, jQuery, _));
</script>

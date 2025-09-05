// اجرا در صفحات ادمین
jQuery(function ($) {
  if (!$('#avp_video_id').length) return;

  var frame;

  $(document).on('click', '#avp_upload_video_btn', function (e) {
    e.preventDefault();

    if (frame) { frame.open(); return; }

    frame = wp.media({
      title: 'انتخاب یا آپلود ویدیو',
      button: { text: 'استفاده از این ویدیو' },
      library: { type: 'video' },
      multiple: false
    });

    frame.on('select', function () {
      var att = frame.state().get('selection').first().toJSON();
      $('#avp_video_id').val(att.id);
      $('#avp_video_preview').html('<video src="' + att.url + '" controls style="max-width:100%;height:auto;"></video>');
      $('#avp_remove_video_btn').show();
    });

    frame.open();
  });

  $(document).on('click', '#avp_remove_video_btn', function () {
    $('#avp_video_id').val('');
    $('#avp_video_preview').html('');
    $(this).hide();
  });
});

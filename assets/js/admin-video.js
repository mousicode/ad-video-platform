// اجرا در صفحات ادمین
jQuery(function ($) {
  // اگر متاباکس ما وجود ندارد، کاری نکن
  if (!$('#avp_video_url').length) return;

  var frame;

  // کلیک روی دکمه انتخاب
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
      $('#avp_video_url').val(att.url);
      $('#avp_remove_video_btn').show();
      // برای اطمینان، یک تریگر تغییر هم بزن
      $('#avp_video_url').trigger('change');
      console.log('AVP: video selected ->', att.url);
    });

    frame.open();
  });

  // حذف
  $(document).on('click', '#avp_remove_video_btn', function () {
    $('#avp_video_url').val('');
    $(this).hide();
  });

  console.log('AVP admin-video.js ready');
});

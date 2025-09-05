jQuery(function($){
    var v = document.getElementById('avp-video');
    if (!v) return;

    var quiz = $('#avp-quiz');
    var form = $('#avp-quiz-form');
    var result = $('#avp-quiz-result');
    var nextBox = $('#avp-next');
    var postId = parseInt($(v).data('post'), 10) || 0;

    // نمایش آزمون پس از اتمام ویدیو
    v.addEventListener('ended', function(){
        quiz.show();
        window.scrollTo({top: quiz.offset().top - 100, behavior: 'smooth'});
    });

    form.on('submit', function(e){
        e.preventDefault();
        var ans = parseInt($('input[name="answer"]:checked').val(), 10);
        if (!ans) { result.text('لطفا یک گزینه را انتخاب کنید.'); return; }

        $.post(AVP.ajax, {
            action: 'avp_submit_quiz',
            nonce: AVP.nonce,
            post_id: postId,
            answer: ans
        }, function(res){
            if (!res || !res.success) { result.text('خطا در ارسال.'); return; }
            result.html(res.data.msg || '');
            if (res.data.ok) {
                if (res.data.next) {
                    nextBox.html('<a class="button button-primary" href="'+res.data.next+'">رفتن به ویدیوی بعدی ➜</a>').show();
                } else {
                    nextBox.html('<span>🎉 تبریک! این آخرین ویدیو بود.</span>').show();
                }
            }
        });
    });
});

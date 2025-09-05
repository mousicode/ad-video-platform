jQuery(function($){
    var v = document.getElementById('avp-video');
    if (!v) return;

    var maxReached = 0;
    v.addEventListener('timeupdate', function(){
        if (v.currentTime > maxReached) maxReached = v.currentTime;
    });

    v.addEventListener('seeking', function(){
        if (v.currentTime > maxReached + 0.5) {
            v.currentTime = maxReached;
        }
    });
});

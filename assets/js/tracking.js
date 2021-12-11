let tm = 0;

if (typeof (timerSendInterval) === 'undefined') {
    timerSendInterval = 5;
}

setInterval(function () {
    tm++;
    if (tm % parseInt(timerSendInterval) == 0) {
        trackDuration();
    }
}, 1000);

window.onbeforeunload = trackDuration;

function trackDuration() {
    const o = document.createElement('img');
    o.src = '/tracker/track/duration?url=' + encodeURIComponent(window.location.href) + '&time=' + tm;
}

let lastScrollTop = 0;
let scrollsDown = 0;
let scrollsUp = 0;

window.addEventListener('scroll', function () {
    let st = window.pageYOffset || document.documentElement.scrollTop;
    if (st > lastScrollTop) {
        // downscroll code
        scrollsDown++;
        const o = document.createElement('img');
        o.src = '/tracker/track/scroll?url=' + encodeURIComponent(window.location.href) + '&direction=Down&number=' + scrollsDown;
    } else {
        // upscroll code
        scrollsUp++;
        const o = document.createElement('img');
        o.src = '/tracker/track/scroll?url=' + encodeURIComponent(window.location.href) + '&direction=Up&number=' + scrollsDown;
    }
    lastScrollTop = st <= 0 ? 0 : st;
}, false);
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
    let xmlhttp = new XMLHttpRequest();
    let durationUrl = '/tracker/track/duration?time=' + tm;

    if (typeof (sessionUrlId) === 'undefined') {
        durationUrl += '&url=' + encodeURIComponent(window.location.href);
    } else {
        durationUrl += '&sessionUrlId=' + sessionUrlId;
    }

    xmlhttp.open('GET', durationUrl, true);
    xmlhttp.send();
}


let lastScrollTop = 0;
let scrollsDown = 0;
let scrollsUp = 0;
let timer;

window.addEventListener('scroll', function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
        let st = window.pageYOffset || document.documentElement.scrollTop;
        let xmlhttp = new XMLHttpRequest();
        let scrollUrl = '/tracker/track/scroll?';

        if (typeof (sessionUrlId) === 'undefined') {
            scrollUrl += 'url=' + encodeURIComponent(window.location.href);
        } else {
            scrollUrl += 'sessionUrlId=' + sessionUrlId;
        }

        if (st > lastScrollTop) {
            // downscroll code
            scrollsDown++;
            scrollUrl += '&direction=Down&number=' + scrollsDown;
        } else {
            // upscroll code
            scrollsUp++;
            scrollUrl += '&direction=Up&number=' + scrollsUp;
        }
        xmlhttp.open('GET', scrollUrl, true);
        xmlhttp.send();
        lastScrollTop = st <= 0 ? 0 : st;
    }, 200);
});


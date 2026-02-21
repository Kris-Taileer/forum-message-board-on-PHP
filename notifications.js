if (!("Notification" in window)) {
    console.log("notifications not supported");
} else {
    document.addEventListener('DOMContentLoaded', function() {
        checkPermission();
        updateCount();
        startPolling();
    });
}

let lastCount = 0;

function checkPermission() {
    if (Notification.permission === "granted") {
        console.log("notifications on");
    } else if (Notification.permission !== "denied") {
        showButton();
    }
}

function showButton() {
    if (document.getElementById('notify-btn')) return;
    
    let btn = document.createElement('div');
    btn.id = 'notify-btn';
    btn.innerHTML = '<a href="#" onclick="askPermission(); return false;">enable notifications</a>';
    btn.style.cssText = 'position:fixed; bottom:10px; right:10px; background:#d0d0d0; border:2px solid #808080; padding:8px 12px; z-index:9999; font-family:Arial; font-size:11pt;';
    document.body.appendChild(btn);
}

window.askPermission = function() {
    Notification.requestPermission().then(function(p) {
        if (p === "granted") {
            document.getElementById('notify-btn')?.remove();
            new Notification("enabled", {
                body: "notifications active",
                icon: "https://www.allsmileys.com/files/kolobok/light/76.gif"
            });
        }
    });
}

function updateCount() {
    return fetch('get_message_count.php')
        .then(r => r.json())
        .then(d => {
            lastCount = d.count;
            return lastCount;
        })
        .catch(e => console.log('count error', e));
}

function startPolling() {
    setInterval(checkNew, 10000);
}

function checkNew() {
    fetch('get_message_count.php')
        .then(r => r.json())
        .then(d => {
            if (d.count > lastCount && Notification.permission === "granted") {
                let newMsgs = d.count - lastCount;
                
                let n = new Notification(">>>new message" + (newMsgs > 1 ? "s" : ""), {
                    body: newMsgs + " new message" + (newMsgs > 1 ? "s" : "") + " - click to refresh",
                    icon: "https://www.allsmileys.com/files/kolobok/light/76.gif",
                    vibrate: [200, 100, 200]
                });
                
                n.onclick = function() {
                    window.focus();
                    location.reload();
                };
                
                lastCount = d.count;
            }
        })
        .catch(e => console.log('check error', e));
}

document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        updateCount();
    }
});
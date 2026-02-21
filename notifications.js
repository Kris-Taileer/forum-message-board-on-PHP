let lastMessageCount = 0;

document.addEventListener("DOMContentLoaded", function() {
    
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js")
            .then(() => console.log(">> Service Worker registered"))
            .catch(err => console.log("X Service Worker error:", err));
    }
    
    let button = document.getElementById("enableNotifications");
    if (!button) {
        button = document.createElement("button");
        button.id = "enableNotifications";
        button.textContent = ">> Enable Notifications <<";
        button.style.cssText = "margin:10px 0; padding:5px 10px; background:#c0c0c0; border:2px solid #696969; cursor:pointer; font-weight:bold;";
        
        let formArea = document.querySelector(".form-area");
        if (formArea) {
            formArea.parentNode.insertBefore(button, formArea);
        }
    }
    
    button.addEventListener("click", async function() {
        try {
            const permission = await Notification.requestPermission();
            
            if (permission === "granted") {
                alert(">> Notifications enabled!");
                button.style.display = "none";
                const registration = await navigator.serviceWorker.ready;
                registration.showNotification(">> Notifications active", {
                    body: "You will receive notifications about new messages",
                    icon: "https://www.allsmileys.com/files/skype/61.gif",
                    requireInteraction: false
                });
            }
        } catch (err) {
            console.log("Permission error:", err);
        }
    });
    

    if (Notification.permission === "granted") {
        button.style.display = "none";
    }
    
    checkMessages();
    setInterval(checkMessages, 2500);
});

async function checkMessages() {
    try {
        const response = await fetch("get_message_count.php");
        const data = await response.json();
        
        console.log("Current count:", data.count, "Last count:", lastMessageCount);
        
        if (lastMessageCount === 0) {
            lastMessageCount = data.count;
            return;
        }
        
        if (data.count > lastMessageCount && Notification.permission === "granted") {
            const newMessages = data.count - lastMessageCount;
            sendNotification(">>" + newMessages + " new message" + (newMessages > 1 ? "s" : ""));
            lastMessageCount = data.count;
        }
    } catch (err) {
        console.log("Error checking messages:", err);
    }
}

function sendNotification(text) {
    if (Notification.permission === "granted") {
        navigator.serviceWorker.ready.then(function(registration) {
            registration.showNotification("My Basement", {
                body: text,
                icon: "https://www.allsmileys.com/files/skype/61.gif",
                badge: "https://www.allsmileys.com/files/skype/61.gif",
                vibrate: [200, 100, 200],
                requireInteraction: false
            });
        });
    }
}

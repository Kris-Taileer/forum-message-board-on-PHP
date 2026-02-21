let lastMessageCount = 0;

document.addEventListener("DOMContentLoaded", function() {
    
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js")
            .then(() => console.log("Service Worker registered"))
            .catch(err => console.log("Service Worker error:", err));
    }
    
    let button = document.getElementById("enableNotifications");
    
    if (button) {
        button.addEventListener("click", async function() {
            try {
                const permission = await Notification.requestPermission();
                
                if (permission === "granted") {
                    alert("Notifications enabled!");
                    button.style.display = "none";
                    
                    const registration = await navigator.serviceWorker.ready;
                    registration.showNotification("Notifications active", {
                        body: "You will receive notifications about new messages",
                        icon: "https://www.allsmileys.com/files/kolobok/light/76.gif"
                    });
                }
            } catch (err) {
                console.log("Permission error:", err);
            }
        });
        
        if (Notification.permission === "granted") {
            button.style.display = "none";
        }
    }
    
    checkMessages();
    setInterval(checkMessages, 5000);
});

async function checkMessages() {
    try {
        const response = await fetch("get_message_count.php");
        const data = await response.json();
        
        if (lastMessageCount === 0) {
            lastMessageCount = data.count;
            return;
        }
        
        if (data.count > lastMessageCount && Notification.permission === "granted") {
            const newMessages = data.count - lastMessageCount;
            sendNotification(">> " + newMessages + " new message" + (newMessages > 1 ? "s" : ""));
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
                icon: "https://www.allsmileys.com/files/kolobok/light/76.gif"
            });
        });
    }
}

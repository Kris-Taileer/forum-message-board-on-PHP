self.addEventListener("install", function(event) {
    console.log("Service Worker installed");
});

self.addEventListener("notificationclick", function(event) {
    event.notification.close();
    
    event.waitUntil(
        clients.matchAll({type: "window"}).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                if (clientList[i].url.includes("localhost") && "focus" in clientList[i]) {
                    return clientList[i].focus();
                }
            }
            return clients.openWindow("/");
        })
    );
});

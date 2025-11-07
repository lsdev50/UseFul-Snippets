function resizeIframe() {
    var iframe = jQuery(".AnC-iFrame");
    
    iframe.each(function() {
        var $this = jQuery(this);
        
        try {
            var newHeight = $this[0].contentWindow.document.body.scrollHeight + "px";
            $this.css("height", newHeight);
            console.log("Iframe resized to:", newHeight);
        } catch (e) {
            console.error("Cross-origin restriction prevents resizing:", e);
        }
    });
}

// Resize on load and window resize
jQuery(window).on("load resize", resizeIframe);
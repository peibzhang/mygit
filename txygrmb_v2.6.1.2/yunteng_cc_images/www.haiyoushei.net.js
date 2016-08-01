<!--ÆÁ±ÎÍøÒ³ÓÒæI-->
    function clickIE4() {
        if (event.button == 2) {
            return false;
        }
    }
    function clickNS4(e) {
        if (document.layers || document.getElementById && !document.all) {
            if (e.which == 2 || e.which == 3) {
                return false;
            }
        }
    }
    function OnDeny() {
        if (event.ctrlKey || event.keyCode == 78 && event.ctrlKey || event.altKey || event.altKey && event.keyCode == 115) {
            return false;
        }
    }
    if (document.layers) {
        document.captureEvents(Event.MOUSEDOWN);
        document.onmousedown = clickNS4;
        document.onkeydown = OnDeny();
    } else if (document.all && !document.getElementById) {
        document.onmousedown = clickIE4;
        document.onkeydown = OnDeny();
    }
    document.oncontextmenu = new Function("return false");

<!--°Ù¶ÈÍÆËÍ-->
(function(){
    var bp = document.createElement('script');
    var curProtocol = window.location.protocol.split(':')[0];
    if (curProtocol === 'https') {
        bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';        
    }
    else {
        bp.src = 'http://push.zhanzhang.baidu.com/push.js';
    }
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(bp, s);
})();
<?php
?>
    <script>
    (function(){
        var s = localStorage.getItem('rbpl-theme');
        var p = window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light';
        document.documentElement.setAttribute('data-theme', s||p);
    })();
    </script>

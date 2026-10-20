<?php
// Vercel entry point. All non-asset requests are routed here (see vercel.json)
// and handed to the same allow-list router used by Apache/Nginx/php -S.
require dirname(__DIR__) . '/router.php';

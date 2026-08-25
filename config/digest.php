<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Digest send hour
    |--------------------------------------------------------------------------
    |
    | The hour (0-23, server time) at which daily/weekly digests are sent. The `digest:send`
    | command runs hourly and only acts once the current hour matches this value.
    |
    */
    'send_hour' => (int) env('DIGEST_SEND_HOUR', 8),

];

<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('menu', function () {
    return true;
});

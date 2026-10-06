<?php

use Illuminate\Support\Facades\Schedule;



Schedule::command('essl:sync-access')->dailyAt('00:10');
Schedule::command('dummy:test')->dailyAt('11:38');

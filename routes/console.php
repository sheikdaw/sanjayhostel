<?php

use Illuminate\Support\Facades\Schedule;



// Auto-block all ACTIVE residents on/after day 11 (payment cutoff)
Schedule::command('essl:monthly-block')->dailyAt('06:00');
Schedule::command('dummy:test')->dailyAt('11:28');

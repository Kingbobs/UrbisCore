<?php

declare(strict_types = 1);

namespace core\clearlag;

use core\clearlag\task\ClearLagTask;
use core\Urbis;

class ClearLagManager{

    public function __construct(){
        Urbis::getInstance()->getScheduler()->scheduleRepeatingTask(new ClearLagTask(), 20);
    }
}
<?php

use Ayvazyan10\ArmSoft\ArmSoft;

if (!function_exists('armsoft')) {
    function armsoft(): ArmSoft
    {
        return app('ArmSoft');
    }
}

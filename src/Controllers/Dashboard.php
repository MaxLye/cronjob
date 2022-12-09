<?php

declare(strict_types=1);

namespace Daycry\CronJob\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    protected $helpers = ['form'];

    /**
     * Displays the form the login to the site.
     */
    public function index()
    {
        return view(config('CronJob')->views['login']);
    }
}
<?php
// controllers/HomeAdminController.php

namespace App\Controllers;

class HomeAdminController extends BaseController
{
    public function index(): void
    {
        // Admin dashboard
        $this->render('HomeAdmin/index');
    }
}

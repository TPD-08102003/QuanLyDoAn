<?php
// controllers/HomeController.php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index(): void
    {
        // Dashboard for general users
        $this->render('home/index');
    }
}

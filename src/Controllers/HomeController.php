<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, View};

final class HomeController
{
    /** Display the home page or redirect signed-in users. */
    public function index(): void
    {
        if (Auth::user()) {
            redirect(Auth::hasRole('admin') ? '/yonetim' : '/katalog');
        }
        View::render('home');
    }
}

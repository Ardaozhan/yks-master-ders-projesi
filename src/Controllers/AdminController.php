<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

final class AdminController
{
    /** Display the administration placeholder. */
    public function index(): void
    {
        View::render('admin/index');
    }
}

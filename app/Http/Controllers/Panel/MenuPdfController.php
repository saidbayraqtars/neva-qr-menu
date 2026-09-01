<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\MenuPdfService;
use Symfony\Component\HttpFoundation\Response;

class MenuPdfController extends Controller
{
    public function download(MenuPdfService $service): Response
    {
        return $service->build(app('restaurant'));
    }
}

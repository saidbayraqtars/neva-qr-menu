<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ServerHealth;
use Illuminate\View\View;

/**
 * Sunucu ve uygulama sağlık ekranı.
 *
 * Barındırma panelinin gösterdiği RAM/disk burada da var ama asıl değer
 * uygulamaya özel sinyallerde: kuyruk işçisi gerçekten iş işliyor mu, son
 * yedek ne zaman alındı, SQLite WAL modunda mı, yüklenen görseller ne kadar
 * yer kaplıyor. Bunları hiçbir barındırma paneli bilemez.
 */
class SystemController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.system', ['health' => ServerHealth::all()]);
    }
}

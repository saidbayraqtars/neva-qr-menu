<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\VisitTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Menü görüntülenme "beacon" ucu.
 *
 * Sayfa önbellekten servis edildiği için sayaç burada artırılır. GET olmasının
 * nedeni, istemcinin `fetch(..., {keepalive:true})` ile CSRF jetonu taşımadan
 * çağırabilmesi; uç hiçbir kalıcı kullanıcı verisi yazmaz, yalnızca sayaç artırır.
 */
class TrackController extends Controller
{
    public function __invoke(Request $request, VisitTracker $tracker): Response
    {
        /** @var Restaurant $tenant */
        $tenant = app('tenant');

        $tracker->record(
            $tenant,
            $request->query('masa'),
            $request->query('yeni') === '1',
        );

        return response()->noContent()->header('Cache-Control', 'no-store');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\DeviceOwner;
use App\Models\Policy;
use App\Models\App;
use App\Models\DeviceCommand;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDevices = Device::where('state', '!=', 'DELETED')->count();
        $activeDevices = Device::where('state', 'ACTIVE')->count();
        $lostModeDevices = DeviceCommand::where('command_type', 'START_LOST_MODE')
            ->where('status', 'SUCCESS')
            ->count();
        $totalPolicies = Policy::count();
        $totalApps = App::count();

        // Show only active (non-deleted) devices, ordered by newest enrollment first
        $recentDevices = Device::with(['owner', 'policy'])
            ->where('state', '!=', 'DELETED')
            ->orderByRaw('COALESCE(enrolled_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'totalDevices',
            'activeDevices',
            'lostModeDevices',
            'totalPolicies',
            'totalApps',
            'recentDevices'
        ));
    }
}

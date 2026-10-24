<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Branch;
use App\Models\Policy;
use App\Models\App;
use App\Models\DeviceCommand;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedBranchId = $user->isBranchScoped() ? $user->branch_id : $request->input('branch_id');

        $deviceQuery = Device::forUser($user);
        if (!empty($selectedBranchId)) {
            $deviceQuery->where('branch_id', $selectedBranchId);
        }

        $totalDevices = (clone $deviceQuery)->where('state', '!=', 'DELETED')->count();
        $activeDevices = (clone $deviceQuery)->where('state', 'ACTIVE')->count();

        $lostModeQuery = DeviceCommand::where('command_type', 'START_LOST_MODE')
            ->where('status', 'SUCCESS');
        if ($user->isBranchScoped() || !empty($selectedBranchId)) {
            $lostModeQuery->whereIn('device_id', (clone $deviceQuery)->select('device_id'));
        }
        $lostModeDevices = $lostModeQuery->count();

        $totalPolicies = Policy::count();
        $totalApps = App::count();

        // Show only active (non-deleted) devices, ordered by newest enrollment first
        $recentDevices = (clone $deviceQuery)
            ->with(['owner', 'policy', 'branch'])
            ->where('state', '!=', 'DELETED')
            ->orderByRaw('COALESCE(enrolled_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $currentBranch = $selectedBranchId ? Branch::find($selectedBranchId) : null;

        return view('dashboard', compact(
            'totalDevices',
            'activeDevices',
            'lostModeDevices',
            'totalPolicies',
            'totalApps',
            'recentDevices',
            'branches',
            'currentBranch',
            'selectedBranchId'
        ));
    }
}

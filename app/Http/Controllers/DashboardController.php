<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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
        $offlineAlertCount = (clone $deviceQuery)->offlineAlert()->count();

        $lostModeQuery = DeviceCommand::where('command_type', 'START_LOST_MODE')
            ->where('status', 'SUCCESS');
        if ($user->isBranchScoped() || !empty($selectedBranchId)) {
            $lostModeQuery->whereIn('device_id', (clone $deviceQuery)->select('device_id'));
        }
        $lostModeDevices = $lostModeQuery->count();

        $totalPolicies = Policy::count();
        $totalApps = App::count();

        // Overall Data Consistency (Active Devices with Owner vs Unassigned)
        $overallMatchedDevices = (clone $deviceQuery)
            ->where('state', '!=', 'DELETED')
            ->whereHas('owner', function ($q) {
                $q->whereNotNull('nama')->where('nama', '!=', '');
            })
            ->count();
        $overallUnassignedDevices = max(0, $totalDevices - $overallMatchedDevices);

        // Today's (or selected date's) Enrollment Chart & Reconciliation Data
        $rawDate = $request->input('enrollment_date', now()->toDateString());
        try {
            $enrollmentDate = Carbon::parse($rawDate)->toDateString();
        } catch (\Throwable $e) {
            $enrollmentDate = now()->toDateString();
        }

        $enrolledOnDate = (clone $deviceQuery)
            ->with(['owner', 'branch'])
            ->where('state', '!=', 'DELETED')
            ->whereDate(DB::raw('COALESCE(enrolled_at, created_at)'), $enrollmentDate)
            ->orderByRaw('COALESCE(enrolled_at, created_at) DESC')
            ->get();

        $todayTotalCount = $enrolledOnDate->count();
        $todayMatchedCount = $enrolledOnDate->filter(function ($d) {
            return $d->owner && !empty($d->owner->nama);
        })->count();
        $todayUnassignedCount = max(0, $todayTotalCount - $todayMatchedCount);

        $hourlyLabels = [];
        $hourlyMatched = array_fill(0, 24, 0);
        $hourlyUnassigned = array_fill(0, 24, 0);

        for ($h = 0; $h < 24; $h++) {
            $hourlyLabels[] = sprintf('%02d:00', $h);
        }

        foreach ($enrolledOnDate as $dev) {
            $ts = $dev->enrolled_at ?? $dev->created_at;
            $hour = $ts ? (int) $ts->format('G') : 0;
            $hasOwner = $dev->owner && !empty($dev->owner->nama);
            if ($hasOwner) {
                $hourlyMatched[$hour]++;
            } else {
                $hourlyUnassigned[$hour]++;
            }
        }

        // 7-Day Daily Enrollment Trend (ending on $enrollmentDate)
        $baseDate = Carbon::parse($enrollmentDate);
        $start7Days = $baseDate->copy()->subDays(6)->startOfDay();
        $end7Days = $baseDate->copy()->endOfDay();

        $enrolledLast7Days = (clone $deviceQuery)
            ->with('owner')
            ->where('state', '!=', 'DELETED')
            ->whereBetween(DB::raw('COALESCE(enrolled_at, created_at)'), [$start7Days, $end7Days])
            ->get();

        $dailyLabels = [];
        $dailyMatched = [];
        $dailyUnassigned = [];
        $dailyMap = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = $baseDate->copy()->subDays($i);
            $key = $d->toDateString();
            $dailyLabels[] = $d->translatedFormat('d M');
            $dailyMap[$key] = ['matched' => 0, 'unassigned' => 0];
        }

        foreach ($enrolledLast7Days as $dev) {
            $ts = $dev->enrolled_at ?? $dev->created_at;
            $key = $ts ? $ts->toDateString() : null;
            if ($key && isset($dailyMap[$key])) {
                $hasOwner = $dev->owner && !empty($dev->owner->nama);
                if ($hasOwner) {
                    $dailyMap[$key]['matched']++;
                } else {
                    $dailyMap[$key]['unassigned']++;
                }
            }
        }

        foreach ($dailyMap as $vals) {
            $dailyMatched[] = $vals['matched'];
            $dailyUnassigned[] = $vals['unassigned'];
        }

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
            'offlineAlertCount',
            'lostModeDevices',
            'totalPolicies',
            'totalApps',
            'recentDevices',
            'branches',
            'currentBranch',
            'selectedBranchId',
            'enrollmentDate',
            'todayTotalCount',
            'todayMatchedCount',
            'todayUnassignedCount',
            'overallMatchedDevices',
            'overallUnassignedDevices',
            'hourlyLabels',
            'hourlyMatched',
            'hourlyUnassigned',
            'dailyLabels',
            'dailyMatched',
            'dailyUnassigned'
        ));
    }
}

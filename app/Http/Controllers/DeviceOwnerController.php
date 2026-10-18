<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceOwner;
use App\Models\AuditLog;

class DeviceOwnerController extends Controller
{
    /**
     * Store or update owner association.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id'  => ['required', 'string'],
            'nama'       => ['required', 'string', 'max:255'],
            'id_anggota' => ['required', 'string', 'max:100'],
            'telepon'    => ['nullable', 'string', 'max:50'],
            'departemen' => ['nullable', 'string', 'max:100'],
            'catatan'    => ['nullable', 'string'],
        ]);

        $owner = DeviceOwner::updateOrCreate(
            ['device_id' => $validated['device_id']],
            $validated
        );

        AuditLog::log('ASSIGN_DEVICE_OWNER', 'DeviceOwner', (string)$owner->id, $validated);

        return back()->with('success', 'Data pemilik perangkat berhasil diperbarui.');
    }
}

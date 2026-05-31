<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LokasiKantor;

class LokasiKantorController extends Controller
{
    public function index()
    {
        $lokasi = LokasiKantor::first();
        if (!$lokasi) {
            $lokasi = LokasiKantor::create([
                'nama_lokasi' => 'Kantor Pusat',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meter' => 100,
            ]);
        }
        return view('admin.lokasi-kantor.index', compact('lokasi'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:10|max:5000',
        ]);

        $lokasi = LokasiKantor::first();
        $lokasi->update($request->all());

        return redirect()->route('admin.lokasi-kantor.index')
            ->with('success', 'Pengaturan lokasi kantor berhasil diperbarui!');
    }
}

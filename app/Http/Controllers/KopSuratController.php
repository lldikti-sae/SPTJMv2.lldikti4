<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KopSuratController extends Controller
{
    public function indexKop()
    {
        $data = DB::table('m_kop_surat')->first();
        return view('admin.kop-surat', compact('data'));
    }

    public function updateKop(Request $request)
    {
        $request->validate([
            'file_pdf' => 'nullable|mimes:pdf|max:5120', // 5MB max
        ]);

        $data = DB::table('m_kop_surat')->first();
        $file_pdf_url = $data->file_pdf_url ?? null;

        if ($request->hasFile('file_pdf')) {
            $filePdf = $request->file('file_pdf');
            $filenamePdf = 'kop_surat_bg_' . time() . '.' . $filePdf->getClientOriginalExtension();
            
            $filePdf->storeAs('public/kop_surat', $filenamePdf);
            
            if ($file_pdf_url && strpos($file_pdf_url, 'storage/kop_surat') !== false) {
                $oldPdfPath = str_replace('storage/', 'public/', $file_pdf_url);
                if (Storage::exists($oldPdfPath)) {
                    Storage::delete($oldPdfPath);
                }
            }
            
            $file_pdf_url = 'storage/kop_surat/' . $filenamePdf;
        }

        $updateData = [
            'file_pdf_url' => $file_pdf_url,
            'updated_at' => now()
        ];

        if ($data) {
            DB::table('m_kop_surat')->where('id', $data->id)->update($updateData);
        } else {
            $updateData['created_at'] = now();
            DB::table('m_kop_surat')->insert($updateData);
        }

        return redirect()->back()->with('success', 'Data Kop Surat berhasil diperbarui.');
    }

    public function indexPenandatangan()
    {
        $pejabatList = DB::table('m_pejabat')->orderBy('urutan', 'asc')->get();
        return view('admin.penandatangan', compact('pejabatList'));
    }

    public function storePenandatangan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'nip' => 'nullable|string',
            'jabatan' => 'required|string',
            'urutan' => 'required|integer',
        ]);

        DB::table('m_pejabat')->insert([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'urutan' => $request->urutan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pejabat berhasil ditambahkan.');
    }

    public function updatePenandatangan(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string',
            'nip' => 'nullable|string',
            'jabatan' => 'required|string',
            'urutan' => 'required|integer',
        ]);

        DB::table('m_pejabat')->where('id', $id)->update([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'urutan' => $request->urutan,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Data Pejabat berhasil diperbarui.');
    }

    public function destroyPenandatangan($id)
    {
        DB::table('m_pejabat')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Pejabat berhasil dihapus.');
    }

    public function toggleStatusPenandatangan($id)
    {
        $pejabat = DB::table('m_pejabat')->where('id', $id)->first();
        if ($pejabat) {
            $newStatus = $pejabat->is_aktif ? 0 : 1;
            DB::table('m_pejabat')->where('id', $id)->update(['is_aktif' => $newStatus, 'updated_at' => now()]);
            return response()->json(['success' => true, 'is_aktif' => $newStatus, 'message' => 'Status berhasil diubah.']);
        }
        return response()->json(['success' => false, 'message' => 'Pejabat tidak ditemukan.'], 404);
    }
}

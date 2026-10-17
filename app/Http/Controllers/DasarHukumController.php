<?php

namespace App\Http\Controllers;

use App\Models\DasarHukumPasal;
use App\Models\DasarHukumPeraturan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DasarHukumController extends Controller
{
    public function show(string $slug): View
    {
        $modules = $this->modules();
        abort_unless(array_key_exists($slug, $modules), 404);

        return view('dasar-hukum.show', [
            'slug' => $slug,
            'title' => $modules[$slug],
            'regulations' => DasarHukumPeraturan::query()
                ->with('pasal')
                ->where('module_slug', $slug)
                ->orderBy('nama_peraturan')
                ->get(),
        ]);
    }

    public function storeRegulation(Request $request, string $slug): RedirectResponse
    {
        $this->ensureModuleExists($slug);
        $data = $request->validate([
            'nama_peraturan' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $file = $request->file('document');
        DasarHukumPeraturan::query()->create([
            'module_slug' => $slug,
            'nama_peraturan' => $data['nama_peraturan'],
            'document_path' => $file->store('dasar-hukum'),
            'document_original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Peraturan berhasil ditambahkan.');
    }

    public function updateRegulation(Request $request, string $slug, DasarHukumPeraturan $regulation): RedirectResponse
    {
        $this->ensureRegulationInModule($slug, $regulation);
        $data = $request->validate([
            'nama_peraturan' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $oldDocumentPath = $regulation->document_path;
        $regulation->nama_peraturan = $data['nama_peraturan'];
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $regulation->document_path = $file->store('dasar-hukum');
            $regulation->document_original_name = $file->getClientOriginalName();
        }
        $regulation->save();

        if ($regulation->document_path !== $oldDocumentPath) {
            Storage::delete($oldDocumentPath);
        }

        return back()->with('success', 'Data peraturan berhasil diperbarui.');
    }

    public function destroyRegulation(string $slug, DasarHukumPeraturan $regulation): RedirectResponse
    {
        $this->ensureRegulationInModule($slug, $regulation);
        $documentPath = $regulation->document_path;
        $regulation->delete();
        Storage::delete($documentPath);

        return back()->with('success', 'Peraturan dan seluruh pasalnya berhasil dihapus.');
    }

    public function storeArticle(Request $request, string $slug, DasarHukumPeraturan $regulation): RedirectResponse
    {
        $this->ensureRegulationInModule($slug, $regulation);
        $data = $request->validate([
            'nomor_pasal' => ['required', 'string', 'max:100'],
            'penjelasan' => ['required', 'string'],
        ]);

        $regulation->pasal()->create($data);

        return back()->with('success', 'Pasal berhasil ditambahkan.');
    }

    public function destroyArticle(
        string $slug,
        DasarHukumPeraturan $regulation,
        DasarHukumPasal $article
    ): RedirectResponse {
        $this->ensureRegulationInModule($slug, $regulation);
        abort_unless($article->peraturan_id === $regulation->id, 404);
        $article->delete();

        return back()->with('success', 'Pasal berhasil dihapus.');
    }

    public function download(string $slug, DasarHukumPeraturan $regulation): StreamedResponse
    {
        $this->ensureRegulationInModule($slug, $regulation);
        abort_unless(Storage::exists($regulation->document_path), 404);

        return Storage::download($regulation->document_path, $regulation->document_original_name);
    }

    public function preview(string $slug, DasarHukumPeraturan $regulation): StreamedResponse
    {
        $this->ensureRegulationInModule($slug, $regulation);
        abort_unless(strtolower(pathinfo($regulation->document_original_name, PATHINFO_EXTENSION)) === 'pdf', 404);
        abort_unless(Storage::exists($regulation->document_path), 404);

        return Storage::response(
            $regulation->document_path,
            $regulation->document_original_name,
            ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff'],
            'inline'
        );
    }

    private function modules(): array
    {
        return config('dasar_hukum');
    }

    private function ensureModuleExists(string $slug): void
    {
        abort_unless(array_key_exists($slug, $this->modules()), 404);
    }

    private function ensureRegulationInModule(string $slug, DasarHukumPeraturan $regulation): void
    {
        $this->ensureModuleExists($slug);
        abort_unless($regulation->module_slug === $slug, 404);
    }
}

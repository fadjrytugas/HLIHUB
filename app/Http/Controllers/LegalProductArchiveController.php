<?php

namespace App\Http\Controllers;

use App\Models\LegalProductArchive;
use App\Models\Stakeholder;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LegalProductArchiveController extends Controller
{
    private const TYPES = [
        'pks' => 'PKS',
        'mou' => 'MoU',
    ];

    public function index(Request $request, string $type): View
    {
        $title = $this->titleFor($type);
        $searchTerm = trim((string) $request->query('q', ''));

        return view('arsip-produk-hukum.index', [
            'type' => $type,
            'title' => $title,
            'archives' => LegalProductArchive::query()
                ->where('type', $type)
                ->with('addenda')
                ->when($searchTerm !== '', function ($query) use ($searchTerm): void {
                    $query->where(function ($query) use ($searchTerm): void {
                        foreach ([
                            'nama_dokumen',
                            'document_original_name',
                            'stakeholder',
                            'unit_pelaksana',
                            'ruang_lingkup',
                            'penandatangan_pihak_pertama',
                            'penandatangan_pihak_kedua',
                            'korespondensi_pihak_pertama',
                            'korespondensi_pihak_kedua',
                            'tanggal_mulai',
                            'tanggal_berakhir',
                            'jangka_waktu',
                        ] as $field) {
                            $query->orWhere($field, 'like', '%'.$searchTerm.'%');
                        }

                        $query->orWhereHas('addenda', function ($addendumQuery) use ($searchTerm): void {
                            $addendumQuery->where('perubahan', 'like', '%'.$searchTerm.'%')
                                ->orWhere('tanggal_adendum', 'like', '%'.$searchTerm.'%');
                        });
                    });
                })
                ->orderBy('tanggal_berakhir')
                ->orderBy('nama_dokumen')
                ->paginate(12)
                ->withQueryString(),
            'searchTerm' => $searchTerm,
            'stakeholders' => Stakeholder::query()->orderBy('nama')->pluck('nama'),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $this->titleFor($type);
        $data = $request->validate($this->rules(true));
        $file = $request->file('document');

        LegalProductArchive::query()->create([
            ...$data,
            'type' => $type,
            'document_path' => $file->store('produk-hukum/'.$type),
            'document_original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()
            ->route('legal-products.index', $type)
            ->with('success', 'Arsip '.$this->titleFor($type).' berhasil ditambahkan.');
    }

    public function update(Request $request, string $type, LegalProductArchive $archive): RedirectResponse
    {
        $this->ensureArchiveInType($type, $archive);
        $data = $request->validate($this->rules(false));
        $oldDocumentPath = $archive->document_path;

        $archive->fill($data);
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $archive->document_path = $file->store('produk-hukum/'.$type);
            $archive->document_original_name = $file->getClientOriginalName();
        }
        $archive->save();

        if ($archive->document_path !== $oldDocumentPath) {
            Storage::delete($oldDocumentPath);
        }

        return redirect()
            ->route('legal-products.index', $type)
            ->with('success', 'Arsip '.$this->titleFor($type).' berhasil diperbarui.');
    }

    public function storeAddendum(Request $request, string $type, LegalProductArchive $archive): RedirectResponse
    {
        $this->ensureArchiveInType($type, $archive);
        $data = $request->validate([
            'tanggal_adendum' => ['required', 'date'],
            'perubahan' => ['required', 'string', 'max:10000'],
        ]);

        $archive->addenda()->create($data);

        return redirect()
            ->route('legal-products.index', $type)
            ->with('success', 'Adendum '.$this->titleFor($type).' berhasil dicatat.');
    }

    public function destroy(string $type, LegalProductArchive $archive): RedirectResponse
    {
        $this->ensureArchiveInType($type, $archive);
        $documentPath = $archive->document_path;
        $archive->delete();
        Storage::delete($documentPath);

        return redirect()
            ->route('legal-products.index', $type)
            ->with('success', 'Arsip '.$this->titleFor($type).' berhasil dihapus.');
    }

    public function download(string $type, LegalProductArchive $archive): StreamedResponse
    {
        $this->ensureArchiveInType($type, $archive);
        abort_unless(Storage::exists($archive->document_path), 404);

        return Storage::download($archive->document_path, $archive->document_original_name);
    }

    private function rules(bool $documentRequired): array
    {
        return [
            'nama_dokumen' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jangka_waktu' => ['required', 'string', 'max:100'],
            'stakeholder' => ['required', 'string', 'max:255'],
            'unit_pelaksana' => ['required', 'string', 'max:255'],
            'ruang_lingkup' => ['required', 'string', 'max:10000'],
            'penandatangan_pihak_pertama' => ['required', 'string', 'max:255'],
            'penandatangan_pihak_kedua' => ['required', 'string', 'max:255'],
            'korespondensi_pihak_pertama' => ['required', 'string', 'max:5000'],
            'korespondensi_pihak_kedua' => ['required', 'string', 'max:5000'],
            'document' => [
                $documentRequired ? 'required' : 'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:20480',
            ],
        ];
    }

    private function titleFor(string $type): string
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        return self::TYPES[$type];
    }

    private function ensureArchiveInType(string $type, LegalProductArchive $archive): void
    {
        $this->titleFor($type);
        abort_unless($archive->type === $type, 404);
    }
}

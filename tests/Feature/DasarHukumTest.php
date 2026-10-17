<?php

namespace Tests\Feature;

use App\Models\DasarHukumPeraturan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DasarHukumTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_regulation_can_be_added_with_articles_and_its_document_downloaded(): void
    {
        Storage::fake();

        $this->post(route('dasar-hukum.regulations.store', 'pso'), [
            'nama_peraturan' => 'Undang-Undang Nomor 23 Tahun 2007 tentang Perkeretaapian',
            'document' => UploadedFile::fake()->createWithContent('uu-23-2007.pdf', "%PDF-1.4\nSample test PDF\n%%EOF"),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $regulation = DasarHukumPeraturan::query()
            ->where('nama_peraturan', 'Undang-Undang Nomor 23 Tahun 2007 tentang Perkeretaapian')
            ->latest('id')
            ->firstOrFail();
        Storage::assertExists($regulation->document_path);

        $this->post(route('dasar-hukum.articles.store', ['pso', $regulation]), [
            'nomor_pasal' => 'Pasal 151',
            'penjelasan' => "(1) Tarif angkutan kereta api terdiri dari tarif angkutan orang dan tarif angkutan barang.\n\n(2) Pedoman tarif ditetapkan oleh Pemerintah.",
        ])->assertRedirect();

        $this->get(route('dasar-hukum.show', 'pso'))
            ->assertOk()
            ->assertSee('Undang-Undang Nomor 23 Tahun 2007 tentang Perkeretaapian')
            ->assertSee('Lihat Dokumen')
            ->assertSee('Pasal 151')
            ->assertSee('Tarif angkutan kereta api terdiri dari tarif angkutan orang');

        $this->get(route('dasar-hukum.regulations.preview', ['pso', $regulation]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename=uu-23-2007.pdf');

        $this->get(route('dasar-hukum.regulations.download', ['pso', $regulation]))
            ->assertOk();
    }

    public function test_an_article_can_be_deleted_without_deleting_its_regulation(): void
    {
        Storage::fake('local');
        $regulation = DasarHukumPeraturan::query()->create([
            'module_slug' => 'pso',
            'nama_peraturan' => 'Peraturan Pengujian',
            'document_path' => 'dasar-hukum/peraturan.pdf',
            'document_original_name' => 'peraturan.pdf',
        ]);
        $article = $regulation->pasal()->create([
            'nomor_pasal' => 'Pasal 1',
            'penjelasan' => 'Isi pasal untuk pengujian.',
        ]);

        $this->delete(route('dasar-hukum.articles.destroy', ['pso', $regulation, $article]))
            ->assertRedirect();

        $this->assertDatabaseMissing('dasar_hukum_pasals', ['id' => $article->id]);
        $this->assertDatabaseHas('dasar_hukum_peraturans', ['id' => $regulation->id]);
    }

    public function test_a_regulation_can_be_updated_and_deleted_with_its_document_and_articles(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('dasar-hukum/old.pdf', 'old document');
        $regulation = DasarHukumPeraturan::query()->create([
            'module_slug' => 'pso',
            'nama_peraturan' => 'Nama Lama',
            'document_path' => 'dasar-hukum/old.pdf',
            'document_original_name' => 'old.pdf',
        ]);
        $article = $regulation->pasal()->create([
            'nomor_pasal' => 'Pasal 1',
            'penjelasan' => 'Isi pasal.',
        ]);

        $this->put(route('dasar-hukum.regulations.update', ['pso', $regulation]), [
            'nama_peraturan' => 'Nama Baru',
            'document' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'),
        ])->assertRedirect();

        $regulation->refresh();
        $this->assertSame('Nama Baru', $regulation->nama_peraturan);
        $this->assertSame('new.pdf', $regulation->document_original_name);
        Storage::disk('local')->assertMissing('dasar-hukum/old.pdf');
        Storage::disk('local')->assertExists($regulation->document_path);

        $this->delete(route('dasar-hukum.regulations.destroy', ['pso', $regulation]))
            ->assertRedirect();

        $this->assertDatabaseMissing('dasar_hukum_peraturans', ['id' => $regulation->id]);
        $this->assertDatabaseMissing('dasar_hukum_pasals', ['id' => $article->id]);
        Storage::disk('local')->assertMissing($regulation->document_path);
    }

    public function test_a_regulation_cannot_be_modified_from_another_module(): void
    {
        $regulation = DasarHukumPeraturan::query()->create([
            'module_slug' => 'pso',
            'nama_peraturan' => 'Peraturan Pengujian',
            'document_path' => 'dasar-hukum/peraturan.pdf',
            'document_original_name' => 'peraturan.pdf',
        ]);

        $this->post(route('dasar-hukum.articles.store', ['tac', $regulation]), [
            'nomor_pasal' => 'Pasal 1',
            'penjelasan' => 'Isi pasal.',
        ])->assertNotFound();

        $this->assertSame(0, $regulation->pasal()->count());
    }

    public function test_global_search_finds_regulations_and_matching_articles_across_modules(): void
    {
        $regulation = DasarHukumPeraturan::query()->create([
            'module_slug' => 'tac',
            'nama_peraturan' => 'Regulasi Pencarian Global Unik',
            'document_path' => 'dasar-hukum/search.pdf',
            'document_original_name' => 'search.pdf',
        ]);
        $article = $regulation->pasal()->create([
            'nomor_pasal' => 'Pasal 987',
            'penjelasan' => 'Penjelasan berisi frasa-lacak-global untuk pengujian.',
        ]);
        DasarHukumPeraturan::query()->create([
            'module_slug' => 'pso',
            'nama_peraturan' => 'Frasa Nama Regulasi Global Unik',
            'document_path' => 'dasar-hukum/search-name.pdf',
            'document_original_name' => 'search-name.pdf',
        ]);

        $this->get(route('isu-strategis.search', ['q' => 'frasa-lacak-global']))
            ->assertOk()
            ->assertSee('Peraturan dan Pasal yang Ditemukan')
            ->assertSee('Regulasi Pencarian Global Unik')
            ->assertSee('Pasal 987')
            ->assertSee('frasa-lacak-global')
            ->assertSee('dasar-hukum/tac?open=' . $regulation->id . '&article=' . $article->id, false);

        $this->get(route('isu-strategis.search', ['q' => 'Nama Regulasi Global Unik']))
            ->assertOk()
            ->assertSee('Frasa Nama Regulasi Global Unik');

        $this->get(route('isu-strategis.search', ['q' => 'Pasal 987']))
            ->assertOk()
            ->assertSee('Regulasi Pencarian Global Unik')
            ->assertSee('Pasal 987');
    }
}

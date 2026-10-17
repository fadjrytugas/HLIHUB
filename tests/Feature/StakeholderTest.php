<?php

namespace Tests\Feature;

use App\Models\Stakeholder;
use App\Models\StakeholderMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StakeholderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_stakeholder_priorities_are_separated_into_switchable_tabs(): void
    {
        Stakeholder::query()->create([
            'nama' => 'Stakeholder Top Priority Tab Test',
            'prioritas' => 'TOP PRIORITY',
        ]);
        Stakeholder::query()->create([
            'nama' => 'Stakeholder Second Priority Tab Test',
            'prioritas' => '2ND PRIORITY',
        ]);

        $this->get(route('stakeholders.index'))
            ->assertOk()
            ->assertSee('Stakeholder Top Priority Tab Test')
            ->assertDontSee('Stakeholder Second Priority Tab Test')
            ->assertSee('2ND PRIORITY');

        $this->get(route('stakeholders.index', ['priority' => '2ND PRIORITY']))
            ->assertOk()
            ->assertSee('Stakeholder Second Priority Tab Test')
            ->assertDontSee('Stakeholder Top Priority Tab Test');
    }

    public function test_stakeholders_can_be_created_updated_and_deleted_independently(): void
    {
        $this->get(route('stakeholders.index'))
            ->assertOk()
            ->assertSee('Stakeholder')
            ->assertSee('TOP Priority')
            ->assertSee('Fungsi Instansi');

        $this->post(route('stakeholders.store'), [
            'nama' => 'Stakeholder CRUD Test Unique',
            'prioritas' => 'TOP PRIORITY',
            'fungsi_instansi' => 'Fungsi instansi test',
            'website_resmi' => 'https://example.test',
            'struktur_organisasi' => 'Struktur organisasi test',
            'alamat_kantor' => 'Alamat kantor test',
            'telepon' => '021123',
            'fax' => '021456',
            'email_perusahaan' => 'stakeholder@example.test',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $stakeholder = Stakeholder::query()
            ->where('nama', 'Stakeholder CRUD Test Unique')
            ->firstOrFail();
        $this->assertSame('TOP PRIORITY', $stakeholder->prioritas);
        $this->get(route('stakeholders.index'))
            ->assertOk()
            ->assertSee('Stakeholder CRUD Test Unique')
            ->assertSee('TOP PRIORITY');
        $this->get(route('stakeholders.show', $stakeholder))
            ->assertOk()
            ->assertSee('Fungsi instansi test')
            ->assertSee('https://example.test');

        $this->put(route('stakeholders.update', $stakeholder), [
            'nama' => 'Stakeholder CRUD Test Updated',
            'prioritas' => '2ND PRIORITY',
            'fungsi_instansi' => 'Fungsi diperbarui',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stakeholders', [
            'id' => $stakeholder->id,
            'nama' => 'Stakeholder CRUD Test Updated',
            'prioritas' => '2ND PRIORITY',
        ]);

        $this->delete(route('stakeholders.destroy', $stakeholder))
            ->assertRedirect();

        $this->assertDatabaseMissing('stakeholders', ['id' => $stakeholder->id]);
    }

    public function test_member_profile_and_photo_can_be_created_updated_and_deleted(): void
    {
        Storage::fake();
        $stakeholder = Stakeholder::query()->create([
            'nama' => 'Stakeholder Member Parent Test',
            'prioritas' => 'TOP PRIORITY',
        ]);

        $this->get(route('stakeholders.show', $stakeholder))
            ->assertOk()
            ->assertSee('Tambah Anggota')
            ->assertSee('Riwayat Pendidikan')
            ->assertSee('Foto Orang');

        $this->post(route('stakeholders.members.store', $stakeholder), [
            'nama_lengkap' => 'Anggota Stakeholder Test Unik',
            'jabatan' => 'Direktur',
            'nama_panggilan' => 'Pak Anggota',
            'nama_pasangan' => 'Nama Pasangan',
            'nama_anak' => 'Anak Pertama',
            'nama_keluarga_lainnya' => 'Keluarga lainnya',
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1980-01-02',
            'media_sosial' => '@anggota',
            'telepon_protokol' => '081111',
            'telepon_sekretaris' => '082222',
            'telepon_staff' => '083333',
            'hobi' => 'Membaca',
            'makanan_favorit' => 'Nasi goreng',
            'minuman_favorit' => 'Teh',
            'olahraga_favorit' => 'Tenis',
            'riwayat_pendidikan' => "S1 Universitas Test\nS2 Universitas Test",
            'sumber_data_url' => 'https://example.test/profil-anggota',
            'foto' => UploadedFile::fake()->image('anggota.jpg'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $member = StakeholderMember::query()
            ->where('nama_lengkap', 'Anggota Stakeholder Test Unik')
            ->firstOrFail();
        $this->assertSame('https://example.test/profil-anggota', $member->sumber_data_url);
        Storage::assertExists($member->foto_path);

        $this->get(route('stakeholders.members.photo', [$stakeholder, $member]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
        $this->get(route('stakeholders.show', $stakeholder))
            ->assertOk()
            ->assertSee('Sumber Data')
            ->assertSee('https://example.test/profil-anggota');

        $this->put(route('stakeholders.members.update', [$stakeholder, $member]), [
            'nama_lengkap' => 'Anggota Stakeholder Test Diperbarui',
            'jabatan' => 'Komisaris',
            'jenis_kelamin' => 'Laki-laki',
            'sumber_data_url' => 'https://example.test/profil-anggota-diperbarui',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertSame('Komisaris', $member->jabatan);
        $this->assertSame('https://example.test/profil-anggota-diperbarui', $member->sumber_data_url);
        Storage::assertExists($member->foto_path);

        $this->delete(route('stakeholders.members.destroy', [$stakeholder, $member]))
            ->assertRedirect();
        $this->assertDatabaseMissing('stakeholder_members', ['id' => $member->id]);
        Storage::assertMissing($member->foto_path);
    }

    public function test_member_source_data_must_be_a_valid_url(): void
    {
        $stakeholder = Stakeholder::query()->create([
            'nama' => 'Stakeholder Member Source URL Test',
            'prioritas' => 'TOP PRIORITY',
        ]);

        $this->from(route('stakeholders.show', $stakeholder))
            ->post(route('stakeholders.members.store', $stakeholder), [
                'nama_lengkap' => 'Anggota Sumber Data Test',
                'sumber_data_url' => 'bukan-url',
            ])
            ->assertRedirect(route('stakeholders.show', $stakeholder))
            ->assertSessionHasErrors('sumber_data_url');

        $this->assertDatabaseMissing('stakeholder_members', [
            'stakeholder_id' => $stakeholder->id,
            'nama_lengkap' => 'Anggota Sumber Data Test',
        ]);
    }

    public function test_stakeholder_names_must_be_unique(): void
    {
        Stakeholder::query()->create([
            'nama' => 'Stakeholder Nama Duplikat Test',
            'prioritas' => '2ND PRIORITY',
        ]);

        $this->from(route('stakeholders.index'))
            ->post(route('stakeholders.store'), [
                'nama' => 'Stakeholder Nama Duplikat Test',
                'prioritas' => '2ND PRIORITY',
            ])
            ->assertRedirect(route('stakeholders.index'))
            ->assertSessionHasErrors('nama');
    }

    public function test_a_member_cannot_be_modified_through_another_stakeholder(): void
    {
        $stakeholder = Stakeholder::query()->create([
            'nama' => 'Stakeholder Member Ownership Test',
            'prioritas' => '2ND PRIORITY',
        ]);
        $otherStakeholder = Stakeholder::query()->create([
            'nama' => 'Stakeholder Member Other Parent Test',
            'prioritas' => '2ND PRIORITY',
        ]);
        $member = $stakeholder->members()->create(['nama_lengkap' => 'Anggota Kepemilikan Test']);

        $this->delete(route('stakeholders.members.destroy', [$otherStakeholder, $member]))
            ->assertNotFound();

        $this->assertDatabaseHas('stakeholder_members', ['id' => $member->id]);
    }
}

<div class="stakeholder-form-grid">
    <label class="legal-field">Nama Lengkap <b>*</b>
        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $member?->nama_lengkap) }}" maxlength="255" required>
    </label>
    <label class="legal-field">Jabatan
        <input type="text" name="jabatan" value="{{ old('jabatan', $member?->jabatan) }}" maxlength="255">
    </label>
    <label class="legal-field">Nama Panggilan
        <input type="text" name="nama_panggilan" value="{{ old('nama_panggilan', $member?->nama_panggilan) }}" maxlength="255">
    </label>
    <label class="legal-field">Jenis Kelamin
        <select name="jenis_kelamin">
            <option value="">Pilih jenis kelamin</option>
            <option value="Laki-laki" @selected(old('jenis_kelamin', $member?->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
            <option value="Perempuan" @selected(old('jenis_kelamin', $member?->jenis_kelamin) === 'Perempuan')>Perempuan</option>
        </select>
    </label>
    <label class="legal-field">Tempat Lahir
        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $member?->tempat_lahir) }}" maxlength="255">
    </label>
    <label class="legal-field">Tanggal Lahir
        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $member?->tanggal_lahir?->format('Y-m-d')) }}">
    </label>
    <label class="legal-field">Nama Istri/Suami
        <input type="text" name="nama_pasangan" value="{{ old('nama_pasangan', $member?->nama_pasangan) }}" maxlength="255">
    </label>
    <label class="legal-field">Nama Anak
        <textarea name="nama_anak" rows="2">{{ old('nama_anak', $member?->nama_anak) }}</textarea>
    </label>
    <label class="legal-field">Nama Keluarga Lainnya
        <textarea name="nama_keluarga_lainnya" rows="2">{{ old('nama_keluarga_lainnya', $member?->nama_keluarga_lainnya) }}</textarea>
    </label>
    <label class="legal-field">Media Sosial
        <textarea name="media_sosial" rows="2" placeholder="Akun atau tautan media sosial">{{ old('media_sosial', $member?->media_sosial) }}</textarea>
    </label>
    <label class="legal-field">No. Tlp. Protokol
        <input type="text" name="telepon_protokol" value="{{ old('telepon_protokol', $member?->telepon_protokol) }}" maxlength="100">
    </label>
    <label class="legal-field">No. Tlp. Sekretaris
        <input type="text" name="telepon_sekretaris" value="{{ old('telepon_sekretaris', $member?->telepon_sekretaris) }}" maxlength="100">
    </label>
    <label class="legal-field">No. Tlp. Staff
        <input type="text" name="telepon_staff" value="{{ old('telepon_staff', $member?->telepon_staff) }}" maxlength="100">
    </label>
    <label class="legal-field">Hobi
        <textarea name="hobi" rows="2">{{ old('hobi', $member?->hobi) }}</textarea>
    </label>
    <label class="legal-field">Makanan Favorit
        <textarea name="makanan_favorit" rows="2">{{ old('makanan_favorit', $member?->makanan_favorit) }}</textarea>
    </label>
    <label class="legal-field">Minuman Favorit
        <textarea name="minuman_favorit" rows="2">{{ old('minuman_favorit', $member?->minuman_favorit) }}</textarea>
    </label>
    <label class="legal-field">Olahraga Favorit
        <textarea name="olahraga_favorit" rows="2">{{ old('olahraga_favorit', $member?->olahraga_favorit) }}</textarea>
    </label>
    <label class="legal-field stakeholder-form-grid__wide">Riwayat Pendidikan
        <textarea name="riwayat_pendidikan" rows="4" placeholder="Tuliskan riwayat pendidikan, satu jenjang per baris">{{ old('riwayat_pendidikan', $member?->riwayat_pendidikan) }}</textarea>
    </label>
    <label class="legal-field stakeholder-form-grid__wide">Link Sumber Data
        <input type="url" name="sumber_data_url" value="{{ old('sumber_data_url', $member?->sumber_data_url) }}" maxlength="2048" placeholder="https://contoh.go.id/profil-anggota">
        <small>Masukkan tautan halaman yang menjadi sumber informasi anggota.</small>
    </label>
    <label class="legal-field stakeholder-form-grid__wide">Foto Orang
        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
        <small>JPG, PNG, atau WebP; maksimal 5 MB.@if ($member) Foto saat ini dapat dilihat di kartu anggota.@endif</small>
    </label>
</div>

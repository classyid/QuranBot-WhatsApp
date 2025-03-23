# Quran WhatsApp Bot

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Quran WhatsApp Bot adalah webhook sederhana untuk WhatsApp yang dibangun menggunakan PHP untuk memberikan akses mudah dan cepat ke Al-Quran langsung melalui platform WhatsApp. Bot ini memanfaatkan API Al-Quran untuk menyediakan berbagai fitur termasuk membaca surah, ayat, mencari kata kunci dalam Al-Quran, dan mendengarkan audio tilawah.

## Fitur

- 📋 **Daftar Surah**: Menampilkan daftar lengkap 114 surah dalam Al-Quran
- 📖 **Info Surah**: Menampilkan informasi lengkap tentang suatu surah
- 🔍 **Baca Ayat**: Menampilkan ayat tertentu dengan teks Arab, Latin, dan terjemahan
- 🔎 **Pencarian**: Mencari ayat berdasarkan kata kunci
- 🔢 **Info Juz**: Menampilkan informasi tentang juz tertentu
- 🎧 **Audio Ayat**: Mendengarkan audio ayat tertentu
- 📚 **Tafsir**: Menampilkan tafsir surah
- 🎲 **Ayat Acak**: Menampilkan ayat acak dari Al-Quran
- 📖 **Surah Acak**: Menampilkan surah acak dari Al-Quran

## Cara Penggunaan

Berikut adalah daftar perintah yang tersedia:

1. `quran` atau `alquran` - Menampilkan bantuan dan daftar perintah
2. `surah list` - Menampilkan daftar semua surah
3. `surah {nomor}` - Menampilkan info surah dan ayat pertama (contoh: `surah 1`)
4. `ayat {surah}:{ayat}` - Menampilkan ayat tertentu (contoh: `ayat 1:1`)
5. `juz {nomor}` - Menampilkan ayat pertama dari juz tertentu (contoh: `juz 1`)
6. `cari {kata kunci}` - Mencari ayat berdasarkan kata kunci (contoh: `cari perang`)
7. `audio {surah}:{ayat}` - Mendapatkan audio ayat tertentu (contoh: `audio 1:1`)
8. `tafsir {surah}` - Menampilkan tafsir surah (contoh: `tafsir 1`)
9. `random` - Menampilkan ayat acak dari Al-Quran
10. `random surah` - Menampilkan surah acak dari Al-Quran

## Instalasi

### Prasyarat
- PHP 7.2 atau lebih tinggi
- Akses ke layanan WhatsApp API / Server WhatsApp Bot

### Langkah-langkah Instalasi

1. Clone repository ini:
```bash
git clone https://github.com/classyid/quran-whatsapp-bot.git
```

2. Pindah ke direktori project:
```bash
cd quran-whatsapp-bot
```

3. Upload file ke server web Anda

4. Pastikan file `ResponWebhookFormatter.php` berada di server Anda

5. Konfigurasikan webhook URL di pengaturan WhatsApp API Anda

6. Uji dengan mengirim pesan `quran` ke nomor WhatsApp yang terhubung dengan webhook

## Konfigurasi 

Anda dapat mengubah URL API Al-Quran jika diperlukan:

```php
// URL API Al-Quran
$api_url = "https://script.google.com/macros/s/AKfycbyDhS4WMtLO2sSvvKImE6tq4gRcazMPGPkQDzjmIu2xDAeiVnD3mdsRfAetYFvi2RQUjw/exec";
```

## Kontribusi

Kontribusi sangat dipersilakan! Jika Anda ingin berkontribusi, silakan ikuti langkah-langkah berikut:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/amazing-feature`)
3. Commit perubahan Anda (`git commit -m 'Add some amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buka Pull Request

## Lisensi

Didistribusikan di bawah Lisensi MIT. Lihat `LICENSE` untuk informasi lebih lanjut.

## Kredit

- Kontributor dan pengembang yang telah membantu dalam pengembangan bot ini

## Kontak

Andri Wiratmono - kontak@classy.id

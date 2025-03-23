<?php

// Atur zona waktu ke Asia/Jakarta (GMT+7)
date_default_timezone_set('Asia/Jakarta');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');

require_once 'ResponWebhookFormatter.php';
header('content-type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) die('URL ini untuk webhook.');

file_put_contents('whatsapp.txt', '[' . date('Y-m-d H:i:s') . "]\n" . json_encode($data) . "\n\n", FILE_APPEND);
$message = strtolower($data['message']); // pesan masuk dari whatsapp
$from = strtolower($data['from']); // nomor pengirim
$bufferimage = isset($data['bufferImage']) ? $data['bufferImage'] : null; // buffer gambar jika pesan berisi gambar

$respon = false;
$responFormatter = new ResponWebhookFormater();

// URL API Al-Quran
$api_url = "https://script.google.com/macros/s/AKfycbyDhS4WMtLO2sSvvKImE6tq4gRcazMPGPkQDzjmIu2xDAeiVnD3mdsRfAetYFvi2RQUjw/exec";

// Bantuan/Help
if ($message == 'quran' || $message == 'alquran' || $message == 'help quran') {
    $respon = $responFormatter->bold("🕋 PANDUAN AL-QURAN BOT 🕋")
        ->line("")
        ->bold("DAFTAR PERINTAH:")
        ->line("1. *surah list* - Menampilkan daftar semua surah")
        ->line("2. *surah {nomor}* - Menampilkan info surah dan ayat pertama (contoh: surah 1)")
        ->line("3. *ayat {surah}:{ayat}* - Menampilkan ayat tertentu (contoh: ayat 1:1)")
        ->line("4. *juz {nomor}* - Menampilkan ayat pertama dari juz tertentu")
        ->line("5. *cari {kata kunci}* - Mencari ayat berdasarkan kata kunci")
        ->line("6. *audio {surah}:{ayat}* - Mendapatkan audio ayat tertentu")
        ->line("7. *tafsir {surah}* - Menampilkan tafsir surah")
        ->line("8. *random* - Menampilkan ayat acak dari Al-Quran")
        ->line("9. *random surah* - Menampilkan surah acak dari Al-Quran")
        ->line("")
        ->line("Silakan gunakan perintah di atas untuk menjelajahi Al-Quran 🤲")
        ->responAsText();
}

// Menampilkan daftar surah
if ($message == 'surah list' || $message == 'daftar surah') {
    $url = $api_url . "?action=getAllSurah";
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success') {
        $responFormatter->bold("📖 DAFTAR SURAH AL-QURAN 📖")->line("");
        
        // Kelompokkan surah dalam beberapa bagian untuk memudahkan pembacaan
        $total_surah = count($json['data']);
        $surah_per_section = 10;
        $section_count = ceil($total_surah / $surah_per_section);
        
        for ($i = 0; $i < $section_count; $i++) {
            $section_title = "Surah " . (($i * $surah_per_section) + 1) . " - " . min((($i + 1) * $surah_per_section), $total_surah);
            
            $rows = [];
            for ($j = ($i * $surah_per_section); $j < min((($i + 1) * $surah_per_section), $total_surah); $j++) {
                $surah = $json['data'][$j];
                $rows[] = [
                    'title' => $surah['number'] . ". " . $surah['name_id'] . " (" . $surah['name_short'] . ")",
                    'rowId' => 'surah_' . $surah['number'],
                    'description' => $surah['translation_id'] . ' - ' . $surah['number_of_verses'] . ' ayat'
                ];
            }
            
            // Tambahkan surah ke respons teks
            for ($j = ($i * $surah_per_section); $j < min((($i + 1) * $surah_per_section), $total_surah); $j++) {
                $surah = $json['data'][$j];
                $responFormatter->line($surah['number'] . ". " . $surah['name_id'] . " (" . $surah['name_short'] . ") - " . $surah['translation_id'] . ' - ' . $surah['number_of_verses'] . ' ayat');
            }
            
            // Tambahkan baris kosong antara setiap kelompok
            if ($i < $section_count - 1) {
                $responFormatter->line("");
            }
        }
        
        $responFormatter->line("")->line("Ketik 'surah {nomor}' untuk melihat isi surah.");
        $respon = $responFormatter->responAsText();
    } else {
        $respon = $responFormatter->line("❌ Terjadi kesalahan saat mengambil daftar surah.")->responAsText();
    }
}

// Menampilkan surah berdasarkan nomor
if (preg_match('/^surah (\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $url = $api_url . "?action=getSurah&number=" . $surah_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        $ayat_pertama = $json['data']['ayat'][0];
        
        $responFormatter->bold("📖 " . $surah['name_id'] . " (" . $surah['name_short'] . ")")
            ->line($surah['name_long'])
            ->line("")
            ->bold("Informasi Surah:")
            ->line("Nomor: " . $surah['number'])
            ->line("Arti: " . $surah['translation_id'])
            ->line("Jumlah Ayat: " . $surah['number_of_verses'])
            ->line("Diturunkan di: " . $surah['revelation_id'])
            ->line("")
            ->bold("Ayat Pertama:")
            ->line($ayat_pertama['Arab'])
            ->line("Latin: " . $ayat_pertama['Latin'])
            ->line("Arti: " . $ayat_pertama['Text'])
            ->line("")
            ->line("Ketik 'ayat " . $surah_number . ":{nomor ayat}' untuk membaca ayat tertentu")
            ->line("Ketik 'tafsir " . $surah_number . "' untuk membaca tafsir surah");
        
        $responFormatter->line("")
            ->line("Ketik 'tafsir " . $surah_number . "' untuk membaca tafsir surah")
            ->line("Ketik 'audio " . $surah_number . ":1' untuk mendengarkan audio ayat pertama");
            
        $respon = $responFormatter->responAsText();
    } else {
        $respon = $responFormatter->line("❌ Surah dengan nomor " . $surah_number . " tidak ditemukan.")
            ->line("Ketik 'surah list' untuk melihat daftar surah yang tersedia.")
            ->responAsText();
    }
}

// Menampilkan ayat berdasarkan surah dan nomor ayat
if (preg_match('/^ayat (\d+):(\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $ayat_number = $matches[2];
    $url = $api_url . "?action=getAyat&surah=" . $surah_number . "&ayat=" . $ayat_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
        $surah = $json['data']['surah'];
        $ayat = $json['data']['ayat'];
        
        $responFormatter->bold("📖 " . $surah['name_id'] . " (" . $surah['name_short'] . ") - Ayat " . $ayat_number)
            ->line("")
            ->line($ayat['Arab'])
            ->line("")
            ->line($ayat['Latin'])
            ->line("")
            ->bold("Arti:")
            ->line($ayat['Text'])
            ->line("")
            ->line("Halaman: " . $ayat['Page'] . " | Juz: " . $ayat['Juz']);
            
        $responFormatter->line("")
            ->line("Ketik 'audio " . $surah_number . ":" . $ayat_number . "' untuk mendengarkan audio ayat ini")
            ->line("Ketik 'ayat " . $surah_number . ":" . max(1, $ayat_number - 1) . "' untuk ayat sebelumnya")
            ->line("Ketik 'ayat " . $surah_number . ":" . ($ayat_number + 1) . "' untuk ayat berikutnya");
            
        $respon = $responFormatter->responAsText();
    } else {
        $respon = $responFormatter->line("❌ Ayat tidak ditemukan.")
            ->line("Periksa kembali nomor surah dan ayat.")
            ->line("Format: ayat {nomor_surah}:{nomor_ayat}")
            ->line("Contoh: ayat 1:1")
            ->responAsText();
    }
}

// Mencari ayat berdasarkan kata kunci
if (preg_match('/^cari (.+)$/', $message, $matches)) {
    $keyword = $matches[1];
    $url = $api_url . "?action=search&q=" . urlencode($keyword);
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success') {
        $total_hasil = $json['count'];
        $responFormatter->bold("🔍 HASIL PENCARIAN: '" . $keyword . "'")
            ->line("Ditemukan " . $total_hasil . " ayat")
            ->line("");
        
        if ($total_hasil > 0) {
            // Tampilkan 3 hasil pertama
            $max_results = min(3, $total_hasil);
            for ($i = 0; $i < $max_results; $i++) {
                $ayat = $json['data'][$i];
                $surah_name = getSurahName($ayat['Surah']);
                
                $responFormatter->bold("Q.S. " . $surah_name . " [" . $ayat['Surah'] . ":" . $ayat['Ayah'] . "]")
                    ->line($ayat['Arab'])
                    ->line("")
                    ->line($ayat['Text'])
                    ->line("")
                    ->line("Ketik 'ayat " . $ayat['Surah'] . ":" . $ayat['Ayah'] . "' untuk detail lengkap")
                    ->line("");
            }
            
            if ($total_hasil > 3) {
                $responFormatter->bold("... dan " . ($total_hasil - 3) . " ayat lainnya")
                    ->line("")
                    ->italic("Kata kunci '" . $keyword . "' juga ditemukan dalam ayat-ayat lain. Silakan perjelas pencarian Anda untuk hasil yang lebih spesifik.");
            }
        }
        
        $respon = $responFormatter->responAsText();
    } else {
        $respon = $responFormatter->line("❌ Tidak dapat melakukan pencarian.")
            ->line("Silakan coba dengan kata kunci lain.")
            ->responAsText();
    }
}

// Menampilkan juz berdasarkan nomor
if (preg_match('/^juz (\d+)$/', $message, $matches)) {
    $juz_number = $matches[1];
    
    if ($juz_number < 1 || $juz_number > 30) {
        $respon = $responFormatter->line("❌ Nomor juz harus antara 1-30.")
            ->responAsText();
    } else {
        $url = $api_url . "?action=getJuz&number=" . $juz_number;
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success') {
            $total_ayat = $json['count'];
            $first_ayat = $json['data'][0];
            $last_ayat = $json['data'][count($json['data']) - 1];
            
            $responFormatter->bold("📖 JUZ " . $juz_number)
                ->line("Total " . $total_ayat . " ayat")
                ->line("")
                ->bold("Dimulai dari:")
                ->line("Q.S. " . $first_ayat['Surah'] . ":" . $first_ayat['Ayah'] . " (" . getSurahName($first_ayat['Surah']) . ")")
                ->line("")
                ->bold("Sampai:")
                ->line("Q.S. " . $last_ayat['Surah'] . ":" . $last_ayat['Ayah'] . " (" . getSurahName($last_ayat['Surah']) . ")")
                ->line("")
                ->bold("Ayat Pertama:")
                ->line($first_ayat['Arab'])
                ->line("")
                ->line($first_ayat['Text'])
                ->line("")
                ->line("Ketik 'ayat {surah}:{ayat}' untuk membaca ayat tertentu");
                
            $respon = $responFormatter->responAsText();
        } else {
            $respon = $responFormatter->line("❌ Terjadi kesalahan saat mengambil data juz.")
                ->responAsText();
        }
    }
}

// Mendapatkan audio ayat
if (preg_match('/^audio (\d+):(\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $ayat_number = $matches[2];
    
    // Dapatkan info surah dulu
    $url_surah = $api_url . "?action=getSurah&number=" . $surah_number;
    $response_surah = file_get_contents($url_surah);
    $json_surah = json_decode($response_surah, true);
    
    if ($json_surah['status'] == 'success' && isset($json_surah['data']['surah'])) {
        $surah_info = $json_surah['data']['surah'];
        
        // Kemudian dapatkan audio ayat
        $url = $api_url . "?action=getAudio&surah=" . $surah_number . "&ayat=" . $ayat_number . "&qari=default";
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success' && isset($json['audio_url'])) {
            $ayat_info = $json['ayat_info'];
            $audio_url = $json['audio_url'];
            
            $caption = "🎧 *AUDIO AL-QURAN*\n\n";
            $caption .= "📖 *Surah " . $surah_info['name_id'] . " (" . $surah_info['name_short'] . ")*\n";
            $caption .= "Ayat " . $ayat_number . " dari " . $surah_info['number_of_verses'] . " ayat\n\n";
            $caption .= $ayat_info['Arab'] . "\n\n";
            $caption .= "*Latin:* " . $ayat_info['Latin'] . "\n\n";
            $caption .= "*Arti:* " . $ayat_info['Text'] . "\n\n";
            $caption .= "Ketik 'ayat " . $surah_number . ":" . ($ayat_number + 1) . "' untuk ayat berikutnya";
            
            // Kirim audio dengan caption
            $respon = $responFormatter->line($caption)->responAsMedia($audio_url, 'audio');
        } else {
            $respon = $responFormatter->line("❌ Audio tidak ditemukan.")
                ->line("Periksa kembali nomor surah dan ayat.")
                ->responAsText();
        }
    } else {
        $respon = $responFormatter->line("❌ Surah dengan nomor " . $surah_number . " tidak ditemukan.")
            ->responAsText();
    }
}

// Menampilkan tafsir surah
if (preg_match('/^tafsir (\d+)$/', $message, $matches)) {
    $surah_number = $matches[1];
    $url = $api_url . "?action=getSurah&number=" . $surah_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        
        $responFormatter->bold("📚 TAFSIR SURAH " . $surah['name_id'])
            ->line($surah['name_long'])
            ->line("")
            ->line($surah['tafsir'])
            ->line("")
            ->line("Ketik 'surah " . $surah_number . "' untuk membaca ayat-ayat surah ini");
            
        $respon = $responFormatter->responAsText();
    } else {
        $respon = $responFormatter->line("❌ Surah dengan nomor " . $surah_number . " tidak ditemukan.")
            ->responAsText();
    }
}

// Fungsi bantuan untuk mendapatkan nama surah
function getSurahName($surah_number) {
    // Ideally, we'd cache this data to avoid repeated API calls
    $surah_names = [
        1 => "Al-Fatihah", 2 => "Al-Baqarah", 3 => "Ali 'Imran", 4 => "An-Nisa'",
        5 => "Al-Ma'idah", 6 => "Al-An'am", 7 => "Al-A'raf", 8 => "Al-Anfal",
        9 => "At-Taubah", 10 => "Yunus", 11 => "Hud", 12 => "Yusuf",
        13 => "Ar-Ra'd", 14 => "Ibrahim", 15 => "Al-Hijr", 16 => "An-Nahl",
        17 => "Al-Isra'", 18 => "Al-Kahf", 19 => "Maryam", 20 => "Ta Ha",
        21 => "Al-Anbiya'", 22 => "Al-Hajj", 23 => "Al-Mu'minun", 24 => "An-Nur",
        25 => "Al-Furqan", 26 => "Asy-Syu'ara'", 27 => "An-Naml", 28 => "Al-Qasas",
        29 => "Al-'Ankabut", 30 => "Ar-Rum", 31 => "Luqman", 32 => "As-Sajdah",
        33 => "Al-Ahzab", 34 => "Saba'", 35 => "Fatir", 36 => "Ya Sin",
        37 => "As-Saffat", 38 => "Sad", 39 => "Az-Zumar", 40 => "Gafir",
        41 => "Fussilat", 42 => "Asy-Syura", 43 => "Az-Zukhruf", 44 => "Ad-Dukhan",
        45 => "Al-Jasiyah", 46 => "Al-Ahqaf", 47 => "Muhammad", 48 => "Al-Fath",
        49 => "Al-Hujurat", 50 => "Qaf", 51 => "Az-Zariyat", 52 => "At-Tur",
        53 => "An-Najm", 54 => "Al-Qamar", 55 => "Ar-Rahman", 56 => "Al-Waqi'ah",
        57 => "Al-Hadid", 58 => "Al-Mujadilah", 59 => "Al-Hasyr", 60 => "Al-Mumtahanah",
        61 => "As-Saff", 62 => "Al-Jumu'ah", 63 => "Al-Munafiqun", 64 => "At-Tagabun",
        65 => "At-Talaq", 66 => "At-Tahrim", 67 => "Al-Mulk", 68 => "Al-Qalam",
        69 => "Al-Haqqah", 70 => "Al-Ma'arij", 71 => "Nuh", 72 => "Al-Jinn",
        73 => "Al-Muzzammil", 74 => "Al-Muddassir", 75 => "Al-Qiyamah", 76 => "Al-Insan",
        77 => "Al-Mursalat", 78 => "An-Naba'", 79 => "An-Nazi'at", 80 => "'Abasa",
        81 => "At-Takwir", 82 => "Al-Infitar", 83 => "Al-Mutaffifin", 84 => "Al-Insyiqaq",
        85 => "Al-Buruj", 86 => "At-Tariq", 87 => "Al-A'la", 88 => "Al-Gasyiyah",
        89 => "Al-Fajr", 90 => "Al-Balad", 91 => "Asy-Syams", 92 => "Al-Lail",
        93 => "Ad-Duha", 94 => "Asy-Syarh", 95 => "At-Tin", 96 => "Al-'Alaq",
        97 => "Al-Qadr", 98 => "Al-Bayyinah", 99 => "Az-Zalzalah", 100 => "Al-'Adiyat",
        101 => "Al-Qari'ah", 102 => "At-Takasur", 103 => "Al-'Asr", 104 => "Al-Humazah",
        105 => "Al-Fil", 106 => "Quraisy", 107 => "Al-Ma'un", 108 => "Al-Kausar",
        109 => "Al-Kafirun", 110 => "An-Nasr", 111 => "Al-Lahab", 112 => "Al-Ikhlas",
        113 => "Al-Falaq", 114 => "An-Nas"
    ];
    
    return isset($surah_names[$surah_number]) ? $surah_names[$surah_number] : "Surah " . $surah_number;
}

// Fitur tambahan: Ayat Random
if ($message == 'random') {
    // Generate random surah and ayat
    $random_surah = rand(1, 114);
    
    // Get info about the surah to know max ayat
    $url = $api_url . "?action=getSurah&number=" . $random_surah;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        $max_ayat = $surah['number_of_verses'];
        $random_ayat = rand(1, $max_ayat);
        
        // Get the random ayat
        $url = $api_url . "?action=getAyat&surah=" . $random_surah . "&ayat=" . $random_ayat;
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
            $ayat = $json['data']['ayat'];
            
            $responFormatter->bold("📖 AYAT ACAK")
                ->line("")
                ->bold("Q.S. " . $surah['name_id'] . " [" . $random_surah . ":" . $random_ayat . "]")
                ->line("")
                ->line($ayat['Arab'])
                ->line("")
                ->line($ayat['Latin'])
                ->line("")
                ->bold("Arti:")
                ->line($ayat['Text'])
                ->line("")
                ->line("Ketik 'ayat " . $random_surah . ":" . $random_ayat . "' untuk melihat detail ayat ini")
                ->line("Ketik 'audio " . $random_surah . ":" . $random_ayat . "' untuk mendengarkan ayat ini");
                
            $respon = $responFormatter->responAsText();
        }
    }
    
    if (!$respon) {
        $respon = $responFormatter->line("❌ Terjadi kesalahan saat mengambil ayat acak.")
            ->line("Silakan coba lagi.")
            ->responAsText();
    }
}

// Fitur tambahan: Ayat Hari Ini
if ($message == 'today') {
    // Menggunakan tanggal hari ini sebagai seed untuk mendapatkan ayat yang konsisten per hari
    $today = date('Y-m-d');
    $seed = crc32($today);
    srand($seed);
    
    $surah_number = rand(1, 114);
    
    // Get info about the surah to know max ayat
    $url = $api_url . "?action=getSurah&number=" . $surah_number;
    $response = file_get_contents($url);
    $json = json_decode($response, true);
    
    if ($json['status'] == 'success' && isset($json['data']['surah'])) {
        $surah = $json['data']['surah'];
        $max_ayat = $surah['number_of_verses'];
        $ayat_number = rand(1, $max_ayat);
        
        // Get the ayat
        $url = $api_url . "?action=getAyat&surah=" . $surah_number . "&ayat=" . $ayat_number;
        $response = file_get_contents($url);
        $json = json_decode($response, true);
        
        if ($json['status'] == 'success' && isset($json['data']['ayat'])) {
            $ayat = $json['data']['ayat'];
            
            $responFormatter->bold("📅 AYAT PILIHAN HARI INI (" . date('d-m-Y') . ")")
                ->line("")
                ->bold("Q.S. " . $surah['name_id'] . " [" . $surah_number . ":" . $ayat_number . "]")
                ->line("")
                ->line($ayat['Arab'])
                ->line("")
                ->line($ayat['Text'])
                ->line("")
                ->italic("\"Semoga ayat ini membawa manfaat dan berkah untuk hari Anda\"")
                ->line("")
                ->line("Ketik 'tafsir " . $surah_number . "' untuk membaca tafsir surah ini");
                
            $respon = $responFormatter->responAsText();
        }
    }
    
    if (!$respon) {
        $respon = $responFormatter->line("❌ Terjadi kesalahan saat mengambil ayat pilihan hari ini.")
            ->line("Silakan coba lagi.")
            ->responAsText();
    }
    
    // Reset random seed
    srand();
}

// Fitur tambahan: Jadwal Sholat
if (preg_match('/^sholat (.+)$/', $message, $matches)) {
    $kota = $matches[1];
    // Ini hanya simulasi, dalam implementasi nyata Anda perlu menggunakan API jadwal sholat
    // seperti https://api.myquran.com/ atau https://api.pray.zone/
    
    // Simulasi data jadwal sholat
    $waktu_sholat = [
        'subuh' => date('H:i', strtotime('04:' . rand(10, 59))),
        'dzuhur' => date('H:i', strtotime('12:' . rand(0, 15))),
        'ashar' => date('H:i', strtotime('15:' . rand(10, 30))),
        'maghrib' => date('H:i', strtotime('18:' . rand(0, 15))),
        'isya' => date('H:i', strtotime('19:' . rand(15, 30)))
    ];
    
    $responFormatter->bold("🕌 JADWAL SHOLAT")
        ->line("Kota: " . ucwords($kota))
        ->line("Tanggal: " . date('d-m-Y'))
        ->line("")
        ->line("Subuh: " . $waktu_sholat['subuh'])
        ->line("Dzuhur: " . $waktu_sholat['dzuhur'])
        ->line("Ashar: " . $waktu_sholat['ashar'])
        ->line("Maghrib: " . $waktu_sholat['maghrib'])
        ->line("Isya: " . $waktu_sholat['isya'])
        ->line("")
        ->italic("*Jadwal ini hanya perkiraan. Silakan periksa jadwal resmi setempat.")
        ->line("")
        ->line("Ketik 'today' untuk mendapatkan ayat pilihan hari ini");
        
    $respon = $responFormatter->responAsText();
}

// save respon to file
if ($respon) {
    file_put_contents('respon.txt', '[' . date('Y-m-d H:i:s') . "]\n" . $respon . "\n\n", FILE_APPEND);
}
echo $respon;
?>

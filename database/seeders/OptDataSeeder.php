<?php
/**
 * OPT Data Seeder
 * Seeds the master_opt table with real Indonesian OPT (Organisme Pengganggu Tumbuhan) data
 * Based on references from BPTP, Balitbangtan, and other official Indonesian agricultural sources
 */

require_once __DIR__ . '/../../config/database.php';

echo "<h2>OPT Data Seeder</h2>\n";
echo "<pre>\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Real Indonesian OPT data
    $optData = [
        // === HAMA (Pests) ===
        [
            'kode_opt' => 'H001',
            'nama_opt' => 'Wereng Coklat',
            'nama_ilmiah' => 'Nilaparvata lugens',
            'nama_lokal' => 'Wereng Coklat, Brown Planthopper',
            'jenis' => 'Hama',
            'kingdom' => 'Animalia',
            'filum' => 'Arthropoda',
            'kelas' => 'Insecta',
            'ordo' => 'Hemiptera',
            'famili' => 'Delphacidae',
            'genus' => 'Nilaparvata',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sangat Tinggi',
            'deskripsi' => 'Wereng coklat merupakan hama utama tanaman padi di Indonesia. Serangga ini mengisap cairan tanaman sehingga menyebabkan daun dan batang menguning (hopperburn). Populasi tinggi dapat menyebabkan puso (gagal panen total).',
            'etl_acuan' => 10,
            'rekomendasi' => 'Penggunaan varietas tahan wereng, pengendalian hayati dengan parasitoid, rotasi varietas, tidak menggunakan insektisida berlebihan',
            'referensi' => 'BPTP Jawa Timur, Balitbangtan'
        ],
        [
            'kode_opt' => 'H002',
            'nama_opt' => 'Penggerek Batang Padi',
            'nama_ilmiah' => 'Scirpophaga incertulas',
            'nama_lokal' => 'Sundep, Beluk',
            'jenis' => 'Hama',
            'kingdom' => 'Animalia',
            'filum' => 'Arthropoda',
            'kelas' => 'Insecta',
            'ordo' => 'Lepidoptera',
            'famili' => 'Crambidae',
            'genus' => 'Scirpophaga',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Tinggi',
            'deskripsi' => 'Larva menggerek batang padi menyebabkan sundep (fase vegetatif) atau beluk (fase generatif). Gejala sundep: tunas mati. Gejala beluk: malai hampa berwarna putih.',
            'etl_acuan' => 5,
            'rekomendasi' => 'Pengolahan tanah sempurna, pemasangan perangkap feromon, penggunaan parasitoid Trichogramma, aplikasi insektisida tepat waktu',
            'referensi' => 'BPTP Jawa Timur'
        ],
        [
            'kode_opt' => 'H003',
            'nama_opt' => 'Walang Sangit',
            'nama_ilmiah' => 'Leptocorisa oratorius',
            'nama_lokal' => 'Walang Sangit, Rice Bug',
            'jenis' => 'Hama',
            'kingdom' => 'Animalia',
            'filum' => 'Arthropoda',
            'kelas' => 'Insecta',
            'ordo' => 'Hemiptera',
            'famili' => 'Alydidae',
            'genus' => 'Leptocorisa',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sedang',
            'deskripsi' => 'Serangga yang menghisap cairan bulir padi yang sedang mengisi. Menyebabkan bulir hampa atau beras menjadi berwarna hitam (spotty grain). Memiliki bau tidak sedap bila diganggu.',
            'etl_acuan' => 2,
            'rekomendasi' => 'Sanitasi lahan, pengendalian gulma, penggunaan insektisida kontak, pemanfaatan musuh alami',
            'referensi' => 'Balitbangtan'
        ],
        [
            'kode_opt' => 'H004',
            'nama_opt' => 'Tikus Sawah',
            'nama_ilmiah' => 'Rattus argentiventer',
            'nama_lokal' => 'Tikus Sawah',
            'jenis' => 'Hama',
            'kingdom' => 'Animalia',
            'filum' => 'Chordata',
            'kelas' => 'Mammalia',
            'ordo' => 'Rodentia',
            'famili' => 'Muridae',
            'genus' => 'Rattus',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sangat Tinggi',
            'deskripsi' => 'Hama vertebrata paling merusak pada pertanaman padi. Menyerang mulai dari persemaian hingga panen. Dapat menyebabkan kerusakan total area pertanaman.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Gropyokan (berburu massal), pemasangan TBS (Trap Barrier System), penggunaan rodentisida, pembersihan sarang, tanam serempak',
            'referensi' => 'BPTP Jawa Timur'
        ],
        [
            'kode_opt' => 'H005',
            'nama_opt' => 'Ulat Grayak',
            'nama_ilmiah' => 'Spodoptera litura',
            'nama_lokal' => 'Ulat Grayak, Army Worm',
            'jenis' => 'Hama',
            'kingdom' => 'Animalia',
            'filum' => 'Arthropoda',
            'kelas' => 'Insecta',
            'ordo' => 'Lepidoptera',
            'famili' => 'Noctuidae',
            'genus' => 'Spodoptera',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Tinggi',
            'deskripsi' => 'Ulat yang menyerang daun padi secara berkelompok. Pada populasi tinggi dapat bermigrasi secara massal dan memakan habis daun tanaman.',
            'etl_acuan' => 5,
            'rekomendasi' => 'Pemasangan perangkap feromon, pengumpulan dan pemusnahan kelompok telur, penggunaan insektisida biologi (Bacillus thuringiensis)',
            'referensi' => 'Balitbangtan'
        ],
        [
            'kode_opt' => 'H006',
            'nama_opt' => 'Keong Mas',
            'nama_ilmiah' => 'Pomacea canaliculata',
            'nama_lokal' => 'Keong Mas, Golden Apple Snail',
            'jenis' => 'Hama',
            'kingdom' => 'Animalia',
            'filum' => 'Mollusca',
            'kelas' => 'Gastropoda',
            'ordo' => 'Architaenioglossa',
            'famili' => 'Ampullariidae',
            'genus' => 'Pomacea',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Tinggi',
            'deskripsi' => 'Moluska air tawar invasif yang memakan bibit padi muda. Sangat merusak pada fase pesemaian dan awal tanam. Berkembang biak cepat dengan telur berwarna merah muda.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Pengumpulan manual dan telur, penggunaan itik sebagai predator alami, aplikasi moluskisida, pengaturan air sawah',
            'referensi' => 'BPTP Jawa Timur'
        ],
        
        // === PENYAKIT (Diseases) ===
        [
            'kode_opt' => 'P001',
            'nama_opt' => 'Blast (Blas)',
            'nama_ilmiah' => 'Pyricularia oryzae',
            'nama_lokal' => 'Blas, Potong Leher',
            'jenis' => 'Penyakit',
            'kingdom' => 'Fungi',
            'filum' => 'Ascomycota',
            'kelas' => 'Sordariomycetes',
            'ordo' => 'Magnaporthales',
            'famili' => 'Pyriculariaceae',
            'genus' => 'Pyricularia',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sangat Tinggi',
            'deskripsi' => 'Penyakit jamur paling merusak pada padi. Menyerang daun (leaf blast), leher malai (neck blast), dan ruas batang. Gejala berupa bercak belah ketupat dengan pusat abu-abu.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Penggunaan varietas tahan, pemupukan berimbang, aplikasi fungisida (Trisiklazol, Isoprothiolane), pengaturan waktu tanam',
            'referensi' => 'BB Padi'
        ],
        [
            'kode_opt' => 'P002',
            'nama_opt' => 'Hawar Daun Bakteri',
            'nama_ilmiah' => 'Xanthomonas oryzae pv. oryzae',
            'nama_lokal' => 'Kresek, Bacterial Leaf Blight',
            'jenis' => 'Penyakit',
            'kingdom' => 'Bacteria',
            'filum' => 'Proteobacteria',
            'kelas' => 'Gammaproteobacteria',
            'ordo' => 'Xanthomonadales',
            'famili' => 'Xanthomonadaceae',
            'genus' => 'Xanthomonas',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Tinggi',
            'deskripsi' => 'Penyakit bakteri yang menyebabkan hawar pada daun padi. Gejala awal berupa bercak hijau kelabu di tepi daun yang meluas. Pada fase kresek, daun mengering seperti terbakar.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Penggunaan varietas tahan, sanitasi lahan, pemupukan berimbang, tidak memupuk nitrogen berlebihan',
            'referensi' => 'BB Padi'
        ],
        [
            'kode_opt' => 'P003',
            'nama_opt' => 'Tungro',
            'nama_ilmiah' => 'Rice Tungro Virus Complex',
            'nama_lokal' => 'Tungro, Mentek',
            'jenis' => 'Penyakit',
            'kingdom' => 'Virus',
            'filum' => null,
            'kelas' => null,
            'ordo' => null,
            'famili' => null,
            'genus' => null,
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sangat Tinggi',
            'deskripsi' => 'Penyakit virus yang ditularkan oleh wereng hijau (Nephotettix virescens). Menyebabkan pertumbuhan kerdil, daun menguning hingga oranye, dan malai hampa.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Penggunaan varietas tahan, pengendalian vektor wereng hijau, eradikasi tanaman terinfeksi, tanam serempak',
            'referensi' => 'Balitbangtan'
        ],
        [
            'kode_opt' => 'P004',
            'nama_opt' => 'Busuk Batang',
            'nama_ilmiah' => 'Sclerotium oryzae',
            'nama_lokal' => 'Busuk Batang',
            'jenis' => 'Penyakit',
            'kingdom' => 'Fungi',
            'filum' => 'Basidiomycota',
            'kelas' => 'Agaricomycetes',
            'ordo' => 'Atheliales',
            'famili' => 'Atheliaceae',
            'genus' => 'Sclerotium',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sedang',
            'deskripsi' => 'Penyakit jamur yang menyerang pelepah daun dan batang bagian bawah. Menyebabkan batang mudah rebah dan malai tidak berkembang sempurna.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Pengolahan tanah sempurna, pemupukan kalium yang cukup, drainase yang baik, aplikasi fungisida',
            'referensi' => 'BB Padi'
        ],
        [
            'kode_opt' => 'P005',
            'nama_opt' => 'Bercak Coklat',
            'nama_ilmiah' => 'Bipolaris oryzae',
            'nama_lokal' => 'Bercak Coklat, Brown Spot',
            'jenis' => 'Penyakit',
            'kingdom' => 'Fungi',
            'filum' => 'Ascomycota',
            'kelas' => 'Dothideomycetes',
            'ordo' => 'Pleosporales',
            'famili' => 'Pleosporaceae',
            'genus' => 'Bipolaris',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sedang',
            'deskripsi' => 'Penyakit jamur dengan gejala bercak bulat lonjong berwarna coklat pada daun. Sering menyerang tanaman yang kekurangan unsur hara atau stres air.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Pemupukan berimbang, penggunaan benih sehat, perlakuan benih dengan fungisida, pengairan yang baik',
            'referensi' => 'BB Padi'
        ],
        
        // === GULMA (Weeds) ===
        [
            'kode_opt' => 'G001',
            'nama_opt' => 'Eceng Gondok',
            'nama_ilmiah' => 'Eichhornia crassipes',
            'nama_lokal' => 'Eceng Gondok, Water Hyacinth',
            'jenis' => 'Gulma',
            'kingdom' => 'Plantae',
            'filum' => 'Tracheophyta',
            'kelas' => 'Liliopsida',
            'ordo' => 'Commelinales',
            'famili' => 'Pontederiaceae',
            'genus' => 'Eichhornia',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Tinggi',
            'deskripsi' => 'Gulma air yang berkembang biak sangat cepat. Menyumbat saluran irigasi, mengurangi kadar oksigen air, dan bersaing dengan tanaman padi untuk sinar matahari.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Pengangkatan mekanis secara berkala, pengendalian biologi dengan kumbang Neochetina, herbisida selektif pada saluran air',
            'referensi' => 'BPTP Jawa Timur'
        ],
        [
            'kode_opt' => 'G002',
            'nama_opt' => 'Teki',
            'nama_ilmiah' => 'Cyperus rotundus',
            'nama_lokal' => 'Teki, Nut Grass',
            'jenis' => 'Gulma',
            'kingdom' => 'Plantae',
            'filum' => 'Tracheophyta',
            'kelas' => 'Liliopsida',
            'ordo' => 'Cyperales',
            'famili' => 'Cyperaceae',
            'genus' => 'Cyperus',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Tinggi',
            'deskripsi' => 'Gulma berdaun sempit yang sangat sulit dikendalikan karena memiliki umbi (nut) di dalam tanah. Berkompetisi kuat dengan tanaman padi untuk air, hara, dan cahaya.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Pengolahan tanah berulang, pencabutan manual dengan umbi, herbisida pratumbuh, penyiraman genangan yang dalam',
            'referensi' => 'Balitbangtan'
        ],
        [
            'kode_opt' => 'G003',
            'nama_opt' => 'Rumput Sawah',
            'nama_ilmiah' => 'Echinochloa crusgalli',
            'nama_lokal' => 'Jawan, Barnyard Grass',
            'jenis' => 'Gulma',
            'kingdom' => 'Plantae',
            'filum' => 'Tracheophyta',
            'kelas' => 'Liliopsida',
            'ordo' => 'Poales',
            'famili' => 'Poaceae',
            'genus' => 'Echinochloa',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Sedang',
            'deskripsi' => 'Gulma rumput yang mirip tanaman padi saat muda. Sangat kompetitif terutama pada fase awal pertumbuhan padi. Dapat menurunkan hasil panen secara signifikan.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Penyiangan dini, herbisida pratumbuh, pengaturan air sawah yang tepat, rotasi tanam',
            'referensi' => 'BPTP Jawa Timur'
        ],
        [
            'kode_opt' => 'G004',
            'nama_opt' => 'Genjer',
            'nama_ilmiah' => 'Limnocharis flava',
            'nama_lokal' => 'Genjer',
            'jenis' => 'Gulma',
            'kingdom' => 'Plantae',
            'filum' => 'Tracheophyta',
            'kelas' => 'Liliopsida',
            'ordo' => 'Alismatales',
            'famili' => 'Alismataceae',
            'genus' => 'Limnocharis',
            'status_karantina' => 'Tidak',
            'tingkat_bahaya' => 'Rendah',
            'deskripsi' => 'Gulma air yang dapat dimakan sebagai sayuran. Meskipun berkompetisi dengan padi, dampaknya relatif ringan dan tanaman ini memiliki nilai ekonomi sebagai pangan.',
            'etl_acuan' => 0,
            'rekomendasi' => 'Penyiangan manual untuk pemanfaatan sebagai sayuran, pengaturan tinggi air sawah',
            'referensi' => 'Balitbangtan'
        ],
    ];
    
    echo "Memulai seeding data OPT...\n";
    echo str_repeat('-', 60) . "\n";
    
    $insertCount = 0;
    $updateCount = 0;
    $skipCount = 0;
    
    foreach ($optData as $data) {
        // Check if already exists by kode_opt
        $stmt = $db->prepare("SELECT id FROM master_opt WHERE kode_opt = ?");
        $stmt->execute([$data['kode_opt']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing record
            $sql = "UPDATE master_opt SET 
                nama_opt = ?, nama_ilmiah = ?, nama_lokal = ?, jenis = ?,
                kingdom = ?, filum = ?, kelas = ?, ordo = ?, famili = ?, genus = ?,
                status_karantina = ?, tingkat_bahaya = ?, deskripsi = ?,
                etl_acuan = ?, rekomendasi = ?, referensi = ?, updated_at = NOW()
                WHERE kode_opt = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['nama_opt'], $data['nama_ilmiah'], $data['nama_lokal'], $data['jenis'],
                $data['kingdom'], $data['filum'], $data['kelas'], $data['ordo'], $data['famili'], $data['genus'],
                $data['status_karantina'], $data['tingkat_bahaya'], $data['deskripsi'],
                $data['etl_acuan'], $data['rekomendasi'], $data['referensi'], $data['kode_opt']
            ]);
            
            echo "UPDATE: {$data['kode_opt']} - {$data['nama_opt']}\n";
            $updateCount++;
        } else {
            // Insert new record
            $sql = "INSERT INTO master_opt 
                (kode_opt, nama_opt, nama_ilmiah, nama_lokal, jenis, 
                kingdom, filum, kelas, ordo, famili, genus,
                status_karantina, tingkat_bahaya, deskripsi, 
                etl_acuan, rekomendasi, referensi, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['kode_opt'], $data['nama_opt'], $data['nama_ilmiah'], $data['nama_lokal'], $data['jenis'],
                $data['kingdom'], $data['filum'], $data['kelas'], $data['ordo'], $data['famili'], $data['genus'],
                $data['status_karantina'], $data['tingkat_bahaya'], $data['deskripsi'],
                $data['etl_acuan'], $data['rekomendasi'], $data['referensi']
            ]);
            
            echo "INSERT: {$data['kode_opt']} - {$data['nama_opt']}\n";
            $insertCount++;
        }
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "Seeding completed!\n";
    echo "Inserted: $insertCount, Updated: $updateCount, Skipped: $skipCount\n";
    echo str_repeat('=', 60) . "\n";
    
    // Show summary
    echo "\nData Summary:\n";
    $stats = $db->query("SELECT jenis, COUNT(*) as total FROM master_opt GROUP BY jenis")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($stats as $stat) {
        echo "  - {$stat['jenis']}: {$stat['total']} records\n";
    }
    
    $total = $db->query("SELECT COUNT(*) as total FROM master_opt")->fetch()['total'];
    echo "\nTotal OPT records: $total\n";
    
    echo "\n✅ OPT data seeding completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Seeding failed: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
echo "<p><a href='" . (defined('BASE_URL') ? BASE_URL : '/jagapadi/') . "opt'>← View OPT List</a></p>";
?>

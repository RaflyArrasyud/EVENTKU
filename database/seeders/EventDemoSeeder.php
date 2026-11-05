<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EventDemoSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@eventku.test'],
            ['name' => 'Organizer Demo', 'password' => Hash::make('password'), 'role' => 'organizer']
        );

        $now = now();
        $events = [
            [
                'judul' => 'Seminar Nasional Literasi Digital untuk Generasi Muda',
                'kategori' => 'Seminar', 'tipe' => 'offline', 'lokasi' => 'Gedung Serbaguna Jakarta',
                'mulai' => $now->copy()->addDays(12)->setTime(9, 0),
                'deskripsi' => 'Seminar sehari penuh yang membahas cara bermedia sosial secara bijak, mengenali hoaks, dan menjaga privasi data pribadi. Peserta akan berdiskusi langsung dengan praktisi literasi digital serta membawa pulang panduan praktis yang bisa diterapkan sehari-hari.',
                'tiket' => [['Reguler', 50000, 200], ['Pelajar', 25000, 100]],
            ],
            [
                'judul' => 'Webinar Strategi Personal Branding di LinkedIn',
                'kategori' => 'Webinar', 'tipe' => 'online', 'lokasi' => 'Zoom Meeting',
                'mulai' => $now->copy()->addDays(10)->setTime(19, 0),
                'deskripsi' => 'Webinar malam hari yang membahas cara menyusun profil LinkedIn yang menarik, menulis ringkasan pengalaman yang jujur, dan membangun jaringan profesional secara konsisten. Cocok untuk mahasiswa tingkat akhir dan pencari kerja.',
                'tiket' => [['Umum', 0, 300]],
            ],
            [
                'judul' => 'Workshop Hands-On UI/UX dengan Figma',
                'kategori' => 'Workshop', 'tipe' => 'offline', 'lokasi' => 'Ruang Kreatif Bandung',
                'mulai' => $now->copy()->addDays(18)->setTime(10, 0),
                'deskripsi' => 'Workshop praktik merancang tampilan aplikasi dari nol menggunakan Figma, mulai dari wireframe, komponen, sampai prototipe yang bisa diklik. Peserta wajib membawa laptop dan akan didampingi mentor selama sesi praktik.',
                'tiket' => [['Early Bird', 75000, 60], ['Reguler', 120000, 90]],
            ],
            [
                'judul' => 'Workshop Dasar Fotografi Produk dengan HP',
                'kategori' => 'Workshop', 'tipe' => 'offline', 'lokasi' => 'Studio Mini Surabaya',
                'mulai' => $now->copy()->addDays(25)->setTime(13, 0),
                'deskripsi' => 'Workshop santai untuk pemilik usaha kecil yang ingin memotret produk sendiri hanya dengan HP. Materi meliputi pencahayaan alami, komposisi sederhana, dan editing ringan memakai aplikasi gratis.',
                'tiket' => [['Umum', 60000, 50]],
            ],
            [
                'judul' => 'Kompetisi Hackathon Aplikasi Kota Cerdas',
                'kategori' => 'Kompetisi', 'tipe' => 'offline', 'lokasi' => 'Balai Kota Yogyakarta',
                'mulai' => $now->copy()->addDays(40)->setTime(8, 0),
                'deskripsi' => 'Hackathon dua hari untuk tim beranggotakan maksimal tiga orang. Setiap tim merancang purwarupa aplikasi yang membantu persoalan kota seperti sampah, transportasi, atau layanan warga. Panitia menyediakan mentor dan hadiah pembinaan bagi tim terbaik.',
                'tiket' => [['Tim (maks 3 orang)', 150000, 40]],
            ],
            [
                'judul' => 'Lomba Esai Nasional Ekonomi Kreatif',
                'kategori' => 'Kompetisi', 'tipe' => 'online', 'lokasi' => 'Pengumpulan daring',
                'mulai' => $now->copy()->addDays(30)->setTime(23, 59),
                'deskripsi' => 'Lomba menulis esai bertema peran anak muda dalam ekonomi kreatif Indonesia. Naskah dikirim daring dengan panjang seribu sampai seribu lima ratus kata. Karya terbaik akan dibukukan dan diumumkan pada malam puncak secara live.',
                'tiket' => [['Peserta', 0, 500]],
            ],
            [
                'judul' => 'Job Fair dan Career Expo Kampus Merdeka',
                'kategori' => 'Karier', 'tipe' => 'offline', 'lokasi' => 'Hall Utama Depok',
                'mulai' => $now->copy()->addDays(22)->setTime(9, 0),
                'deskripsi' => 'Bursa kerja yang mempertemukan pencari kerja dengan perusahaan dari berbagai bidang. Selain stan perusahaan, tersedia sesi review CV gratis dan simulasi wawancara singkat bersama praktisi HR.',
                'tiket' => [['Pengunjung', 0, 1000], ['Prioritas + Goodie Bag', 35000, 200]],
            ],
            [
                'judul' => 'Talkshow Persiapan Wawancara Kerja Pertama',
                'kategori' => 'Karier', 'tipe' => 'online', 'lokasi' => 'Google Meet',
                'mulai' => $now->copy()->addDays(15)->setTime(19, 30),
                'deskripsi' => 'Talkshow interaktif tentang cara menjawab pertanyaan wawancara yang paling sering muncul, menyusun cerita pengalaman dengan metode STAR, dan etika berpakaian saat interview daring maupun luring.',
                'tiket' => [['Umum', 0, 250]],
            ],
            [
                'judul' => 'Tech Talk: Pengenalan Kecerdasan Artifisial untuk Pemula',
                'kategori' => 'Teknologi', 'tipe' => 'online', 'lokasi' => 'Zoom Meeting',
                'mulai' => $now->copy()->addDays(20)->setTime(19, 0),
                'deskripsi' => 'Sesi pengenalan konsep kecerdasan artifisial tanpa rumus berat: apa itu machine learning, contoh penerapannya di kehidupan sehari-hari, dan cara mulai belajar secara otodidak. Diakhiri demo langsung membuat chatbot sederhana.',
                'tiket' => [['Umum', 0, 400]],
            ],
            [
                'judul' => 'Bootcamp Singkat Pemrograman Web dengan Laravel',
                'kategori' => 'Teknologi', 'tipe' => 'offline', 'lokasi' => 'Lab Komputer Bekasi',
                'mulai' => $now->copy()->addDays(35)->setTime(9, 0),
                'deskripsi' => 'Pelatihan intensif tiga hari membangun aplikasi web CRUD dengan Laravel, dari routing dan Blade sampai autentikasi. Peserta pulang membawa satu proyek jadi yang bisa dipamerkan di portofolio.',
                'tiket' => [['Reguler', 250000, 40], ['Mahasiswa', 175000, 30]],
            ],
            [
                'judul' => 'Seminar Membangun UMKM Naik Kelas',
                'kategori' => 'Bisnis', 'tipe' => 'offline', 'lokasi' => 'Aula Dinas Koperasi Semarang',
                'mulai' => $now->copy()->addDays(28)->setTime(9, 30),
                'deskripsi' => 'Seminar untuk pelaku UMKM yang membahas pencatatan keuangan sederhana, pengemasan produk yang menarik, dan cara masuk ke marketplace. Narasumber adalah pemilik usaha yang merintis dari skala rumahan.',
                'tiket' => [['UMKM', 30000, 150]],
            ],
            [
                'judul' => 'Webinar Manajemen Keuangan untuk Freelancer',
                'kategori' => 'Bisnis', 'tipe' => 'online', 'lokasi' => 'Zoom Meeting',
                'mulai' => $now->copy()->addDays(16)->setTime(20, 0),
                'deskripsi' => 'Webinar yang membahas cara mengatur penghasilan tidak tetap: memisahkan rekening pribadi dan usaha, menyiapkan dana darurat, dan menghitung tarif jasa yang sehat. Dilengkapi template anggaran yang bisa langsung dipakai.',
                'tiket' => [['Umum', 45000, 200]],
            ],
            [
                'judul' => 'Festival Kuliner Nusantara Malam Minggu',
                'kategori' => 'Lainnya', 'tipe' => 'offline', 'lokasi' => 'Lapangan Parkir Timur Solo',
                'mulai' => $now->copy()->addDays(14)->setTime(16, 0),
                'deskripsi' => 'Festival kuliner malam hari dengan puluhan stan makanan daerah, dari gudeg sampai papeda. Tiket masuk sudah termasuk kupon jajan dan akses ke panggung hiburan akustik.',
                'tiket' => [['Masuk + Kupon Jajan', 20000, 800]],
            ],
            [
                'judul' => 'Seminar Kesehatan Mental di Tempat Kerja',
                'kategori' => 'Seminar', 'tipe' => 'offline', 'lokasi' => 'Auditorium Tangerang',
                'mulai' => $now->copy()->addDays(45)->setTime(9, 0),
                'deskripsi' => 'Seminar yang membahas cara mengenali tanda kelelahan kerja, membangun batasan yang sehat, dan menciptakan lingkungan tim yang suportif. Dibawakan oleh psikolog organisasi dengan sesi tanya jawab terbuka.',
                'tiket' => [['Reguler', 80000, 180], ['Komunitas', 50000, 70]],
            ],
            [
                'judul' => 'Webinar Public Speaking untuk Pemula',
                'kategori' => 'Webinar', 'tipe' => 'online', 'lokasi' => 'Zoom Meeting',
                'mulai' => $now->copy()->addDays(11)->setTime(19, 0),
                'deskripsi' => 'Webinar latihan berbicara di depan umum untuk pemula: mengatur napas, menyusun pembuka yang memikat, dan mengatasi demam panggung. Peserta terpilih bisa praktik langsung dan mendapat umpan balik.',
                'tiket' => [['Umum', 0, 300], ['Praktik + Feedback', 55000, 40]],
            ],
        ];

        foreach ($events as $i => $d) {
            $event = Event::firstOrCreate(
                ['judul' => $d['judul']],
                [
                    'organizer_id' => $organizer->id,
                    'deskripsi' => $d['deskripsi'],
                    'kategori' => $d['kategori'],
                    'tipe' => $d['tipe'],
                    'lokasi' => $d['lokasi'],
                    'tanggal_mulai' => $d['mulai'],
                    'tanggal_selesai' => $d['mulai']->copy()->addHours(3),
                    'poster' => 'posters/event-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT).'.jpg',
                    'status' => 'terbit',
                ]
            );
            foreach ($d['tiket'] as $t) {
                $event->ticketTypes()->firstOrCreate(
                    ['event_id' => $event->id, 'nama' => $t[0]],
                    ['harga' => $t[1], 'kuota' => $t[2]]
                );
            }
        }
    }
}

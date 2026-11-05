# Midtrans Core API QRIS — Sandbox

Sandbox only. No production endpoint in this task.

## Setup

1. Daftar akun sandbox di https://dashboard.sandbox.midtrans.com, ambil **Server Key** sandbox.
2. JANGAN tulis Server Key ke `.env` — file itu tracked di git
   (`git ls-files .env` mengembalikannya) walau `.gitignore` menyebutnya,
   jadi key akan ikut commit. Tanpa instruksi eksplisit, jangan untrack/edit `.env`.
   Cara aman (hanya proses shell ini, tidak tersimpan ke file):
    ```powershell
    # Hentikan server EventKu lama terlebih dahulu (Ctrl+C di terminal server).
    $env:MIDTRANS_SERVER_KEY='<Server Key SANDBOX milik Anda>'
    # Windows lokal: CA bundle resmi di luar repository (jika PHP belum punya).
    $env:MIDTRANS_CA_BUNDLE="$env:LOCALAPPDATA\EventKu\certificates\cacert.pem"
    php artisan serve --port=8082
    ```
   Alternatif sadar-risiko: untrack `.env` dulu (`git rm --cached .env`),
    baru isi key — hanya setelah pemilik proyek menyetujuinya.
   Jangan tampilkan key di screenshot/log/output.
3. Migrasi kolom pembayaran sudah dijalankan pada project lokal setelah backup
   SQLite. Untuk salinan/deployment baru: backup dulu, lalu `php artisan migrate`.
   Jangan gunakan `migrate:fresh` pada database demo.
4. Jalankan scheduler agar rekonsiliasi jalan tiap 5 menit:
   `php artisan schedule:work` di terminal kedua dengan environment Server Key
   sandbox yang sama. Environment terminal pertama tidak otomatis diwarisi
   terminal kedua.
   atau manual: `php artisan payments:reconcile-qris`

## Alur

- Order berbayar baru selalu `payment_method=midtrans_qris`, reservasi 15 menit
  (`payment_expires_at`), `payment_status=created`. Jika key belum
  dikonfigurasi, checkout berbayar ditolak dengan error ramah (tidak ada
  fallback `manual` — itu jalur bukti/approve lama yang tidak aman).
- Pemilik order tekan bayar → `POST /orders/{order}/payment/start` (auth + CSRF +
  throttle) → `POST https://api.sandbox.midtrans.com/v2/charge`
  (`payment_type=qris`, `custom_expiry` 15 menit dengan `order_time`
  format `Y-m-d H:i:s O` zona Asia/Jakarta, mis. `2016-12-07 11:54:12 +0700`,
  tanpa `acquirer` sesuai contoh charge resmi). QR tidak pernah difake.
- UI render `qr_string` lokal via simple-qrcode. `payment_qr_url` (aksi
  `generate-qr-code`/`generate-qr-code-v2` sandbox, host persis
  `api.sandbox.midtrans.com` via HTTPS) disimpan untuk ditempel ke simulator.
- Cek status manual: `POST /orders/{order}/payment/status` → GET
  `/v2/{gateway_order_id}/status` remote, update DB dari status otoritatif
  (`status_code=200` wajib). Order non-gateway ditolak 422; order lunas
  dijawab lokal tanpa remote call.
- Webhook: `POST /payments/midtrans/notification` (tanpa auth, CSRF exception
  persis `payments/midtrans/notification`). Verifikasi signature
  `sha512(order_id+status_code+gross_amount+server_key)` dengan `hash_equals`,
  lalu selalu refetch GET status otoritatif sebelum apply (tanpa bypass
  fraud-deny langsung). Validasi ketat: `order_id` == `gateway_order_id`,
  `payment_type=qris`, `currency=IDR` (wajib, bukan default), `gross_amount`
  desimal-bulat persis tanpa fraksi/trunkasi, `fraud_status` absen OK tapi
  harus `accept` untuk settlement, QRIS hanya `settlement` (bukan `capture`).
  Unknown → 404, invalid → 400/422. Event valid → 2xx.
- `settlement` → `lunas` + tiket atomik sekali (kuota sudah direservasi saat
  menunggu, jadi settlement telat TETAP fulfill). `expire`/`deny`/
  `cancel` → `batal` (lepas kuota). `lunas` tidak pernah downgrade status
  maupun payment_status. Settlement otoritatif setelah cancel lokal → flag
  `settlement_review` tanpa tiket/resurrect, rekonsiliasi manual.
- Start atomik: klaim `created`→`creating` + `gateway_order_id` stabil dalam
  transaksi writer; percobaan lama SELALU GET status dulu, tidak pernah
  re-charge buta. Timeout pertahankan id untuk rekonsiliasi; 404 remote =
  hold ambigu. Expiry asli checkout+15 (atau `expiry_time` provider jika
  valid), tidak pernah reset dari now.
- Gateway QR yang sudah charge tidak dilepas saat network gagal — hold
  konservatif sampai status terminal dari server. Hanya order lokal tanpa
  `gateway_order_id` yang boleh dilepas saat expiry.
- Order gratis tetap otomatis `lunas`. Order legacy `manual` tetap bisa
  approve/reject organizer + upload bukti. Order gateway: upload bukti dan
   approve/reject manual diblok (403).

Scheduler memeriksa semua percobaan gateway aktif (bukan hanya yang lewat batas
waktu), bergiliran menurut waktu pembaruan agar percobaan bermasalah tidak
menghalangi pesanan baru. Timer halaman hanya informasi, bukan bukti pembayaran.

## Simulator QRIS

- Buka https://simulator.sandbox.midtrans.com/v2/qris/index
- Simulator butuh URL gambar QR yang di-host. Opsi:
  - tempel `payment_qr_url` dari aksi `generate-qr-code` sandbox, atau
  - expose route gambar QR lokal via HTTPS publik lalu tempel URL-nya.
- Jangan pakai pembayaran bank asli. Webhook publik butuh hosting HTTPS;
  kompatibilitas hosting belum terverifikasi — tombol status lokal bisa
  rekonsiliasi tanpa callback publik.

## Kontrak file

- `config/midtrans.php`: `sandbox=true` hardcoded, `base_url` sandbox,
   `server_key` dari env (default kosong); `ca_bundle` opsional dari
   `MIDTRANS_CA_BUNDLE` untuk PHP Windows yang belum punya trust store.
   Verifikasi HTTPS tetap aktif; CA bundle lokal berasal dari
   https://curl.se/ca/cacert.pem dan disimpan di luar repository. Di hosting
   dengan trust store bawaan, biarkan `MIDTRANS_CA_BUNDLE` kosong.
- `Order`: fillable + cast baru, `isGatewayPayment()`, `paymentConfigured()`,
   `issueTickets()` idempoten dalam transaksi + writer barrier SQLite.
- `PaymentController`: `start`, `status`, `notification`.
- `QrisPaymentService`: charge/replay guard, `applyRemoteStatus`,
  `reconcileDue`, `verifySignature`.
- Command `payments:reconcile-qris` + schedule 5 menit.

## Waktu transaksi dan masa berlaku

- `created_at` adalah waktu pesanan dibuat, bukan bukti waktu pembayaran.
- `paid_at` mencatat konfirmasi lunas. QRIS memakai `settlement_time` dari
  respons status terverifikasi jika tersedia; konfirmasi baru tanpa timestamp
  provider memakai waktu konfirmasi server. Order lama tidak diisi dari
  `updated_at`, karena kolom tersebut juga berubah saat rekonsiliasi.
- Detail pesanan dan daftar Tiket Saya menampilkan waktu dalam WIB
  (`Asia/Jakarta`) tanpa mengubah zona penyimpanan UTC.
- `payment_expires_at` hanya batas pembayaran QRIS, bukan batas penggunaan
  tiket. Setelah lunas, riwayat batas bayar tetap dapat dilihat.
- Tiket dapat dipakai satu kali oleh organizer pemilik event, selama event
  belum mencapai `tanggal_selesai`. Setelah digunakan atau event selesai,
  QR check-in tidak ditampilkan lagi dan server menolak pemakaian ulang.
  Check-in lebih awal tetap diperbolehkan oleh organizer.
- Status pembayaran tetap `lunas` meskipun masa penggunaan tiket habis.
  Tiket kedaluwarsa merupakan status tampilan dari jadwal event, bukan
  perubahan status pesanan atau penghapusan riwayat. Sertifikat untuk tiket
  terpakai tetap tersedia.

## Status verifikasi

- Tes otomatis memakai SQLite `:memory:` + `Http::fake`, bukan transaksi Midtrans
  sungguhan. Bootstrap tes menolak database non-isolasi sebelum migrasi tes.
- Layout diuji di browser 1440px/375px, light/dark, menggunakan fixture di SQLite
  sementara di luar project. QR fixture bukan QRIS yang bisa dibayar.
- Pengujian end-to-end akun sandbox berhasil: checkout peserta → QRIS dari
  API sandbox → simulator QRIS menunjukkan PAID → pengecekan status server
  mengonfirmasi settlement/lunas → satu tiket diterbitkan. Dua pengecekan
  remote tambahan tetap menghasilkan satu tiket (tanpa duplikasi).
- 75 tes otomatis lulus (293 assertions); hash database demo tidak berubah
  selama suite. Uji webhook pada endpoint HTTPS publik belum dilakukan.
- Key hanya aktif melalui environment proses server, tidak ditulis ke file
  project. Restart server memerlukan environment kembali. Key yang pernah
  dibagikan lewat chat harus dirotasi; jangan membayar QR sandbox dari aplikasi
   bank/e-wallet sungguhan.

## Verifikasi Railway (11 Oktober 2026)

- Server Key sandbox dipasang privat melalui Variables Railway, bukan file proyek. HTTPS callback disimpan pada dashboard Midtrans: `https://web-event1-production.up.railway.app/payments/midtrans/notification`.
- Checkout berbayar publik order #6 berhasil menghasilkan QR dari Midtrans. Simulator resmi menunjukkan PAID. Notifikasi settlement asli dengan user-agent Veritrans mencapai endpoint publik dan mendapatkan HTTP 200; order menjadi `lunas`/`settlement`, `paid_at` tercatat dan tepat satu tiket diterbitkan **sebelum** tombol cek status ditekan.
- Tiga pengecekan status ulang tetap menghasilkan satu tiket. Detail lunas diverifikasi pada 375px/1440px, terang/gelap, tanpa overflow.
- Notifikasi pending awal menerima HTTP 500 ketika status upstream belum siap; ini sengaja meminta retry, bukan menerima status tanpa verifikasi. Notifikasi settlement berikutnya berhasil. Kegagalan tombol test dashboard tidak diperlakukan sebagai bukti pembayaran ataupun alasan melonggarkan signature.
- Uji ini hanya sandbox, tidak melibatkan bank/e-wallet asli. Database lokal tidak disalin/diubah untuk pengujian publik.

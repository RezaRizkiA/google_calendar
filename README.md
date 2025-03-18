# Laravel Google Calendar Integration
Proyek ini merupakan implementasi integrasi Google Calendar menggunakan Laravel, Socialite, dan Google Calendar API. Proyek ini mendukung pembuatan, pemetaan, dan penghapusan event untuk user dengan peran guru dan murid.

## Fitur Utama

- Login dengan Google: Otentikasi menggunakan Google OAuth2.
- Pembuatan Event: Buat event di kalender Google untuk murid dan guru secara bersamaan.
- Pemetaan Event: Simpan mapping ID event antara kalender murid dan guru di database.
- Penghapusan Event: Hanya user yang membuat event (murid) yang dapat menghapus event melalui tampilan.

## Prasyarat
- PHP versi 7.4 atau lebih tinggi
- Composer
- Database (MySQL)
- Google API Credentials (Client ID, Client Secret, Redirect URI)
- (Opsional) Node.js & NPM untuk mengelola asset frontend

## Cara Instalasi
Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lokal:
1. Clone Repository:
    ```sh
    git clone <repository-url>
    cd <nama-folder-proyek>
    ```
2. Instal Dependensi PHP dengan Composer:
    ```sh
    composer install
    ```
3. Salin File Environment:
    Salin file .env.example ke .env:
    ```sh
    cp .env.example .env
    ```
4. Konfigurasi File .env:
    - Atur koneksi database (DB_DATABASE, DB_USERNAME, DB_PASSWORD, dll).
    - Atur Google API Credentials:
        ```sh
        GOOGLE_CLIENT_ID=your-google-client-id
        GOOGLE_CLIENT_SECRET=your-google-client-secret
        GOOGLE_REDIRECT_URI=your-google-redirect-uri
        ```
    - Pastikan APP_DEBUG=true untuk pengembangan.

5. Generate Application Key:
    ```sh
    php artisan key:generate
    ```
6. Jalankan Migrasi Database:
    ```sh
    php artisan migrate
    ```
7. (Opsional) Buat Storage Link:
    ```sh
    php artisan storage:link
    ``` 
8. (Opsional) Instal Dependensi Frontend & Compile Asset:
    ```sh    
    npm install
    npm run dev
    ```
9. Jalankan Aplikasi:
    ```sh
    php artisan serve
    ```
    Buka browser dan akses: http://127.0.0.1:8000/auth/google

## Konfigurasi Google API
- Buka Google Developer Console.
- Buat proyek baru.
- Aktifkan Google Calendar API.
- Buat kredensial OAuth 2.0 (Client ID dan Client Secret).
- Atur Authorized Redirect URI ke URL callback aplikasi Anda (misal: http://127.0.0.1:8000/google/callback).
- Masukkan kredensial tersebut ke file .env dan konfigurasi pada config/services.php.
    ```sh
    config/services.php
    'google'   => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URL'),
    ],
    ```

# Kontribusi
Jika Anda ingin berkontribusi, silakan fork repositori ini, lakukan perubahan, dan ajukan pull request. Untuk pertanyaan atau masalah, Anda bisa membuka issue.
# Lisensi
Proyek ini dilisensikan di bawah MIT License.
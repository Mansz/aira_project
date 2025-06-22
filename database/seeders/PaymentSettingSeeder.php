<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentSetting;

class PaymentSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'payment_type' => 'bank_transfer',
                'name' => 'BCA',
                'account_number' => '1234567890',
                'account_name' => 'PT Example Store',
                'description' => 'Transfer Bank BCA',
                'is_active' => true,
                'instructions' => [
                    'Buka aplikasi m-Banking atau internet banking BCA Anda',
                    'Pilih menu Transfer',
                    'Masukkan nomor rekening tujuan',
                    'Masukkan jumlah transfer sesuai tagihan',
                    'Periksa kembali detail transfer Anda',
                    'Masukkan PIN atau password untuk konfirmasi',
                    'Simpan bukti transfer',
                    'Upload bukti transfer di halaman konfirmasi pembayaran'
                ],
            ],
            [
                'payment_type' => 'bank_transfer',
                'name' => 'Mandiri',
                'account_number' => '0987654321',
                'account_name' => 'PT Example Store',
                'description' => 'Transfer Bank Mandiri',
                'is_active' => true,
                'instructions' => [
                    'Buka aplikasi Livin by Mandiri atau internet banking Mandiri Anda',
                    'Pilih menu Transfer',
                    'Masukkan nomor rekening tujuan',
                    'Masukkan jumlah transfer sesuai tagihan',
                    'Periksa kembali detail transfer Anda',
                    'Masukkan PIN atau password untuk konfirmasi',
                    'Simpan bukti transfer',
                    'Upload bukti transfer di halaman konfirmasi pembayaran'
                ],
            ],
            [
                'payment_type' => 'e_wallet',
                'name' => 'DANA',
                'account_number' => '081234567890',
                'account_name' => 'Example Store',
                'description' => 'Pembayaran via DANA',
                'is_active' => true,
                'instructions' => [
                    'Buka aplikasi DANA Anda',
                    'Pilih menu Kirim',
                    'Masukkan nomor DANA tujuan',
                    'Masukkan jumlah pembayaran sesuai tagihan',
                    'Periksa kembali detail pembayaran',
                    'Masukkan PIN DANA Anda',
                    'Simpan bukti pembayaran',
                    'Upload bukti pembayaran di halaman konfirmasi'
                ],
            ],
            [
                'payment_type' => 'e_wallet',
                'name' => 'OVO',
                'account_number' => '081234567891',
                'account_name' => 'Example Store',
                'description' => 'Pembayaran via OVO',
                'is_active' => true,
                'instructions' => [
                    'Buka aplikasi OVO Anda',
                    'Pilih menu Transfer',
                    'Masukkan nomor OVO tujuan',
                    'Masukkan jumlah pembayaran sesuai tagihan',
                    'Periksa kembali detail pembayaran',
                    'Masukkan PIN OVO Anda',
                    'Simpan bukti pembayaran',
                    'Upload bukti pembayaran di halaman konfirmasi'
                ],
            ],
        ];

        foreach ($settings as $setting) {
            PaymentSetting::create($setting);
        }
    }
}

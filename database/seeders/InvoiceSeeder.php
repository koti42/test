<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('tr_TR');
        
        // Örnek PDF dosyası oluştur
        $samplePdfContent = '%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>/Contents 4 0 R>>endobj
4 0 obj<</Length 51>>stream
BT /F1 12 Tf 72 720 Td (Örnek Fatura PDF Dosyası) Tj ET
endstream endobj
xref
0 5
0000000000 65535 f
0000000009 00000 n
0000000056 00000 n
0000000111 00000 n
0000000212 00000 n
trailer <</Size 5/Root 1 0 R>>
startxref
312
%%EOF';

        $statuses = ['Bekliyor', 'Hazırlanıyor', 'Kargoda', 'Tamamlandı'];
        $regions = ['İstanbul Anadolu', 'İstanbul Avrupa', 'Ankara', 'İzmir', 'Bursa'];
        $warehouses = ['İstanbul Kadıköy', 'İstanbul Beylikdüzü', 'Ankara Merkez', 'İzmir Merkez'];
        
        // 10 adet fake veri oluştur
        for ($i = 0; $i < 10; $i++) {
            $region = $faker->randomElement($regions);
            $total = $faker->randomFloat(2, 100, 2000);
            $kdv = $total * 0.18;
            
            // Her kayıt için benzersiz bir PDF dosyası oluştur
            $fileName = 'invoices/fatura_' . ($i + 1) . '.pdf';
            Storage::disk('public')->put($fileName, $samplePdfContent);

            Invoice::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->email,
                'phone' => $faker->phoneNumber,
                'pdf_path' => $fileName,
                'status' => $faker->randomElement($statuses),
                'shipping_no' => 'SVK-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'warehouse' => $faker->randomElement($warehouses),
                'shipping_region' => $region,
                'address' => $faker->address,
                'shipping_date' => $faker->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
                'total_amount' => $total,
                'kdv_amount' => $kdv,
                'currency' => 'TL',
                'membership_status' => $faker->randomElement(['Üyeliksiz', 'Üye', 'VIP'])
            ]);
        }
    }
}

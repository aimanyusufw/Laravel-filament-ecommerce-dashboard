<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function PHPSTORM_META\map;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        function fetchProvincesAndCities()
        {
            $provincesResponse = Http::withHeader('key', env('RAJAONGKIR_API_KEY'))->get('https://api.rajaongkir.com/starter/province');
            if ($provincesResponse->successful()) {
                $provinces = $provincesResponse['rajaongkir']['results'];
                foreach ($provinces as $province) {
                    Province::create([
                        'province_name' => $province['province'],
                        'alt_name' => Str::slug($province['province']),
                    ]);
                    $citiesResponse = Http::withHeader('key', env('RAJAONGKIR_API_KEY'))->get('https://api.rajaongkir.com/starter/city?province=' . $province['province_id']);
                    if ($citiesResponse->successful()) {
                        $cities = $citiesResponse['rajaongkir']['results'];
                        foreach ($cities as $city) {
                            City::create([
                                'province_id' => $city['province_id'],
                                'type' => $city['type'],
                                'city_name' => $city['city_name'],
                                'postal_code' => $city['postal_code'],
                            ]);
                        }
                    } else {
                        Log::error('Failed to fetch cities for province: ' . $province['province_id']);
                    }
                    sleep(1);
                }
            } else {
                Log::error('Failed to fetch provinces from RajaOngkir API.');
            }
        }

        // fetchProvincesAndCities();

        
    }
}

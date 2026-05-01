<?php

namespace Database\Seeders;

use App\Models\Campervan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlquifurgoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user (based on User model canAccessPanel email)
        User::firstOrCreate(
            ['email' => 'admin@alquifurgo.es'],
            [
                'name' => 'Admin Alquifurgo',
                'password' => Hash::make('password'),
            ]
        );

        // Demo Client User
        User::firstOrCreate(
            ['email' => 'cliente@alquifurgo.es'],
            [
                'name' => 'Cliente Demo',
                'password' => Hash::make('password'),
            ]
        );

        // Campervans for Alquifurgo Albacete
        Campervan::firstOrCreate(
            ['name' => 'Furgoneta Camper Gran Volumen - Alquifurgo Albacete'],
            [
                'description' => 'Disfruta de la mejor experiencia camper en Albacete con nuestra furgoneta Gran Volumen. Perfecta para escapadas en pareja o familia. Totalmente equipada con cocina, baño completo y cama de matrimonio.',
                'price_per_night' => 120.00,
                'allows_deposit' => true,
                'is_visible' => true,
                'no_checkout_booking' => false,
                'check_in_time' => '15:00:00',
                'check_out_time' => '11:00:00',
                'km_limit' => 200,
                'price_per_extra_km' => 0.25,
            ]
        );

        Campervan::firstOrCreate(
            ['name' => 'Camper Compacta - Alquifurgo Albacete'],
            [
                'description' => 'Ideal para moverse por la ciudad y la naturaleza. Consumo reducido y fácil de aparcar. Todo lo necesario en un espacio optimizado para tu aventura desde Albacete.',
                'price_per_night' => 90.00,
                'allows_deposit' => true,
                'is_visible' => true,
                'no_checkout_booking' => false,
                'check_in_time' => '15:00:00',
                'check_out_time' => '11:00:00',
                'km_limit' => 200,
                'price_per_extra_km' => 0.25,
            ]
        );

        Campervan::firstOrCreate(
            ['name' => 'Autocaravana Familiar - Alquifurgo Albacete'],
            [
                'description' => 'La opción más espaciosa para familias o grupos de amigos. Con capacidad para hasta 6 personas, baño completo, cocina amplia y todas las comodidades para un viaje inolvidable partiendo de Albacete.',
                'price_per_night' => 150.00,
                'allows_deposit' => true,
                'is_visible' => true,
                'no_checkout_booking' => false,
                'check_in_time' => '16:00:00',
                'check_out_time' => '10:00:00',
                'km_limit' => 300,
                'price_per_extra_km' => 0.30,
            ]
        );
    }
}

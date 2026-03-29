<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'location_title' => 'Our Rental Shop Location',
                'map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1007.2400853653274!2d125.49589573174583!3d9.787397809471626!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x330136bee35cc3ef%3A0x9b7f2d5a0bac57b5!2sNarciso%20Street%2C%20Surigao%2C%20Surigao%20del%20Norte!5e0!3m2!1sen!2sph!4v1765790499257!5m2!1sen!2sph',
                'cards' => [
                    [
                        'title' => 'Total Customers',
                        'value' => 90,
                    ],
                    [
                        'title' => 'Daily Vehicles Summary',
                        'value' => 80,
                    ],
                    [
                        'title' => 'Daily Sales Summary',
                        'value' => 95,
                    ],
                ],
            ],
        ]);
    }
}
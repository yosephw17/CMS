<?php

// database/seeders/EvaluatorSeeder.php
namespace Database\Seeders;
use App\Models\Evaluator;
use Illuminate\Database\Seeder;

class EvaluatorSeeder extends Seeder
{
    public function run()
    {
        $evaluators = [
            [
                'email' => 'endrishiyaru@gmail.com',
                'name' => 'Endris Hiyaru',
                'type' => 'instructor'
            ],
            [
                'email' => 'radahmednuru@gmail.com',
                'name' => 'AHMED NURU',
                'type' => 'instructor'
            ],
            [
                'email' => 'angobenaj@gmail.com',
                'name' => 'Andargachew Gobena',
                'type' => 'instructor'
            ],
            [
                'email' => 'agerberhanu29@gmail.com',
                'name' => 'Agere Berhanu',
                'type' => 'instructor'
            ],
            [
                'email' => 'aschalewabebe33@gmail.com',
                'name' => 'Aschalew Abebe',
                'type' => 'instructor'
            ],
            [
                'email' => 'awokeeyrw@gmail.com',
                'name' => 'Atirsaw Awoke',
                'type' => 'instructor'
            ],
            [
                'email' => 'lijadis@gmail.com',
                'name' => 'lijadis workneh',
                'type' => 'instructor'
            ],
            [
                'email' => 'mulerce@gmail.com',
                'name' => 'molla Atanaw',
                'type' => 'instructor'
            ],
            [
                'email' => 'hengdaw@gmail.com',
                'name' => 'Haileeyesus Engdaw',
                'type' => 'instructor'
            ],
            [
                'email' => 'tegistubiyadg@gmail.com',
                'name' => 'Tegistu Biyadgilign',
                'type' => 'instructor'
            ],
            [
                'email' => 'tgkebebaw@gmail.com',
                'name' => 'Tegegne Kebebaw',
                'type' => 'instructor'
            ],
            [
                'email' => 'tinbitad@gmail.com',
                'name' => 'Tinbit Admassu',
                'type' => 'instructor'
            ],
            [
                'email' => 'tinbitad@yahoo.com',
                'name' => 'tinbit admassu',
                'type' => 'instructor'
            ],
            [
                'email' => 'tmsmhr@gmail.com',
                'name' => 'Temesgen Mihiretu',
                'type' => 'instructor'
            ],
            [
                'email' => 'saragetu8@gmail.com',
                'name' => 'Sara Getu',
                'type' => 'instructor'
            ],
            [
                'email' => 'abrehamt2@gmail.com',
                'name' => 'Abreham Alene',
                'type' => 'instructor'
            ],
            [
                'email' => 'tenagnefikirie@gmail.com',
                'name' => 'Tenagne Fikire',
                'type' => 'instructor'
            ],
            [
                'email' => 'tibeyinmaru@gmail.com',
                'name' => 'maru kindeneh',
                'type' => 'instructor'
            ],
            [
                'email' => 'eleshlake223@gmail.com',
                'name' => 'Eleshaday lake',
                'type' => 'instructor'
            ],
            [
                'email' => 'biniyamsolomo@gmail.com',
                'name' => 'Biniyam Solomon',
                'type' => 'instructor'
            ],
            [
                'email' => 'livanosbek@gmail.com',
                'name' => 'Aneas Bekele',
                'type' => 'instructor'
            ],
            [
                'email' => 'zelexalem34w@gmail.com',
                'name' => 'Zemenu',
                'type' => 'instructor'
            ],
            [
                'email' => 'wubieeng21@gmail.com',
                'name' => 'Wubie Engidaw',
                'type' => 'instructor'
            ],
            [
                'email' => 'Jemal.yosef1000@gmail.com',
                'name' => 'Yosef Jemal',
                'type' => 'instructor'
            ],
            [
                'email' =>'tamratfekadu123@gmail.com',
                'name' => 'Tamrat Fekadu',
                'type' => 'student'
            ],[
                'email' =>'tamratfekadu1234@gmail.com',
                'name' => 'Tomas T',
                'type' => 'dean'

            ]
        ];


        foreach ($evaluators as $evaluator) {
            Evaluator::create($evaluator);
        }
    }
}

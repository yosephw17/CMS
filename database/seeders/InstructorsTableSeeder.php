<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instructor;

class InstructorsTableSeeder extends Seeder
{
    public function run()
    {
        $instructors = [

            ['name' => 'Eneyachew Tamir', 'email' => 'eneyachew.tamir@example.com', 'phone' => '0912345678', 'role_id' => 1, 'is_available' => 0, 'is_studying' => 1, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Adugna Necho', 'email' => 'adugna.necho@example.com', 'phone' => '0912345679', 'role_id' => 1, 'is_available' => 0, 'is_studying' => 1, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Tinbit Addimasu', 'email' => 'tinbit.addimasu@example.com', 'phone' => '0912345680', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Ahmed Nuru', 'email' => 'ahmed.nuru@example.com', 'phone' => '0912345681', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Aschalew Abebe', 'email' => 'aschalew.abebe@example.com', 'phone' => '0912345682', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Molla Atanaw', 'email' => 'molla.atanaw@example.com', 'phone' => '0912345683', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Endris Hiyaru', 'email' => 'endris.hiyaru@example.com', 'phone' => '0912345684', 'role_id' => 1, 'is_available' => 0, 'is_studying' => 1, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Agere Berhanu', 'email' => 'agere.berhanu@example.com', 'phone' => '0912345685', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Addis Workneh', 'email' => 'addis.workneh@example.com', 'phone' => '0912345686', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Tigistu Biyadgilgn', 'email' => 'tigistu.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Andargachew Gobena', 'email' => 'Andargachew.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Atirsaw Awoke', 'email' => 'Atirsaw.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Tegegn Kibebew', 'email' => 'Tegegn.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Temesgen Mihiretu', 'email' => 'Temesgen.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Yesuneh Getachew', 'email' => 'Yesuneh.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Yosef Jemal', 'email' => 'Eleshaday.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Eleshaday Yitibark', 'email' => 'Elshaday.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Biniyam Solomon', 'email' => 'Biniyam.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Abreham Alene', 'email' => 'Abreham.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            ['name' => 'Maru Kindeneh', 'email' => 'Maru.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1, 'department_id' => 2],
            // Add more instructors as needed...

        ];

        foreach ($instructors as $instructor) {
            Instructor::create($instructor);
        }
    }
}

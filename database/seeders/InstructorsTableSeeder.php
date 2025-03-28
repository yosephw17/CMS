<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instructor;

class InstructorsTableSeeder extends Seeder
{
    public function run()
    {
        $instructors = [
            ['name' => 'Eneyachew Tamir', 'email' => 'eneyachew.tamir@example.com', 'phone' => '0912345678', 'role_id' => 1, 'department_id' => 2, 'is_available' => 0, 'is_studying' => 1, 'is_approved' => 1],
            ['name' => 'Adugna Necho', 'email' => 'adugna.necho@example.com', 'phone' => '0912345679', 'role_id' => 1, 'department_id' => 2, 'is_available' => 0, 'is_studying' => 1, 'is_approved' => 1],
            ['name' => 'Tinbit Addimasu', 'email' => 'tinbit.addimasu@example.com', 'phone' => '0912345680', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Ahmed Nuru', 'email' => 'ahmed.nuru@example.com', 'phone' => '0912345681', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Aschalew Abebe', 'email' => 'aschalew.abebe@example.com', 'phone' => '0912345682', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Molla Atanaw', 'email' => 'molla.atanaw@example.com', 'phone' => '0912345683', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Endris Hiyaru', 'email' => 'endris.hiyaru@example.com', 'phone' => '0912345684', 'role_id' => 1, 'department_id' => 2, 'is_available' => 0, 'is_studying' => 1, 'is_approved' => 1],
            ['name' => 'Agere Berhanu', 'email' => 'agere.berhanu@example.com', 'phone' => '0912345685', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Addis Workneh', 'email' => 'addis.workneh@example.com', 'phone' => '0912345686', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Tigistu Biyadgilgn', 'email' => 'tigistu.biyadgilgn@example.com', 'phone' => '0912345687', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Andargachew Gobena', 'email' => 'andargachew.gobena@example.com', 'phone' => '0912345688', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Atirsaw Awoke', 'email' => 'atirsaw.awoke@example.com', 'phone' => '0912345689', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Tegegn Kibebew', 'email' => 'tegegn.kibebew@example.com', 'phone' => '0912345690', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Temesgen Mihiretu', 'email' => 'temesgen.mihiretu@example.com', 'phone' => '0912345691', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Yesuneh Getachew', 'email' => 'yesuneh.getachew@example.com', 'phone' => '0912345692', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Yosef Jemal', 'email' => 'yosef.jemal@example.com', 'phone' => '0912345693', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Eleshaday Yitibark', 'email' => 'eleshaday.yitibark@example.com', 'phone' => '0912345694', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Biniyam Solomon', 'email' => 'biniyam.solomon@example.com', 'phone' => '0912345695', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Abreham Alene', 'email' => 'abreham.alene@example.com', 'phone' => '0912345696', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
            ['name' => 'Maru Kindeneh', 'email' => 'maru.kindeneh@example.com', 'phone' => '0912345697', 'role_id' => 1, 'department_id' => 2, 'is_available' => 1, 'is_studying' => 0, 'is_approved' => 1],
        ];

        foreach ($instructors as $instructor) {
            Instructor::create($instructor);
        }
    }
}

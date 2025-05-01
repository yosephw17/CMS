<?php

namespace Database\Seeders;

use App\Models\AuditSession;
use Illuminate\Database\Seeder;

class AuditSessionSeeder extends Seeder
{
    public function run()
    {
        $sessions = [
            ['name' => 'Before Mid Exam'],
            ['name' => 'After Mid Exam'],
        ];

        AuditSession::insert($sessions);
    }
}
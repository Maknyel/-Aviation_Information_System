<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $now = now();
        $defaults = [
            ['name' => 'Chair',        'facility_field' => 'chair',        'category' => 'furniture',    'total_quantity' => 100, 'unit' => 'pcs'],
            ['name' => 'Podium',       'facility_field' => 'podium',       'category' => 'furniture',    'total_quantity' => 5,   'unit' => 'pcs'],
            ['name' => 'Tent',         'facility_field' => 'tent',         'category' => 'utility',      'total_quantity' => 10,  'unit' => 'pcs'],
            ['name' => 'Tables',       'facility_field' => 'tables',       'category' => 'furniture',    'total_quantity' => 50,  'unit' => 'pcs'],
            ['name' => 'Booths',       'facility_field' => 'booths',       'category' => 'furniture',    'total_quantity' => 20,  'unit' => 'pcs'],
            ['name' => 'Sound System', 'facility_field' => 'sound_system', 'category' => 'av_equipment', 'total_quantity' => 5,   'unit' => 'sets'],
            ['name' => 'Extension',    'facility_field' => 'extension',    'category' => 'utility',      'total_quantity' => 20,  'unit' => 'pcs'],
            ['name' => 'Microphones',  'facility_field' => 'microphones',  'category' => 'av_equipment', 'total_quantity' => 10,  'unit' => 'pcs'],
            ['name' => 'Skirting',     'facility_field' => 'skirting',     'category' => 'utility',      'total_quantity' => 30,  'unit' => 'pcs'],
            ['name' => 'Flags',        'facility_field' => 'flags',        'category' => 'utility',      'total_quantity' => 50,  'unit' => 'pcs'],
        ];

        foreach ($defaults as $item) {
            // Only insert if no item with this facility_field already exists
            $exists = DB::table('inventory_items')->where('facility_field', $item['facility_field'])->exists();
            if (!$exists) {
                DB::table('inventory_items')->insert(array_merge($item, [
                    'condition'  => 'good',
                    'notes'      => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down()
    {
        DB::table('inventory_items')
            ->whereIn('facility_field', ['chair','podium','tent','tables','booths','sound_system','extension','microphones','skirting','flags'])
            ->delete();
    }
};

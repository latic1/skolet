<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- 1. Deduplicate school_classes.name ---------------------------------
        // Keep the earliest row; rename later duplicates as "Name (2)", "Name (3)" …
        $dupeClassNames = DB::table('school_classes')
            ->selectRaw('name, COUNT(*) as cnt')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupeClassNames as $dupe) {
            $extras = DB::table('school_classes')
                ->where('name', $dupe->name)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id', 'name'])
                ->slice(1);  // keep the first; rename the rest

            $counter = 2;
            foreach ($extras as $extra) {
                do {
                    $newName = $extra->name . " ({$counter})";
                    $counter++;
                } while (DB::table('school_classes')->where('name', $newName)->exists());

                DB::table('school_classes')->where('id', $extra->id)->update(['name' => $newName]);
            }
        }

        // --- 2. Deduplicate school_classes.order --------------------------------
        // Keep the earliest row; assign later duplicates the next available order.
        $dupeOrders = DB::table('school_classes')
            ->selectRaw('`order`, COUNT(*) as cnt')
            ->groupBy('order')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupeOrders as $dupe) {
            $extras = DB::table('school_classes')
                ->where('order', $dupe->order)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id'])
                ->slice(1);

            foreach ($extras as $extra) {
                $nextOrder = (int) DB::table('school_classes')->max('order') + 1;
                DB::table('school_classes')->where('id', $extra->id)->update(['order' => $nextOrder]);
            }
        }

        // --- 3. Deduplicate sections(class_id, name) ----------------------------
        $dupeSections = DB::table('sections')
            ->selectRaw('class_id, name, COUNT(*) as cnt')
            ->groupBy('class_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupeSections as $dupe) {
            $extras = DB::table('sections')
                ->where('class_id', $dupe->class_id)
                ->where('name', $dupe->name)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id', 'name'])
                ->slice(1);

            $counter = 2;
            foreach ($extras as $extra) {
                do {
                    $newName = $extra->name . " ({$counter})";
                    $counter++;
                } while (
                    DB::table('sections')
                        ->where('class_id', $dupe->class_id)
                        ->where('name', $newName)
                        ->exists()
                );

                DB::table('sections')->where('id', $extra->id)->update(['name' => $newName]);
            }
        }

        // --- 4. Deduplicate subjects.name ---------------------------------------
        $dupeSubjects = DB::table('subjects')
            ->selectRaw('name, COUNT(*) as cnt')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupeSubjects as $dupe) {
            $extras = DB::table('subjects')
                ->where('name', $dupe->name)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id', 'name'])
                ->slice(1);

            $counter = 2;
            foreach ($extras as $extra) {
                do {
                    $newName = $extra->name . " ({$counter})";
                    $counter++;
                } while (DB::table('subjects')->where('name', $newName)->exists());

                DB::table('subjects')->where('id', $extra->id)->update(['name' => $newName]);
            }
        }

        // --- 5. Add unique indexes ---------------------------------------------
        Schema::table('school_classes', function (Blueprint $table) {
            $table->unique('name', 'school_classes_name_unique');
            $table->unique('order', 'school_classes_order_unique');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->unique(['class_id', 'name'], 'sections_class_id_name_unique');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->unique('name', 'subjects_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropUnique('school_classes_name_unique');
            $table->dropUnique('school_classes_order_unique');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique('sections_class_id_name_unique');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_name_unique');
        });
    }
};

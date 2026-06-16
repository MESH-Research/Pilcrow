<?php
declare(strict_types=1);

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace the obtuse integer role_id on the role-assignment pivots with a
 * human-readable role slug (matching the GraphQL enum names), and drop the
 * foreign key to the spatie roles table. Scoping assignment now references a
 * plain validated slug string.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = [
        'publication_user',
        'submission_user',
        'submission_invitations',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('role')->nullable()->after('role_id');
            });

            foreach (Role::ID_TO_SLUG as $id => $slug) {
                DB::table($table)->where('role_id', $id)->update(['role' => $slug]);
            }

            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['role_id']);
                $t->dropColumn('role_id');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->string('role')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        $slugToId = array_flip(Role::ID_TO_SLUG);

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('role_id')->nullable()->after('id');
            });

            foreach ($slugToId as $slug => $id) {
                DB::table($table)->where('role', $slug)->update(['role_id' => $id]);
            }

            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('role');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('role_id')->nullable(false)->change();
                $t->foreign('role_id')->references('id')->on('roles');
            });
        }
    }
};

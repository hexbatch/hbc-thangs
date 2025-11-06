<?php

namespace Hexbatch\Thangs\Models;



use App\Exceptions\HexbatchNotFound;
use App\Helpers\Utilities;
use App\Models\UserNamespace;
use Hexbatch\Thangs\Data\ThangData;

use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangSavePolicy;
use Hexbatch\Thangs\Exceptions\ThangRefCodes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;



/**
 * @mixin Builder
 * @mixin ThangData
 * @mixin \Illuminate\Database\Query\Builder
 */
class Thang extends Model
{

    protected $table = 'thangs';
    public $timestamps = false;

    protected bool $b_show_ids = false;

    /** @noinspection PhpUnused its used but editor marks it as unused */
    public function getSidAttribute(): bool { return $this->b_show_ids;}

    /** @noinspection PhpUnused  its used but editor marks it as unused*/
    public function setSidAttribute(bool $b_sid): void {  $this->b_show_ids = $b_sid;}
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owning_namespace_id',
        'ref_uuid',
        'finished_data',
        'thang_async_policy',
        'thang_save_policy',
        'thang_save_policy',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'finished_data' => 'array',
        'thang_async_policy' => TypeOfThangAsyncPolicy::class,
        'thang_save_policy' => TypeOfThangSavePolicy::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owning_namespace() : BelongsTo {
        return $this->belongsTo(UserNamespace::class,'owning_namespace_id');
    }


    public function commands() : HasMany {
        return $this->hasMany(ThangCommand::class,'owning_thang_id');
    }


    public function resolveRouteBinding($value, $field = null)
    {
        return static::resolveThang($value);
    }


    public static function buildThang(
        ?int  $me_id = null, ?string $uuid = null
    )
    : Builder
    {
        $build = Thang::with([
            'commands', 'owning_namespace',
            'commands.command_callbacks','commands.command_callbacks.owning_hook'
        ]);

        if ($me_id) {
            $build->where('thangs.id',$me_id);
        }

        if ($uuid) {
            $build->where('thangs.ref_uuid',$uuid);
        }

        return $build;
    }
    public static function resolveThang(?string $value, bool $throw_exception = true)
    : ?static
    {
        if (!$value) {return null;}
        /** @var Builder $build */
        $build = null;

        if (Utilities::is_uuid($value)) {
            $build = static::buildThang(uuid: $value);
        }

        $ret = $build?->first();

        if (empty($ret) && $throw_exception) {
            throw new HexbatchNotFound(
                __("hbc-thangs::thangs.thang_not_found", ['ref' => $value]),
                \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND,
                ThangRefCodes::THANG_NOT_FOUND
            );
        }

        return $ret;
    }




}

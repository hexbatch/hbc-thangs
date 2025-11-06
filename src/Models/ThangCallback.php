<?php

namespace Hexbatch\Thangs\Models;



use Hexbatch\Thangs\Data\ThangCallbackData;
use Hexbatch\Thangs\Enums\TypeOfThangCallbackStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @mixin Builder
 * @mixin ThangCallbackData
 * @mixin \Illuminate\Database\Query\Builder
 */
class ThangCallback extends Model
{

    protected $table = 'thang_callbacks';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owning_hook_id' ,
        'owning_hook_ref' ,
        'source_command_id' ,
        'source_command_ref' ,
        'callback_http_code' ,
        'ref_uuid' ,
        'callback_data' ,
        'callback_status',
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
        'callback_data' => 'array',
        'callback_status' => TypeOfThangCallbackStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owning_hook(): BelongsTo
    {
        return $this->belongsTo(ThangHook::class, 'owning_hook_id');
    }

    public function source_command(): BelongsTo
    {
        return $this->belongsTo(ThangCommand::class, 'source_command_id');
    }


    public function isCompleted() : bool {
        return in_array($this->callback_status,TypeOfThangCallbackStatus::FINISHED_STATE);
    }

    public function isSuccess() : bool {
        return $this->callback_status === TypeOfThangCallbackStatus::SUCCESSFUL;
    }

    public function isRunning() : bool {
        return in_array($this->callback_status,TypeOfThangCallbackStatus::RUNNING_STATE);
    }

}

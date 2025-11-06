<?php

namespace Hexbatch\Thangs\Models;


use App\Models\UserNamespace;
use Hexbatch\Thangs\Data\ThangHookData;
use Hexbatch\Thangs\Enums\TypeOfThangCallbackStatus;
use Hexbatch\Thangs\Interfaces\IHookCaller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use TorMorten\Eventy\Facades\Eventy;


/**
 * @mixin Builder
 * @mixin ThangHookData
 * @mixin \Illuminate\Database\Query\Builder
 */
class ThangHook extends Model
{

    protected $table = 'thang_hooks';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

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
        'hook_data' => 'array',
        'hook_tags' => 'array',
        'is_on' => 'bool',
        'is_async' => 'bool',
        'is_pre' => 'bool',
        'is_public' => 'bool',
        'hook_priority' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owning_namespace(): BelongsTo
    {
        return $this->belongsTo(UserNamespace::class, 'owning_namespace_id');
    }


    public static function addOrWhereKeywords(Builder $build, array $keywords, ?int $owning_namespace_id)
    {
        $build->orWhere(function (Builder $q) use ($keywords, $owning_namespace_id) {
            if (count($keywords)) {
                $tags_json = json_encode(array_values($keywords));
                $q->whereRaw("array(select jsonb_array_elements(thang_hooks.hook_tags) ) && array(select jsonb_array_elements(?) )", $tags_json);
            } else {
                $q->whereRaw("(jsonb_array_length(thang_hooks.hook_tags) is null OR jsonb_array_length(thang_hooks.hook_tags) = 0)");
            }
            if ($owning_namespace_id) {
                $q->where(function (Builder $n) use ($owning_namespace_id) {
                    $n->where('thang_hooks.owning_namespace_id', $owning_namespace_id)
                        ->orWhereNull('thang_hooks.owning_namespace_id');
                });
            } else {
                $q->WhereNull('thang_hooks.owning_namespace_id');
            }

        });
    }





    public static function runHook(
        ThangHook      $hook,
        IHookCaller        $caller,
        ?ThangCommand  $cmd = null,
        ?ThangCallback $callback = null
    ): void
    {


        $command_data = array_replace_recursive(
            $cmd->command_args ?? [],
            $cmd->staging_data_from_children??[],
            $hook->hook_data ?? []);

        if ($hook->event_name) {
            $status_key = 'hook_status_' . str_replace('-', '_', $hook->ref_uuid);
            $command_data[$status_key] = [
                 'status' => TypeOfThangCallbackStatus::RUNNING,
                 'code' => 0,
                 'command_status' => $cmd->command_status,
                 'is_pre' => $hook->is_pre
            ];

            $ret = Eventy::filter($hook->event_name, $command_data, $status_key);

            $status_block = $ret[$status_key];
            $callback->callback_status = $status_block['status'] ?? TypeOfThangCallbackStatus::ERROR;
            $callback->callback_http_code = (int)$status_block['code'] ?? 0;
            unset($ret[$status_key]);
            $callback->callback_data = $ret;

        } else {
            $callback->callback_data = $command_data;
            $callback->callback_status = TypeOfThangCallbackStatus::SUCCESSFUL;
        }



        $caller->onHookCompletion(cmd:$cmd, hook: $hook, callback: $callback);


    }




}

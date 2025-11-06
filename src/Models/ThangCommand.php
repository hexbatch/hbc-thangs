<?php

namespace Hexbatch\Thangs\Models;


use App\Exceptions\HexbatchNotFound;
use App\Helpers\Utilities;
use Hexbatch\Thangs\Data\ThangCommandData;
use Hexbatch\Thangs\Enums\TypeOfCmdStatus;
use Hexbatch\Thangs\Exceptions\ThangRefCodes;
use Hexbatch\Thangs\Interfaces\ICommandCallable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;


/**
 * @mixin Builder
 * @mixin ThangCommandData
 * @mixin \Illuminate\Database\Query\Builder
 */
class ThangCommand extends Model
{

    protected $table = 'thang_commands';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owning_thang_id',
        'parent_id',
        'is_async',
        'ref_uuid',
        'parent_ref_uuid',
        'bubble_exceptions',
        'command_status',
        'command_args',
        'staging_data_from_children',
        'command_errors',
        'command_tags',
        'command_class'
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
        'command_args' => 'array',
        'staging_data_from_children' => 'array',
        'command_tags' => 'array',
        'is_async' => 'bool',
        'bubble_exceptions' => 'bool',
        'command_status' => TypeOfCmdStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent_command(): BelongsTo
    {
        return $this->belongsTo(ThangCommand::class, 'parent_id');
    }

    public function thang_owner(): BelongsTo
    {
        return $this->belongsTo(Thang::class, 'owning_thang_id');
    }



    public function command_callbacks(): HasMany
    {
        return $this->hasMany(ThangCallback::class, 'source_command_id');
    }


    public function resolveRouteBinding($value, $field = null)
    {
        return static::resolveCommand($value);
    }


    public static function buildCommand(
        ?int  $me_id = null, ?string $uuid = null
    )
    : Builder
    {
        $build = ThangCommand::with(['parent_command','thang_owner','thang_owner.owning_namespace',
            'command_callbacks','command_callbacks.owning_hook']);

        if ($me_id) {
            $build->where('thang_commands.id',$me_id);
        }

        if ($uuid) {
            $build->where('thang_commands.ref_uuid',$uuid);
        }

        return $build;
    }
    public static function resolveCommand(?string $value, bool $throw_exception = true)
    : ?static
    {
        if (!$value) {return null;}
        /** @var Builder $build */
        $build = null;

        if (Utilities::is_uuid($value)) {
            $build = static::buildCommand(uuid: $value);
        }

        $ret = $build?->first();

        if (empty($ret) && $throw_exception) {
            throw new HexbatchNotFound(
                __("hbc-thangs::thangs.thang_command_not_found", ['ref' => $value]),
                \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND,
                ThangRefCodes::THANG_COMMAND_NOT_FOUND
            );
        }

        return $ret;
    }








    /**
     * @return ICommandCallable|string
     */
    public function getStaticCommandClass()
    {
        /** @type ICommandCallable */
        return $this->command_class;
    }

    public function isCompleted() : bool {
        return in_array($this->command_status,TypeOfCmdStatus::FINISHED_STATE);
    }

    public function isError() : bool {
        return $this->command_status ===TypeOfCmdStatus::CMD_ERROR;
    }


    public function isSuccess() : bool {
        return $this->command_status ===TypeOfCmdStatus::CMD_SUCCESS;
    }

    public function isRunning() : bool {
        return $this->command_status === TypeOfCmdStatus::CMD_RUNNING;
    }

    public function isReady() : bool {
        return $this->command_status === TypeOfCmdStatus::CMD_WAITING;
    }

    /**
     * @param Collection<ThangCommand> $col
     * @return Collection<ThangCommand>|null
     */
    public static function getCommandsFromCollectionByStatus(Collection $col, TypeOfCmdStatus $status) : ?Collection {
        return $col->filter(
            function (ThangCommand $value, $key) use($status) {
                Utilities::ignoreVar($key);
                return $value->command_status === $status;
            });
    }



    /** @param Collection<ThangCommand> $col */
    public static function getErrorArrayFromCollection(Collection $col) : array {
        $commands = static::getCommandsFromCollectionByStatus($col,TypeOfCmdStatus::CMD_ERROR);
        $ret = [];
        foreach ($commands as $cmd) {
            if ($cmd->command_errors) {
                $ret[] = $cmd->command_errors;
            }
        }
        return $ret;
    }

    /** @param Collection<ThangCommand> $col */
    public static function nestCollection(Collection $col) : array {
        if ($col->isEmpty()) {return [];}
        $arr = [];

        foreach ($col->toArray() as $who) {
            $arr[] = $who;
        }

        $counter = 1;
        $hash = [];
        foreach ($col as $what_thing) {
            $hash[$what_thing->ref_uuid] = $counter++;
        }

        foreach ($arr as &$what_thing) {
            $what_thing['x_id'] =   $hash[$what_thing['ref_uuid']];
            if ($what_thing['parent_ref_uuid']) {
                $what_thing['x_parent_id'] =   $hash[$what_thing['parent_ref_uuid']];
            } else {
                $what_thing['x_parent_id'] = 0;
            }

        }


        return static::inel_set_tree($arr,'x_parent_id','x_id');

    }

    /*
     * Create array tree from array list
     *
     * @param array   ( Required )  The list to create tree
     * @param string                The name key parent. Ex: parent_id
     * @param string                The key to check child. Ex: ID
     *
     * @return array|false Returns array if the date given is not empty; otherwise returns FALSE.
     */
    protected static function inel_set_tree( $list, $PIDkey = 'parent_id', $IDkey = 'ID' ){
        $children = [];
        foreach( $list as $child ){
            if( isset( $child[ $PIDkey ] ) ){
                $children[ $child[ $PIDkey ] ][] = $child;
            }
        }

        if( empty( $children ) )
            return false;

        list( $start_tree ) = $children;

        $fn_set_tree = function( $brethren ) use ( &$fn_set_tree, $children, $IDkey ){
            foreach( $brethren as $key => $brother ){
                $ID = $brother [ $IDkey ];

                if( isset( $children[ $ID ] ) )
                    $brother['children'] = $fn_set_tree( $children[ $ID ] );

                $brethren[ $key ] = $brother;
            }

            return $brethren;
        };

        $tree = $fn_set_tree( $start_tree );

        return $tree;
    }

}

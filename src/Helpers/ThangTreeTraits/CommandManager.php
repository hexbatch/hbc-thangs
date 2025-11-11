<?php

namespace Hexbatch\Thangs\Helpers\ThangTreeTraits;

use App\Helpers\Utilities;
use BlueM\Tree as BlueTree;
use BlueM\Tree\Exception\InvalidParentException as BlueInvalidParentException;
use BlueM\Tree\Node as BlueNode;

use Hexbatch\Thangs\Data\Params\CommandParams;
use Hexbatch\Thangs\Enums\TypeOfCmdStatus;
use Hexbatch\Thangs\Exceptions\ThangException;
use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use Tree\Node\Node as RedNode;
use Tree\Visitor\YieldVisitor as RedYieldVisitor;

trait CommandManager
{
    protected RedNode|null $red_tree = null;

    /** @var Collection<int,ThangCommand> $active_command_collection */
    protected Collection $active_command_collection;

    /** @var Collection<int,ThangCommand> $active_command_collection */
    protected Collection $passive_command_collection;

    protected function makeRedTree(): ?RedNode
    {
        $blue_tree = $this->makeBlueTree();
        $mop = $blue_tree->getRootNodes()[0];
        if (!$mop) {
            return null;
        }

        $builder = new \Tree\Builder\NodeBuilder;
        $builder->value($mop->cmd);
        $this->makeTempTreeInner(builder: $builder, mop: $mop);
        return $builder->getNode();
    }

    protected function makeTempTreeInner(\Tree\Builder\NodeBuilder $builder, ?BlueNode $mop = null)
    {

        if (!$mop) {
            return;
        }
        $meeps = $mop->getChildren();
        foreach ($meeps as $child_meep) {

            if ($child_meep->hasChildren()) {
                $builder->tree($child_meep->cmd);
                $this->makeTempTreeInner(builder: $builder, mop: $child_meep);
                $builder->end();
            } else {
                $builder->leaf($child_meep->cmd);
            }
        }
    }


    protected function makeBlueTree(): BlueTree
    {

        /** @var array<string,int> $uuid_map */
        $uuid_map = [];
        $counter = 0;
        foreach ($this->active_command_collection as $cmd) {
            $uuid_map[$cmd->ref_uuid] = $counter++;
        }

        $nodes = [];
        $root_id = -1;
        foreach ($this->active_command_collection as $cmd) {
            $id = $uuid_map[$cmd->ref_uuid];
            $parent_id = $root_id;
            if ($cmd->parent_ref_uuid) {
                $parent_id = $uuid_map[$cmd->parent_ref_uuid];
            }

            $title = sprintf("%s %s", $cmd->ref_uuid, $cmd->command_class);
            $nodes[] = ['id' => $id, 'parent' => $parent_id, 'title' => $title, 'cmd' => $cmd];
        }


        try {
            if (count($nodes)) {
                $tree = new BlueTree($nodes, [
                    'rootId' => $root_id
                ]);
            } else {
                $tree = null;
            }
        } catch (BlueInvalidParentException $e) {
            throw new ThangException(message: __("hbc-thangs::thangs.cannot_make_tree", ['ref' => $e->getMessage()]), previous: $e);
        }

        if (count($tree->getRootNodes()) > 1) {
            throw new ThangException(__("hbc-thangs::thangs.invalid_tree_multiple_roots"));
        }
        return $tree;

    }


    public static function generateMemoryCommand(CommandParams $params, ?string $parent_uuid = null): ThangCommand
    {
        CommandParams::validate($params->toArray());
        $command = new ThangCommand([
            'ref_uuid'=>  Str::uuid()->toString(),
            'parent_ref_uuid' => $parent_uuid,
            'command_class' => $params->command_class,
            'command_args' => $params->command_args,
            'command_tags' => $params->command_tags,
            'is_async' => $params->is_async,
            'bubble_exceptions' => !!$params->bubble_exceptions,
            'command_status' => TypeOfCmdStatus::CMD_WAITING,

        ]);
        return $command;
    }


    /**
     * @param array<CommandParams|ThangCommand|array> $params
     */
    protected function addCommands(array|Collection $params, ?string $insert_at_parent_uuid = null)
    {
        $this->addCommandsInner($params, $insert_at_parent_uuid);
        $this->red_tree = $this->makeRedTree();
    }

    /**
     * @param array<CommandParams|ThangCommand|array> $params
     */
    protected function addCommandsInner(array|Collection $params, ?string $insert_at_parent_uuid = null): ?string
    {
        $parent_uuid = null;
        if ($insert_at_parent_uuid) {
            $parent = $this->active_command_collection->filter(
                function (ThangCommand $value, $key) use ($insert_at_parent_uuid) {
                    Utilities::ignoreVar($key);
                    return $value->ref_uuid === $insert_at_parent_uuid;
                })->first();

            if (!$parent) {
                throw new ThangException("parent not found by " . $insert_at_parent_uuid);
            }

            $parent_uuid = $parent->ref_uuid;
        }
        $cmd = null;
        foreach ($params as $param) {
            if (is_array($param)) {
                $my_parent = $cmd?->ref_uuid ?? $parent_uuid;
                $this->addCommandsInner(params: $param, insert_at_parent_uuid: $my_parent);
            } else {
                if ($param instanceof CommandParams) {
                    $cmd = static::generateMemoryCommand($param, $parent_uuid);
                } elseif ($param instanceof ThangCommand) {
                    $cmd =  $param;
                } else {
                    if (is_object($param)) {
                        $subtext = get_class($param);
                    } else {
                        $subtext = (string)$param;
                    }
                    throw new \LogicException("Only CommandParams|ThangCommand|array got " . $subtext);
                }

            }
            $this->active_command_collection->offsetSet($cmd->ref_uuid, $cmd);
            $this->passive_command_collection->offsetSet($cmd->ref_uuid, $cmd);


        }

        return $parent_uuid;
    }

    protected function removeCommand(ThangCommand $cmd): ?RedNode
    {
        $visitor = new RedYieldVisitor();
        $yield = $this->red_tree->accept($visitor);
        $parent = null;
        /** @var RedNode $ye */
        foreach ($yield as $ye) {
            /** @var ThangCommand $yoo */
            $yoo = $ye->getValue();
            if ($yoo->ref_uuid === $cmd->ref_uuid) {
                $parent = $ye->getParent();
                $parent?->removeChild($ye);
                break;
            }
        }

        $this->active_command_collection->forget($cmd->ref_uuid);

        return $parent;
    }

    public function getCommand(string $uuid): ThangCommand
    {
        $what = $this->active_command_collection->filter(
            function (ThangCommand $value, $key) use ($uuid) {
                Utilities::ignoreVar($key);
                return $value->ref_uuid === $uuid;
            })->first();
        if (!$what) {
            throw new ThangException("Could not find the command $uuid");
        }
        return $what;
    }

    public function getCallback(string $uuid): ThangCallback
    {
        foreach ($this->getAllCommands() as $cmd) {
            foreach ($cmd->command_callbacks as $call) {
                if ($call->ref_uuid === $uuid) {return $call;}
            }
        }
        throw new ThangException("Could not find the callback $uuid");
    }

    protected function getRootCommand() : ?ThangCommand {
        return $this->red_tree->getValue();
    }

    /**
     * @return ThangCommand[]
     */
    protected function getLeaves() : array {
        $ret = [];
        $visitor = new RedYieldVisitor();
        $yield = $this->red_tree->accept($visitor);
        /** @var RedNode $ye */
        foreach ($yield as $ye) {
            /** @var ThangCommand $cmd */
            $cmd = $ye->getValue();
            $ret[] = $cmd;
        }
        return $ret;
    }

    /** @return Collection<ThangCommand> */
    public function getAllCommands() : Collection { return $this->passive_command_collection; }

    /**
     * @return Collection<ThangCommand>
     */
    protected function getChildren(ThangCommand $cmd): Collection
    {
        return $this->getAllCommands()->filter(
            function (ThangCommand $value, $key) use ($cmd) {
                Utilities::ignoreVar($key);
                return $value->parent_ref_uuid === $cmd->ref_uuid;
            });
    }


}

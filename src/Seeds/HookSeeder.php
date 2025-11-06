<?php
namespace Hexbatch\Thangs\Seeds;

use Hexbatch\Thangs\Interfaces\IHookDefinition;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Hexbatch\Thangs\HexbatchThangsProvider;
use Hexbatch\Thangs\Models\ThangHook;
use Illuminate\Database\Seeder;
use Ranium\SeedOnce\Traits\SeedOnce;
use TorMorten\Eventy\Facades\Eventy;

class HookSeeder extends Seeder
{
    use SeedOnce;

    /**
     * @return IHookDefinition|null
     */
    protected static function castClassNameToHookDefinition(string $full_class_name)  {
        $interfaces = class_implements($full_class_name);
        if (isset($interfaces['Hexbatch\Thangs\Interfaces\IHookDefinition'])) {
            /** @type IHookDefinition */
            return $full_class_name;
        }
        return null;
    }

    protected function saveHooks(){
        $dirs = Eventy::filter(HexbatchThangsProvider::HOOK_CREATION_EVENT,[]);
        foreach ($dirs as $dir) {
            $directory = new RecursiveDirectoryIterator($dir);
            $flattened = new RecursiveIteratorIterator($directory);
            $files = new RegexIterator($flattened, '#\.(?:php)$#Di');
            foreach($files as $file) {
                $namespace = HexbatchThangsProvider::extract_namespace($file);
                $class = basename($file, '.php');
                $full_class_name = $namespace . '\\' .$class;
                if( $dodge = static::castClassNameToHookDefinition($full_class_name)) {
                    $name = $dodge::getHookName();
                    if (!$name) {continue;}
                    if (ThangHook::where('hook_name',$name)->first()) {
                        $this->command->warn("Hook $name already registered");
                        continue;
                    }
                    $hook = new ThangHook();
                    $hook->owning_namespace_id = null;
                    $hook->hook_notes = $dodge::getHookNotes();
                    $hook->hook_tags = $dodge::getHookTags();
                    $hook->hook_name = $dodge::getHookName();
                    $hook->event_name = $dodge::getHookEvent();
                    $hook->is_pre = $dodge::isHookPre();
                    $hook->is_async = $dodge::isAsync();
                    $hook->hook_data = $dodge::getHookData();
                    $hook->save();
                    $this->command->info("Created hook $name ");
                }
            }
        }
    }


    public function run()
    {
      $this->saveHooks();
    }
}

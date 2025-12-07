<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeEnumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:enum {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new enum class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $enumName = Str::studly($name);
        $enumPath = app_path("Enums/{$enumName}.php");

        if(!File::exists(app_path("Enums")))
        {
            File::makeDirectory(app_path("Enums") , 0755 , true);
        }
        if(File::exists($enumPath))
        {
            $this->error("Enum {$enumName} already exists !");
            return ;
        }

        $stub = <<<EOT
        <?php

        namespace App\Enums;
        use App\Traits\EnumToArray;

        enum {$enumName}: string
        {
            use EnumToArray;

            case Example = 'example';
        }
        EOT;

        File::put($enumPath , $stub);

        $this->info("Enum {$enumName} created successfully at app/Enums/{$enumName}.php");
    }
}

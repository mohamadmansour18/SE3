<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeTraitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:trait {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $traitName = Str::studly($name);
        $traitPath = app_path("Traits/{$traitName}.php");

        if(!File::exists(app_path('Traits')))
        {
            File::makeDirectory(app_path('Traits') , 0755, true);
        }
        if(File::exists($traitPath))
        {
            $this->error("The trait {$traitName} already exists !");
            return ;
        }

        $stub = <<<EOT
        <?php
        namespace App\Traits;

        trait {$traitName}
        {
            //
        }
        EOT;

        File::put($traitPath, $stub);
        $this->info("The trait {$traitName} has been created successfully at app/Enums/{$traitName}.php");
    }
}

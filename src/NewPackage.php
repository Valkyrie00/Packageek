<?php

namespace Valkyrie\Packageek;

use Illuminate\Console\Command;

class NewPackage extends Command
{

    protected $signature = "packageek:create";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->getBanner();

        $this->info('Ciao sono io');
        //$check = $this->ask('Che cosa vuoi?');
        $this->confirm('Sono in confirm');
        $this->warn('Warning');
    }


    public function getBanner()
    {
        $this->info('*************************');
        $this->info('***** Packageek *********');
        $this->info('*************************');
    }

}
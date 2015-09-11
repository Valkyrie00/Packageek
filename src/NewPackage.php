<?php

namespace Valkyrie\Packageek;

use Illuminate\Console\Command;
use Valkyrie\Packageek\Helper;

class NewPackage extends Command
{

    protected $signature = "packageek:create";

    protected $helper;

    public function __construct(Helper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }

    public function handle()
    {
        //****************************************
        //Recupero banner packageek
        //****************************************
        $this->getBanner();



        //****************************************
        //Inizzializo cartella contenitore pacchetto
        //****************************************
        $package_folder = $this->ask('Indicare la folder principale del pacchetto');
        if($package_folder)
        {
            //****************************************
            //Verifico cartella contenitore
            //****************************************
            $check_root_folder = $this->helper->fileExists($package_folder);
            if($check_root_folder === true){
                $this->warn('Warning : Cartella già presente.');
                
                //****************************************
                //Verifico se si vuole creare il pacchetto nella cartella
                //****************************************
                $folder_confirm = $this->confirm('Vuoi creare il nuovo pacchetto al suo interno?');
                if($folder_confirm == 'y' || $folder_confirm == 'yes')
                {
                    $this->getVendor($package_folder);
                }
                else
                {
                    exit();
                }
            }else{

                //****************************************
                //Creo cartella con il nome della root folder specificata
                //****************************************
                $this->helper->makeDir($package_folder);
                $this->getVendor($package_folder);

            }
        }
    }


    public function getVendor($package_folder)
    {
        //****************************************
        //Inizializzo nome del vendor
        //****************************************
        $vendor_name = $this->ask('Indicare nome del vendor');

        //****************************************
        //Controllo se esite la cartella vendor
        //****************************************
        $check_vendor_name = $this->helper->fileExists($package_folder.'/'.$vendor_name);
        if($check_vendor_name === true)
        {
            //TODO: creare un pacchetto all'intero dello stesso vendor
        }
        else
        {
            //****************************************
            //Creo cartella con il nome del vendor specificato
            //****************************************
            $this->helper->makeDir($package_folder.'/'.$vendor_name);

            //****************************************
            //Passo alla creazione del pacchetto
            //****************************************
            $this->getPackage($package_folder, $vendor_name);
        }
    }

    public function getPackage($package_folder, $vendor_name)
    {
        //****************************************
        //Inizializzo nome del pacchetto
        //****************************************
        $package_name = $this->ask('Indicare nome del pacchetto');

        //****************************************
        //Controllo se esite la cartella del pacchetto
        //****************************************
        $check_package_name = $this->helper->fileExists($package_folder.'/'.$vendor_name.'/'.$package_name);
        if($check_package_name === true)
        {
            //TODO: chiedo se sivuole creare un sottopacchetto all'interno di questo paccheddo
        }
        else
        {
            //****************************************
            //Creo cartella con il nome del pacchetto specificato
            //****************************************
            $this->helper->makeDir($package_folder.'/'.$vendor_name.'/'.$package_name);

            //****************************************
            //Creo cartella src
            //****************************************
            $this->helper->makeDir($package_folder.'/'.$vendor_name.'/'.$package_name.'/src');

            //****************************************
            //Creo cartella facades
            //****************************************
            $this->helper->makeDir($package_folder.'/'.$vendor_name.'/'.$package_name.'/src/Facades');

            //****************************************
            //Verifico se si vuole creare un pacchetto semplice o un pacchetto complesso
            //****************************************
            $choice_package_type = $this->choice('Quale tipologia di pacchetto vuoi costrutire? ', array('semplice', 'complesso'));
            if($choice_package_type == 'semplice'){
                $this->generateSimplePackage($package_folder, $vendor_name, $package_name);
                $this->info('Fine Semplice!!');
            }
            if($choice_package_type == 'complesso'){
                $this->generateAdvancedPackage($package_folder, $vendor_name, $package_name);
                $this->info('Fine Advanced!!');
            }
            
        }
    }

    public function generateSimplePackage($package_folder, $vendor_name, $package_name)
    {
        $package_path = $package_folder.'/'.$vendor_name.'/'.$package_name;
        $src_package_path = $package_path.'/src';
        
        //****************************************
        // Genero composer
        //****************************************
        $composer = $package_path.'/composer.json';
        $this->helper->replaceAndSave(__DIR__.'/Structure/Simple/Composer.stub', ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}'], [$vendor_name, $package_name, ucfirst($vendor_name), ucfirst($package_name)], $composer);

        //****************************************
        // Genero service Provider
        //****************************************
        $provider = $src_package_path.'/'.ucfirst($package_name).'ServiceProvider.php';
        $this->helper->replaceAndSave(__DIR__.'/Structure/Simple/ServiceProvider.stub', ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}'], [$vendor_name, $package_name, ucfirst($vendor_name), ucfirst($package_name)], $provider);

        //****************************************
        // Genero Helper
        //****************************************
        $helper = $src_package_path.'/'.ucfirst($package_name).'.php';
        $this->helper->replaceAndSave(__DIR__.'/Structure/Simple/Helper.stub', ['{{vendor}}', '{{name}}'], [ucfirst($vendor_name), ucfirst($package_name)], $helper);

        //****************************************
        // Genero Facade
        //****************************************
        $facade = $src_package_path.'/Facades/'.ucfirst($package_name).'.php';
        $this->helper->replaceAndSave(__DIR__.'/Structure/Simple/Facade.stub', ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}'], [$vendor_name, $package_name, ucfirst($vendor_name), ucfirst($package_name)], $facade);


        return true;
    }

    public function generateAdvancedPackage($package_folder, $vendor_name, $package_name)
    {
        $package_path = $package_folder.'/'.$vendor_name.'/'.$package_name;
        $src_package_path = $package_path.'/src';
        
        //****************************************
        // Genero composer
        //****************************************
        $composer = $package_path.'/composer.json';
        $this->helper->replaceAndSave(__DIR__.'/Structure/Advanced/Composer.stub', ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}'], [$vendor_name, $package_name, ucfirst($vendor_name), ucfirst($package_name)], $composer);

        $number_sub_package = $this->ask('Quanti sotto pacchetti desideri crare?');
        for($x = 1; $x <= $number_sub_package; $x++) {
            $sub_package_name = $this->ask('Indicare nome sottopacchetto');

            //****************************************
            // Genero cartella sottopacchetto
            //****************************************
            $this->helper->makeDir($src_package_path.'/'.ucfirst($sub_package_name));
            $sub_package_path = $src_package_path.'/'.ucfirst($sub_package_name);

            //****************************************
            // Genero service Provider
            //****************************************
            $provider = $sub_package_path.'/'.ucfirst($sub_package_name).'ServiceProvider.php';
            $this->helper->replaceAndSave(__DIR__.'/Structure/Advanced/ServiceProvider.stub', ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}', '{{USname}}', '{{Sname}}'], [$vendor_name, $package_name, ucfirst($vendor_name), ucfirst($package_name), ucfirst($sub_package_name), $sub_package_name], $provider);

            //****************************************
            // Genero Helper
            //****************************************
            $helper = $sub_package_path.'/'.ucfirst($sub_package_name).'.php';
            $this->helper->replaceAndSave(__DIR__.'/Structure/Advanced/Helper.stub', ['{{Uvendor}}', '{{Uname}}', '{{USname}}'], [ucfirst($vendor_name), ucfirst($package_name), ucfirst($sub_package_name) ], $helper);

            //****************************************
            // Genero Facade
            //****************************************
            $facade = $src_package_path.'/Facades/'.ucfirst($sub_package_name).'.php';
            $this->helper->replaceAndSave(__DIR__.'/Structure/Advanced/Facade.stub', ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}', '{{USname}}', '{{Sname}}'], [$vendor_name, $package_name, ucfirst($vendor_name), ucfirst($package_name), ucfirst($sub_package_name), $sub_package_name], $facade);
        } 

        return true;

    }

    public function getBanner()
    {
        $this->info("\n*************************");
        $this->info("***** Packageek *********");
        $this->info("*************************\n");
    }

}
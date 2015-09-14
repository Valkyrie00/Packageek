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
        // Recupero banner packageek
        //****************************************
        $this->getBanner();

        //****************************************
        // Inizializzo cartella contenitore pacchetto
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
                // Verifico se si vuole creare il pacchetto nella cartella
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
                // Creo cartella con il nome della root folder specificata
                //****************************************
                if($this->helper->makeDir($package_folder) === true)
                {
                    $this->getVendor($package_folder);
                }
            }
        }
    }


    public function getVendor($package_folder)
    {
        //****************************************
        // Inizializzo nome del vendor
        //****************************************
        $vendor_name = $this->ask('Indicare nome del vendor');

        //****************************************
        //Controllo se esite la cartella vendor
        //****************************************
        $check_vendor_name = $this->helper->fileExists($package_folder.'/'.$vendor_name);
        if($check_vendor_name === true)
        {
            //TODO: creare un pacchetto all'intero dello stesso vendor
            $this->warn('Warning : Vendor già presente.');
            exit();
        }
        else
        {
            //****************************************
            // Creo cartella con il nome del vendor specificato
            //****************************************
            if($this->helper->makeDir($package_folder.'/'.$vendor_name) === true)
            {
                //****************************************
                // Passo alla creazione del pacchetto
                //****************************************
                $this->getPackage($package_folder, $vendor_name);
            }
        }
    }

    public function getPackage($package_folder, $vendor_name)
    {
        //****************************************
        // Inizializzo nome del pacchetto
        //****************************************
        $package_name = $this->ask('Indicare nome del pacchetto');

        //****************************************
        // Controllo se esite la cartella del pacchetto
        //****************************************
        $check_package_name = $this->helper->fileExists($package_folder.'/'.$vendor_name.'/'.$package_name);
        if($check_package_name === false)
        {
            //****************************************
            // Creo cartella con il nome del pacchetto specificato
            //****************************************
            $this->helper->makeDir($package_folder.'/'.$vendor_name.'/'.$package_name);

            //****************************************
            // Creo cartella src
            //****************************************
            $this->helper->makeDir($package_folder.'/'.$vendor_name.'/'.$package_name.'/src');

            //****************************************
            // Creo cartella facades
            //****************************************
            $this->helper->makeDir($package_folder.'/'.$vendor_name.'/'.$package_name.'/src/Facades');

            //****************************************
            // Verifico se si vuole creare un pacchetto semplice o un pacchetto complesso
            //****************************************
            $choice_package_type = $this->choice('Quale tipologia di pacchetto vuoi costrutire? ', array('simple', 'advanced'));
            if($choice_package_type == 'simple'){
                $this->generateSimplePackage($package_folder, $vendor_name, $package_name);
                $this->info('Fine Semplice!!');
            }
            if($choice_package_type == 'advanced'){
                $this->generateAdvancedPackage($package_folder, $vendor_name, $package_name);
                $this->info('Fine Advanced!!');
            }
            
        }
    }

    public function generateSimplePackage($package_folder, $vendor_name, $package_name)
    {
        $package_component = ['package_folder' => $package_folder, 'vendor_name' => $vendor_name, 'package_name' => $package_name];

        //****************************************
        // Genero composer
        //****************************************
        $this->helper->generateComposer($package_component, false);

        //****************************************
        // Genero componenti
        //****************************************
        $this->helper->generateComponent($package_component, false);

        $this->helper->generateSpecSuite($package_component, false);

        return true;
    }

    public function generateAdvancedPackage($package_folder, $vendor_name, $package_name)
    {
        $package_path = $package_folder.'/'.$vendor_name.'/'.$package_name;
        $src_package_path = $package_path.'/src';

        $number_sub_package = $this->ask('Quanti sotto pacchetti desideri crare?');

        $spec_component = [];
        for($x = 1; $x <= $number_sub_package; $x++) {
            $sub_package_name = $this->ask('Indicare nome sottopacchetto');

            array_push($spec_component, $sub_package_name);

            //****************************************
            // Genero cartella sottopacchetto
            //****************************************
            $this->helper->makeDir($src_package_path.'/'.ucfirst($sub_package_name));

            //****************************************
            // Genero componenti sottopacchetto
            //****************************************
            $sub_package_component = ['package_folder' => $package_folder, 'vendor_name' => $vendor_name, 'package_name' => $package_name, 'sub_package_name' => ucfirst($sub_package_name)];
            $this->helper->generateComponent($sub_package_component, true);
        } 

        //****************************************
        // Genero phpspec suite file
        //****************************************
        $this->helper->generateSpecSuite(['package_folder' => $package_folder, 'vendor_name' => $vendor_name, 'package_name' => $package_name, 'component' => $spec_component], true);

        //****************************************
        // Genero composer
        //****************************************
        $this->helper->generateComposer(['package_folder' => $package_folder, 'vendor_name' => $vendor_name, 'package_name' => $package_name, 'component' => $spec_component], true);

        return true;
    }

    public function getBanner()
    {
        $this->info("\n*************************");
        $this->info("***** Packageek *********");
        $this->info("*************************\n");
    }

}
<?php

namespace Valkyrie\Packageek;

use Illuminate\Console\Command;
use Valkyrie\Packageek\Helper;

class NewPackage extends Command
{

    protected $signature = "make:package";
    protected $description = "Create a new package";
    protected $helper;

    public function __construct(Helper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->lang = require __DIR__.'/Lang/en.php';
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
        $package_folder = $this->ask($this->lang['ask1']);
        if($package_folder)
        {
            //****************************************
            //Verifico cartella contenitore
            //****************************************
            $check_root_folder = $this->helper->fileExists($package_folder);
            if($check_root_folder === true){
                $this->warn($this->lang['warning1']);
                
                //****************************************
                // Verifico se si vuole creare il pacchetto nella cartella
                //****************************************
                $folder_confirm = $this->confirm($this->lang['confirm1']);
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

    //****************************************
    // Step 1
    //****************************************
    public function getVendor($package_folder)
    {
        //****************************************
        // Inizializzo nome del vendor
        //****************************************
        $vendor_name = $this->ask($this->lang['ask2']);

        //****************************************
        //Controllo se esite la cartella vendor
        //****************************************
        $check_vendor_name = $this->helper->fileExists($package_folder.'/'.$vendor_name);
        if($check_vendor_name === true)
        {
            $this->warn($this->lang['warning2']);
            $this->getVendor($package_folder);
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

    //****************************************
    // Step 2
    //****************************************
    public function getPackage($package_folder, $vendor_name)
    {
        //****************************************
        // Inizializzo nome del pacchetto
        //****************************************
        $package_name = $this->ask($this->lang['ask3']);

        //****************************************
        // Controllo se esite la cartella del pacchetto
        //****************************************
        $check_package_name = $this->helper->fileExists($package_folder.'/'.$vendor_name.'/'.$package_name);
        if($check_package_name === false)
        {
            $data = [
                        'package_folder' => $package_folder,
                        'vendor_name'    => $vendor_name,
                        'package_name'   => $package_name
                    ];
            //****************************************
            // Generate package folder, src folder and facades folder
            //****************************************
            if($this->helper->generateDirComponent($data) === true)
            {
                //****************************************
                // Verifico se si vuole creare un pacchetto semplice o un pacchetto complesso
                //****************************************
                $choice_package_type = $this->choice($this->lang['choice1'], array('simple', 'advanced'));
                if($choice_package_type == 'simple'){
                    if($this->generateSimplePackage($data) === true)
                    {
                        $this->getFinish();
                    }
                }
                if($choice_package_type == 'advanced'){
                    if($this->generateAdvancedPackage($data) === true)
                    {
                        $this->getFinish();
                    }
                }
            }
        }
    }

    public function generateSimplePackage($package)
    {
        //****************************************
        // Generate composer
        //****************************************
        $this->helper->generateSimpleComposer($package);

        //****************************************
        // Generate component
        //****************************************
        $this->helper->generateSimpleComponent($package);
        
        //****************************************
        // Generate suite phpspec
        //****************************************
        $this->helper->generateSimpleSpecSuite($package);

        return true;
    }

    public function generateAdvancedPackage($package)
    {

        $package_path       = $package['package_folder'].'/'.$package['vendor_name'].'/'.$package['package_name'];
        $src_package_path   = $package_path.'/src';

        $number_sub_package = $this->ask($this->lang['ask4']);

        $spec_component = [];
        for($x = 1; $x <= $number_sub_package; $x++) {
            $sub_package_name = $this->ask($this->lang['ask5']);

            array_push($spec_component, $sub_package_name);

            //****************************************
            // Genero cartella sottopacchetto
            //****************************************
            if($this->helper->makeDir($src_package_path.'/'.ucfirst($sub_package_name)) === true)
            {
                //****************************************
                // Genero componenti sottopacchetto
                //****************************************
                $package['sub_package_name'] = ucfirst($sub_package_name);
                    $this->helper->generateAdvancedComponent($package);
                    
                    //Modifico app config
                    $this->helper->addToAppProviders($package);

                    $this->helper->addToAppComposer($package);

                    $this->helper->addToAppAliases($package);

                unset($package['sub_package_name']);

            }else{
                $this->warn($this->lang['warning3']);
            }
        }

        $package['component'] = $spec_component;

        //****************************************
        // Genero phpspec suite file
        //****************************************
        $this->helper->generateAdvancedSpecSuite($package);

        //****************************************
        // Genero composer
        //****************************************
        $this->helper->generateAdvancedComposer($package);

        return true;
    }

    public function getBanner()
    {
        $this->info("\n*************************");
        $this->info("***** Packageek *********");
        $this->info("*************************\n");
    }

    public function getFinish()
    {
        shell_exec('composer dump-autoload');
        
        $this->info("\n*************************");
        $this->info("***** ".$this->lang['info1']." *****");
        $this->info("*************************\n");
    }

}
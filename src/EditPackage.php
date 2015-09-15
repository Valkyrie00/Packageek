<?php

namespace Valkyrie\Packageek;

use Illuminate\Console\Command;
use Valkyrie\Packageek\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

class EditPackage extends Command
{

    protected $signature = "edit:package";
    protected $description = "Edit a package";
    protected $helper;

    public function __construct(Helper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->lang = require __DIR__.'/Lang/en.php';
    }

    public function handle()
    {
        $this->getBanner();
        //die();

        //****************************************
        // Inizializzo cartella contenitore pacchetto
        //****************************************
        $package_folder = $this->ask('Indicare cartella contenitore');
        if($package_folder)
        {
            //****************************************
            // Verifico cartella contenitore
            //****************************************
            $check_root_folder = $this->helper->fileExists($package_folder);
            if($check_root_folder === true){

                //****************************************
                // Verifico quali vendor sono al suo interno
                //****************************************
                $vendor_list = $this->helper->getVendorList($package_folder);
                $vendor_list = $this->helper->cleanDirList($vendor_list);

                if(count($vendor_list) >= 1){
                    $this->table(array('Vendor'), array($vendor_list));
                    
                    $choice_vendor = $this->choice('quale vendor usare?', $vendor_list);
                    $package_list = $this->helper->getPackageList($package_folder.'/'.$choice_vendor);
                    

                    /*$data = [
                        'package_folder' => $package_folder,
                        'vendor_name'    => $choice_vendor,
                        'package_name'   => $package_name
                    ];*/
                    //$sub_package_name = $this->ask('Indirare nome sottopacchetto');
                    var_dump($package_list);


                }else{
                    echo 'nessun vendor trovato';
                }


            }else{
                $this->warn('cartella non presente');
                exit();
            }
        }

    }

    public function getBanner()
    {
        $this->info("\n*************************");
        $this->info("***** Packageek *********");
        $this->info("*************************\n");
    }

    public function getFinish()
    {
        $this->info("\n*************************");
        $this->info("***** ".$this->lang['info1']." *****");
        $this->info("*************************\n");
    }

}
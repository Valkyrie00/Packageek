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

        //****************************************
        // Inizializzo cartella contenitore pacchetto
        //****************************************
        $package_folder = $this->ask($this->lang['ask1']);
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
                
                $table[] = $package_folder;

                if(count($vendor_list) >= 1){

                    $choice_vendor = $this->choice($this->lang['ask2'], $vendor_list);

                    $table[] = $choice_vendor;
                    $this->table(array('Directory','Vendor'), array($table));

                    $package_list = $this->helper->getPackageList($package_folder.'/'.$choice_vendor);
                    $package_list = $this->helper->cleanDirList($package_list);
                    
                    $choice_package = $this->choice($this->lang['ask3'], $package_list);
                    $table[] = $choice_package;
                    $this->table(array('Directory','Vendor','Package'), array($table));

                    $data = [
                        'package_folder' => $package_folder,
                        'vendor_name'    => $choice_vendor,
                        'package_name'   => $choice_package
                    ];

                    if($this->generateAdvancedPackage($data) === true)
                    {
                        $this->getFinish();
                    }

                }else{
                    $this->warn($this->lang['warning4']);
                }

            }else{
                $this->warn($this->lang['warning5']);
                exit();
            }
        }
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
                unset($package['sub_package_name']);

            }else{
                $this->warn($this->lang['warning3']);
            }
        }

        $package['component'] = $spec_component;

        //****************************************
        // Modifico phpspec suite file
        //****************************************
        $this->helper->generateAdvancedSpecSuite($package);

        //****************************************
        // Modifico composer file
        //****************************************
        $this->helper->editAdvancedComposer($package);

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
        $this->info("\n*************************");
        $this->info("***** ".$this->lang['info1']." *****");
        $this->info("*************************\n");
    }

}
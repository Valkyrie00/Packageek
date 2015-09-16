<?php

namespace Valkyrie\Packageek;

use RuntimeException;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem as Filesystem;

class Helper
{

    public function __construct()
    {
        $this->file = new Filesystem;
    }

    public function fileExists($argument1)
    {
        if($this->file->exists($argument1))
        {
           return true;
        }
        return false;
    }

    public function makeDir($path, $mode = 0755, $recursive = false, $force = false)
    {
        return $this->file->makeDirectory($path, $mode, $recursive, $force);
    }
    
    public function replaceAndSave($oldFile, $search, $replace, $newFile = null)
    {
        $newFile    = ($newFile == null) ? $oldFile : $newFile ;
        $file       = $this->file->get($oldFile);
        $replacing  = str_replace($search, $replace, $file);
        $this->file->put($newFile, $replacing);
    }

    public function replaceAndConcatenate($oldFile, $search, $replace, $newFile = null)
    {
        $newFile    = ($newFile == null) ? $oldFile : $newFile ;
        $file       = $this->file->get($oldFile);
        $replacing  = str_replace($search, $replace, $file);
        $this->file->append($newFile, $replacing);
    }

    public function generateSimpleComponent($package_component)
    {
        $src_package_path   = $this->generateSrcPackagePath($package_component);
        $file_name          = ucfirst($package_component['package_name']);

        $service_path       = $src_package_path;
        $helper_path        = $src_package_path;
        $facade_path        = $src_package_path;
        
        $data_search  = [
                            '{{vendor}}', 
                            '{{name}}', 
                            '{{Uvendor}}', 
                            '{{Uname}}'
                        ];
        $data_replace = [
                            $package_component['vendor_name'], 
                            $package_component['package_name'], 
                            ucfirst($package_component['vendor_name']), 
                            ucfirst($package_component['package_name'])
                        ];
    
        $structure    = 'Simple';

        //****************************************
        // Genero service Provider
        //****************************************
        $provider = $service_path.$file_name.'ServiceProvider.php';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/ServiceProvider.stub', $data_search, $data_replace, $provider);

        //****************************************
        // Genero Helper
        //****************************************
        $helper = $helper_path.$file_name.'.php';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/Helper.stub', $data_search, $data_replace, $helper);

        //****************************************
        // Genero Facade
        //****************************************
        $facade = $facade_path.'Facades/'.$file_name.'.php';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/Facade.stub', $data_search, $data_replace, $facade);

        return true;
    }

    public function generateAdvancedComponent($package)
    {

        $file_name = ucfirst($package['sub_package_name']);

        $service_path = $package['package_folder'].'/'.$package['vendor_name'].'/'.$package['package_name'].'/src/'.ucfirst($package['sub_package_name']).'/';
        $helper_path = $service_path;
        $facade_path = $package['package_folder'].'/'.$package['vendor_name'].'/'.$package['package_name'].'/src/';

        $data_search  = [
                            '{{vendor}}', 
                            '{{name}}', 
                            '{{Uvendor}}', 
                            '{{Uname}}', 
                            '{{USname}}', 
                            '{{Sname}}'
                        ];
        $data_replace = [
                            $package['vendor_name'], 
                            $package['package_name'], 
                            ucfirst($package['vendor_name']), 
                            ucfirst($package['package_name']), 
                            ucfirst($package['sub_package_name']), 
                            $package['sub_package_name']
                        ];
    
        $structure = 'Advanced';

        //****************************************
        // Genero service Provider
        //****************************************
        $provider = $service_path.$file_name.'ServiceProvider.php';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/ServiceProvider.stub', $data_search, $data_replace, $provider);

        //****************************************
        // Genero Helper
        //****************************************
        $helper = $helper_path.$file_name.'.php';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/Helper.stub', $data_search, $data_replace, $helper);

        //****************************************
        // Genero Facade
        //****************************************
        $facade = $facade_path.'Facades/'.$file_name.'.php';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/Facade.stub', $data_search, $data_replace, $facade);
    }

    public function generateSimpleSpecSuite($package)
    {
        $path_suite = $package['package_folder'].'/'.$package['vendor_name'].'/'.$package['package_name'].'/';
        $this->makeDir($path_suite.'/test');
        $suite = $path_suite.'phpspec.yml';
        $this->replaceAndSave(__DIR__.'/Structure/Advanced/Phpspec/base.stub', '', '', $suite);

        $data_search  = [
                            '{{vendor}}', 
                            '{{name}}', 
                            '{{Uvendor}}', 
                            '{{Uname}}'
                        ];
        $data_replace = [
                            $package['vendor_name'], 
                            $package['package_name'], 
                            ucfirst($package['vendor_name']), 
                            ucfirst($package['package_name'])
                        ];

        $this->replaceAndConcatenate(__DIR__.'/Structure/Simple/Phpspec/partial.stub', $data_search, $data_replace, $suite);   
    }

    public function generateAdvancedSpecSuite($package)
    {
        $path_suite = $package['package_folder'].'/'.$package['vendor_name'].'/'.$package['package_name'].'/';
        $suite = $path_suite.'phpspec.yml';

        if($this->fileExists($path_suite.'/test') === false)
        {
            $this->makeDir($path_suite.'/test');
            $this->replaceAndSave(__DIR__.'/Structure/Advanced/Phpspec/base.stub', '', '', $suite);
        }

        foreach ($package['component'] as $v) {

            $data_search  = [
                                '{{vendor}}', 
                                '{{name}}', 
                                '{{Uvendor}}', 
                                '{{Uname}}', 
                                '{{USname}}', 
                                '{{Sname}}'
                            ];
            $data_replace = [
                                $package['vendor_name'], 
                                $package['package_name'], 
                                ucfirst($package['vendor_name']), 
                                ucfirst($package['package_name']), 
                                ucfirst($v), 
                                $v
                            ];

            $this->replaceAndConcatenate(__DIR__.'/Structure/Advanced/Phpspec/partial.stub', $data_search, $data_replace, $suite);   
        }
        
    }

    public function generatePackagePath($package_component)
    {
        $path = '';
        foreach($package_component as $k => $v) {
            $path .= $v.'/'; 
        }
        return $path;
    }

    public function generateSrcPackagePath($package_component)
    {
        $path = '';
        foreach($package_component as $k => $v) {
            $path .= $v.'/'; 
        }
        $path .= 'src/';
        return $path;
    }

    public function generateSimpleComposer($package)
    {
        $path = $this->generatePackagePath($package);
        $structure = 'Simple';
        
        $spec = "           \"".ucfirst($package['vendor_name'])."\\\\".ucfirst($package['package_name'])."\": \"src/\"";

        $data_search  = [
                            '{{vendor}}', 
                            '{{name}}', 
                            '{{Uvendor}}', 
                            '{{Uname}}', 
                            '{{spec}}'
                        ];
        $data_replace = [
                            $package['vendor_name'], 
                            $package['package_name'], 
                            ucfirst($package['vendor_name']), 
                            ucfirst($package['package_name']), 
                            $spec 
                        ];
        
        $composer = $path.'composer.json';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/Composer.stub', $data_search, $data_replace, $composer);

    }

    public function generateAdvancedComposer($package)
    {
        $spec   = '';
        $n      = count($package['component']);
        $i      = 0;

        foreach ($package['component'] as $value) {
            $i++;
            $spec .= "           \"".ucfirst($package['vendor_name'])."\\\\".ucfirst($package['package_name'])."\\\\".ucfirst($value)."\": \"src/\"";
            if($i < $n)
            {
                $spec .= ",\n";
            }
        }

        $path         = $package['package_folder'].'/'.$package['vendor_name'].'/'.$package['package_name'].'/';
        $structure = 'Advanced';
        $data_search  = [
                            '{{vendor}}', 
                            '{{name}}', 
                            '{{Uvendor}}', 
                            '{{Uname}}', 
                            '{{spec}}'
                        ];
        $data_replace = [
                            $package['vendor_name'], 
                            $package['package_name'], 
                            ucfirst($package['vendor_name']), 
                            ucfirst($package['package_name']), 
                            $spec 
                        ];

        $composer = $path.'composer.json';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/Composer.stub', $data_search, $data_replace, $composer);
    }

    public function editAdvancedComposer($package)
    {
        $path     = $package['package_folder'].'/'.$package['vendor_name'].'/'.$package['package_name'].'/';
        $composer = json_decode($this->file->get($path.'composer.json'), true);
        foreach ($package['component'] as $value) {
            $composer['autoload']['psr-0'][ucfirst($package['vendor_name'])."\\".ucfirst($package['package_name'])."\\".ucfirst($value)] = "src/";
        }
        $composer = json_encode($composer, JSON_PRETTY_PRINT);
        $composer = str_replace('\/', '/', $composer);
        $new_composer = $this->file->put($path.'composer.json', $composer);

        return true;
    }

    public function generateDirComponent($p)
    {
        $status = true;
        $path = $p['package_folder'].'/'.$p['vendor_name'].'/'.$p['package_name'];

        //****************************************
        // Generate package folder
        //****************************************
        if($this->makeDir($path) === true)
        {
            //****************************************
            // Generate src folder
            //****************************************
            if($this->makeDir($path.'/src') === true)
            {
                //****************************************
                // Generte facades folder
                //****************************************
                if($this->makeDir($path.'/src/Facades') === true)
                {
                    $status = true;
                }else{
                    $status = false;
                }
            }else{
                $status = false;
            }
        }else{
            $status = false;
        }

        return $status;
    }

    //************************************
    //********** EDIT ********************
    //************************************

    public function getVendorList($path)
    {
        return $this->file->directories($path);
    }

    public function getPackageList($path)
    {
        return $this->file->directories($path);
    }

    public function cleanDirList($array)
    {
        $list = array();
        foreach ($array as $value) {
            $pieces = explode("/", $value);
            $list[] = array_pop($pieces);
        }
        return $list;
    }

    public function addToAppProviders($package)
    {
        $provider = "        ".ucfirst($package['vendor_name'])."\\".ucfirst($package['package_name'])."\\".ucfirst($package['sub_package_name'])."\\".ucfirst($package['sub_package_name'])."ServiceProvider::class,";
        $search = "'providers' => [";
        $replace = $search."\n".$provider;

        $config_app = $this->file->get('config/app.php');
        $new_provider  = str_replace($search, $replace, $config_app);

        $new_config_app = $this->file->put('config/app.php', $new_provider);
    }

    public function addToAppAliases($package)
    {
        $aliases = "        '".ucfirst($package['sub_package_name'])."'       => ".ucfirst($package['vendor_name'])."\\".ucfirst($package['package_name'])."\Facades\\".ucfirst($package['sub_package_name'])."::class,";
        $search = "'aliases' => [";
        $replace = $search."\n".$aliases;

        $config_app = $this->file->get('config/app.php');
        $new_aliases  = str_replace($search, $replace, $config_app);

        $new_config_app = $this->file->put('config/app.php', $new_aliases);
    }

    public function addToAppComposer($package)
    {
        $composer = json_decode($this->file->get('composer.json'), true);
        $composer['autoload']['psr-4'][ucfirst($package['vendor_name'])."\\".ucfirst($package['package_name'])."\\"] = $package['package_folder']."/".$package['vendor_name']."/".$package['package_name']."/src/";

        $composer = json_encode($composer, JSON_PRETTY_PRINT);
        $composer = str_replace('\/', '/', $composer);
        $new_composer = $this->file->put('composer.json', $composer);
    }
}

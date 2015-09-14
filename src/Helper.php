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

    public function generateComponent($package_component, $advanced = false)
    {
        if($advanced === false){
            $src_package_path = $this->generateSrcPackagePath($package_component);
            
            $file_name = ucfirst($package_component['package_name']);

            $service_path = $src_package_path;
            $helper_path = $src_package_path;
            $facade_path = $src_package_path;
            
            $data_search  = ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}'];
            $data_replace = [$package_component['vendor_name'], $package_component['package_name'], ucfirst($package_component['vendor_name']), ucfirst($package_component['package_name'])];
        
            $structure = 'Simple';
        }else{

            $file_name = ucfirst($package_component['sub_package_name']);

            $service_path = $package_component['package_folder'].'/'.$package_component['vendor_name'].'/'.$package_component['package_name'].'/src/'.ucfirst($package_component['sub_package_name']).'/';
            $helper_path = $service_path;
            $facade_path = $package_component['package_folder'].'/'.$package_component['vendor_name'].'/'.$package_component['package_name'].'/src/';

            $data_search  = ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}', '{{USname}}', '{{Sname}}'];
            $data_replace = [$package_component['vendor_name'], $package_component['package_name'], ucfirst($package_component['vendor_name']), ucfirst($package_component['package_name']),  ucfirst($package_component['sub_package_name']), $package_component['sub_package_name']];
        
            $structure = 'Advanced';
        }

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

    public function generateSpecSuite($package_component, $advanced = false)
    {


        $path_suite = $package_component['package_folder'].'/'.$package_component['vendor_name'].'/'.$package_component['package_name'].'/';

        $this->makeDir($path_suite.'/test');

        $suite = $path_suite.'phpspec.yml';

        if($advanced === true){
            $this->replaceAndSave(__DIR__.'/Structure/Advanced/Phpspec/base.stub', '', '', $suite);   

            foreach ($package_component['component'] as $v) {
                $data_search  = ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}', '{{USname}}', '{{Sname}}'];
                $data_replace = [$package_component['vendor_name'], $package_component['package_name'], ucfirst($package_component['vendor_name']), ucfirst($package_component['package_name']),  ucfirst($v), $v];
                $this->replaceAndConcatenate(__DIR__.'/Structure/Advanced/Phpspec/partial.stub', $data_search, $data_replace, $suite);   
            }
        }else{
            $this->replaceAndSave(__DIR__.'/Structure/Advanced/Phpspec/base.stub', '', '', $suite);   

            $data_search  = ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}'];
            $data_replace = [$package_component['vendor_name'], $package_component['package_name'], ucfirst($package_component['vendor_name']), ucfirst($package_component['package_name'])];
            $this->replaceAndConcatenate(__DIR__.'/Structure/Simple/Phpspec/partial.stub', $data_search, $data_replace, $suite);   
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

    public function generateComposer($package_component, $advanced = false)
    {
        //$package_path = $this->generatePackagePath($package);

        if($advanced === true)
        {
            $spec = '';
            $n = count($package_component['component']);
            $i = 0;
            foreach ($package_component['component'] as $value) {
                $i++;
                $spec .= "           \"".ucfirst($package_component['vendor_name'])."\\\\".ucfirst($package_component['package_name'])."\\\\".ucfirst($value)."\": \"src/\"";
                if($i < $n)
                {
                    $spec .= ",\n";
                }
            }

            $path = $package_component['package_folder'].'/'.$package_component['vendor_name'].'/'.$package_component['package_name'].'/';

            $data_search  = ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}', '{{spec}}'];
            $data_replace = [$package_component['vendor_name'], $package_component['package_name'], ucfirst($package_component['vendor_name']), ucfirst($package_component['package_name']), $spec ];
        
            $structure = 'Advanced';
        }else{
            $path = $this->generatePackagePath($package_component);
            $structure = 'Simple';
            
            $spec = "           \"".ucfirst($package_component['vendor_name'])."\\\\".ucfirst($package_component['package_name'])."\": \"src/\"";

            $data_search  = ['{{vendor}}', '{{name}}', '{{Uvendor}}', '{{Uname}}', '{{spec}}'];
            $data_replace = [$package_component['vendor_name'], $package_component['package_name'], ucfirst($package_component['vendor_name']), ucfirst($package_component['package_name']), $spec ];
        }

        $composer = $path.'composer.json';
        $this->replaceAndSave(__DIR__.'/Structure/'.$structure.'/Composer.stub', $data_search, $data_replace, $composer);
    }


}

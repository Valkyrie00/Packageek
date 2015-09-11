<?php

namespace Packageek\Valkyrie\Packageek;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HelperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Valkyrie\Packageek\Helper');
    }

    function it_if_file_exist_return_bool()
    {
    	$path_true = 'vendor';
    	$this->fileExists($path_true)->shouldReturn(true);

    	$path_false = 'vendors';
    	$this->fileExists($path_false)->shouldReturn(false);
    }

    function it_if_makeDir_return_bool()
    {
    	$dir_make = 'folder_test_remove_pls';
    	$dir = 'packages/valkyrie/packageek/folder_test_remove_pls';

    	if($this->fileExists($dir) === false)
    	{
    		$this->makeDir($dir_make)->shouldReturn(true);
    	}

		if($this->fileExists($dir) === true)
    	{
    		$create =$this->makeDir($dir_make, null, null, true)->shouldReturn(false);
    	}
    }

}

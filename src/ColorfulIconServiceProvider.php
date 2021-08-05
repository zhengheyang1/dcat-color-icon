<?php

namespace Zhy\ColorfulIcon;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Zhy\ColorfulIcon\Extensions\Form\ColorIcon;

class ColorfulIconServiceProvider extends ServiceProvider
{
	protected $js = [
	    'js/iconfont.js'
    ];
	protected $css = [
		'css/iconfont.css'
	];

//	protected $menu = [
//	  'title'   => '菜单',
//	  'uri'     => 'colorful-icon',
//	  'icon'   => '#icon-pingtai',
//    ];

	public function register()
	{
		//
	}

	public function init()
	{
        parent::init();

        Admin::requireAssets('@zhy.dcat-color-icon');

        Form::extend('colorIcon',ColorIcon::class);

        app('view')->prependNamespace('admin',$this->getViewPath());

        //

	}

	public function settingForm()
	{
		return new Setting($this);
	}
}

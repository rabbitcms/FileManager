<?php namespace Modules\Filemanager\Providers;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider
{

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerTranslations();
		$this->registerConfig();
		$this->registerViews();
	}

	/**
	 * Register config.
	 *
	 * @return void
	 */
	protected function registerConfig()
	{
		$this->publishes([
			__DIR__.'/../Config/config.php' => config_path('module/filemanager.php'),
		]);
		$this->mergeConfigFrom(
			__DIR__.'/../Config/config.php', 'module.filemanager'
		);
	}

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = base_path('resources/views/modules/filemanager');

		$sourcePath = __DIR__.'/../Resources/views';

		$this->publishes([
			$sourcePath => $viewPath,
		]);

		$this->loadViewsFrom(array_merge(array_map(function ($path) {
			return $path.'/modules/filemanager';
		}, \Config::get('view.paths')), [$sourcePath]), 'filemanager');
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = base_path('resources/lang/modules/filemanager');

		if (is_dir($langPath)) {
			$this->loadTranslationsFrom($langPath, 'filemanager');
		} else {
			$this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'filemanager');
		}
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}

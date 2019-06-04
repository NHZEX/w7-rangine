<?php

namespace W7\Console\Command\Vendor;

use W7\Console\Command\GeneratorCommandAbstract;
use W7\Core\Helper\StringHelper;

class MakeCommand extends GeneratorCommandAbstract {
	protected $description = 'generate package';


	protected function before() {
		if ($this->filesystem->exists($this->rootPath())) {
			throw new \Exception('the package ' . $this->name . ' is existed');
		}
	}

	protected function getStub() {
		return dirname(__DIR__, 1) . '/Stubs/package-stubs';
	}

	protected function replaceStub() {
		$this->replace('{{ namespace }}', $this->packageNamespace(), 'src/ServiceProvider.stub');
		$this->replace('{{ name }}', $this->name, 'composer.json');
		$this->replace('{{ escapedNamespace }}', $this->escapedPackageNamespace(), 'composer.json');
	}

	protected function after() {
		$this->addRepositoryToRootComposer();
		$this->addPackageToRootComposer();

		$this->composerUpdate();
	}

	/**
	 * Add a path repository for the tool to the application's composer.json file.
	 *
	 * @return void
	 */
	protected function addRepositoryToRootComposer() {
		$composer = json_decode(file_get_contents(BASE_PATH . '/composer.json'), true);

		$composer['repositories'][] = [
			'type' => 'path',
			'url' => './'.$this->savePath(),
		];

		file_put_contents(
			BASE_PATH . '/composer.json',
			json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);
	}

	/**
	 * Add a package entry for the tool to the application's composer.json file.
	 *
	 * @return void
	 */
	protected function addPackageToRootComposer() {
		$composer = json_decode(file_get_contents(BASE_PATH . '/composer.json'), true);

		$composer['require'][$this->name] = 'dev-master';

		file_put_contents(
			BASE_PATH . '/composer.json',
			json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);
	}

	/**
	 * Get the path to the tool.
	 *
	 * @return string
	 */
	protected function savePath() {
		return 'components/' . $this->packageClass();
	}

	/**
	 * Get the package's namespace.
	 *
	 * @return string
	 */
	protected function packageNamespace() {
		return StringHelper::studly($this->packageVendor()).'\\'.$this->packageClass();
	}

	/**
	 * Get the package's escaped namespace.
	 *
	 * @return string
	 */
	protected function escapedPackageNamespace() {
		return str_replace('\\', '\\\\', $this->packageNamespace());
	}

	/**
	 * Get the package's class name.
	 *
	 * @return string
	 */
	protected function packageClass() {
		return StringHelper::studly($this->packageName());
	}

	/**
	 * Get the package's vendor.
	 *
	 * @return string
	 */
	protected function packageVendor() {
		return explode('/', $this->name)[0];
	}

	/**
	 * Get the package's base name.
	 *
	 * @return string
	 */
	protected function packageName() {
		return explode('/', $this->name)[1];
	}

	protected function composerUpdate() {
		exec('composer update');
	}
}
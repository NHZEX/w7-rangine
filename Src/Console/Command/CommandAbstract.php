<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use W7\Console\Io\Output;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Core\Config\Env;

abstract class CommandAbstract extends Command {
	use AppCommonTrait;

	protected $description = '';
	/**
	 * @var InputInterface
	 */
	protected $input;
	/**
	 * @var Output
	 */
	protected $output;

	public function __construct(string $name = null) {
		parent::__construct($name);
		$this->setDescription($this->description);
	}

	/**
	 * Command arguments that override configurations, such as --config-app-setting-env=1, override the setting/env value in config/app
	 */
	private function overwriteConfigByOptions() {
		foreach ($this->input->getOptions() as $option => $value) {
			if (is_null($value) || is_array($value) || trim($value) === '') {
				continue;
			}
			if (strpos($option, 'config') !== false) {
				$option = explode('-', $option);
				array_shift($option);
				//The reason for counting >2 here is to ensure that there are at least configuration groups in the structure of the option
				if (count($option) >= 2) {
					$key = implode('.', $option);
					putenv($key . '=' . Env\Loader::parseValue($value));

					$this->getConfig()->set($key, ienv($key));
				}
			}
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->getApplication()->setDefaultCommand($this->getName());
		$this->input = $input;
		$this->output = $output;

		$this->overwriteConfigByOptions();

		$ret = $this->handle($this->input->getOptions());
		return isset($ret) ? (int)$ret : 0;
	}

	abstract protected function handle($options);

	public function option($key = null) {
		if (is_null($key)) {
			return $this->input->getOptions();
		}

		return $this->input->getOption($key);
	}

	protected function call($command, $arguments = []) {
		$arguments['command'] = $command;
		$input = new ArrayInput($arguments);
		return $this->getApplication()->find($command)->run(
			$input,
			new Output()
		);
	}
}

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

namespace W7\Console\Io;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class Output extends SymfonyStyle {
	const GAP_CHAR = '  ';
	const LEFT_CHAR = '  ';

	public function __construct() {
		parent::__construct(new ArgvInput(), new ConsoleOutput());
	}

	private function writeKey($key) {
		echo "\033[0;32m$key \e[0m";
	}

	public function table(array $headers, array $rows) {
		$table = new Table($this);
		$table->setHeaders($headers);
		$table->setRows($rows);
		$table->render();
	}

	public function writeList($list) {
		foreach ($list as $title => $items) {
			$title = "$title";
			$this->writeln($title);

			$this->writeItems((array)$items);
			$this->writeln('');
		}
	}

	public function info($content) {
		$this->write('<info>'.$content.'</info>');
		$this->newLine();
	}

	private function writeItems($items, $level = 1) {
		foreach ($items as $cmd => $desc) {
			if (\is_int($cmd)) {
				$cmd = '';
			}

			$maxLength = $this->getCmdMaxLength(array_keys($items));
			$cmd = \str_pad($cmd, $maxLength, ' ');
			$this->writeKey($this->writeLeft($level) . $cmd);

			if (is_array($desc)) {
				if (!empty($desc)) {
					$this->writeln('');
					$this->writeItems($desc, $level + 1);
					continue;
				} else {
					$desc = '[]';
				}
			}

			if ($desc === false) {
				$desc = 'false';
			}
			if ($desc === true) {
				$desc = 'true';
			}

			$this->writeln(self::GAP_CHAR . $desc);
		}
	}

	private function writeLeft($level) {
		$left = '';
		for ($i = 0; $i < $level; $i++) {
			$left .= self::LEFT_CHAR;
		}
		return $left;
	}

	private function getCmdMaxLength($commands) {
		$max = 0;

		foreach ($commands as $cmd) {
			$length = \strlen($cmd);
			if ($length > $max) {
				$max = $length;
				continue;
			}
		}

		return $max;
	}
}

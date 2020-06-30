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

namespace W7\Core\Cache\Provider;

use W7\Core\Cache\Cache;
use W7\Core\Cache\ConnectorManager;
use W7\Core\Facades\Event;
use W7\Core\Provider\ProviderAbstract;

class CacheProvider extends ProviderAbstract {
	public function register() {
		$connectionConfig = $this->config->get('app.cache', []);
		$poolConfig = $this->config->get('app.pool.cache', []);
		foreach ($connectionConfig as &$config) {
			$config['driver'] = $this->config->get('handler.cache.' . $config['driver'], $config['driver']);
		}

		$connectorManager = new ConnectorManager($connectionConfig, $poolConfig);
		$connectorManager->setEventDispatcher(Event::getFacadeRoot());
		Cache::setConnectionResolver($connectorManager);

		$channels = array_keys($connectionConfig);
		foreach ($channels as $key => $channel) {
			$this->container->set('cache-' . $channel, function () use ($channel) {
				return new Cache($channel);
			});
		}
	}
}

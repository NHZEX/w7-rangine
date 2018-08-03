<?php
/**
 * author: alex
 * date: 18-8-3 上午9:38
 */

namespace W7\Core\Base\Dispatcher;

use W7\Core\Base\Provider\TaskProvider;

/**
 * Class TaskDispatcher
 * @package W7\Core\Helper\Dispather
 */
class TaskDispatcher extends DispatcherFacade
{
    public function getFacadeAccessor()
    {
        return iloader()->singleton(TaskProvider::class);
    }
}

<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    // 现在 $context['APP_ENV'] 和 $context['APP_DEBUG'] 已经可以用了
    if ($context['APP_ENV'] === 'dev') {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

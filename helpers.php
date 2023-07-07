<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
if (! function_exists('app')) {
    /**
     * 获取容器实例.
     *
     * @return \Psr\Container\ContainerInterface
     */
    function app(): Psr\Container\ContainerInterface
    {
        return Hyperf\Context\ApplicationContext::getContainer();
    }
}

if (! function_exists('logger')) {
    /**
     * 日志组件.
     *
     * @param string $group 日志配置
     *
     * @return \Psr\Log\LoggerInterface
     */
    function logger(string $group = 'default'): Psr\Log\LoggerInterface
    {
        return \Hyperf\Support\make(\Hyperf\Logger\LoggerFactory::class)
            ->get('default', $group);
    }
}

if (! function_exists('stdoutLogger')) {
    function stdoutLogger()
    {
        return \Hyperf\Support\make(\Hyperf\Contract\StdoutLoggerInterface::class);
    }
}

if (! function_exists('cache')) {
    /**
     * 获取缓存驱动.
     */
    function cache()
    {
        return \Hyperf\Support\make(\Psr\SimpleCache\CacheInterface::class);
    }
}

if (! function_exists('event')) {
    /**
     * 触发事件.
     */
    function event(object $event)
    {
        \Hyperf\Support\make(\Psr\EventDispatcher\EventDispatcherInterface::class)->dispatch($event);
    }
}

if (! function_exists('real_ip')) {
    /**
     * 获取真实ip.
     */
    function real_ip(mixed $request = null): mixed
    {
        $request = $request ?? \Hyperf\Support\make(\Hyperf\HttpServer\Contract\RequestInterface::class);

        $ip = $request->getHeader('x-forwarded-for');

        if (empty($ip)) {
            $ip = $request->getHeader('x-real-ip');
        }

        if (empty($ip)) {
            $ip = $request->getServerParams()['remote_addr'] ?? '127.0.0.1';
        }

        if (is_array($ip)) {
            $ip = \Hyperf\Collection\Arr::first($ip);
        }

        return \Hyperf\Collection\Arr::first(explode(',', $ip));
    }
}

if (! function_exists('asyncQueue')) {
    /**
     * 投递队列.
     *
     * @param \Hyperf\AsyncQueue\Job $job 异步Job
     * @param int $delay 延迟时间-秒
     * @param string $driver 消息队列驱动
     */
    function asyncQueue(Hyperf\AsyncQueue\Job $job, int $delay = 0, string $driver = 'default')
    {
        \Hyperf\Support\make(\Hyperf\AsyncQueue\Driver\DriverFactory::class)->get($driver)->push($job, $delay);
    }
}

if (! function_exists('redirect')) {
    /**
     * 页面重定向.
     *
     * @param string $url 跳转URL
     * @param int $status HTTP状态码
     * @param string $schema 协议
     * @return \Psr\Http\Message\ResponseInterface
     */
    function redirect(string $url, int $status = 302, string $schema = 'http'): Psr\Http\Message\ResponseInterface
    {
        return \Hyperf\Support\make(\Hyperf\HttpServer\Contract\ResponseInterface::class)->redirect($url, $status, $schema);
    }
}

if (! function_exists('remember')) {
    /**
     * 数据缓存.
     *
     * @param string $key 缓存KEY
     * @param null|\DateInterval|int $ttl 缓存时间
     * @param \Closure $closure
     */
    function remember(string $key, null|int|DateInterval $ttl, Closure $closure): mixed
    {
        if (! empty($value = cache()->get($key))) {
            return $value;
        }

        $value = $closure();

        cache()->set($key, $value, $ttl);

        return $value;
    }
}

if (! function_exists('config_set')) {
    /**
     * 修改配置项.
     *
     * @param string $key identifier of the entry to set
     * @param mixed $value the value that save to container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function config_set(string $key, mixed $value): mixed
    {
        return app()->get(\Hyperf\Contract\ConfigInterface::class)->set($key, $value);
    }
}

if (! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @param mixed $condition 判断条件
     * @param string|\Throwable $exception 指定异常信息(RuntimeException)|抛出异常
     * @param mixed ...$parameters 异常自定义参数
     *
     * @throws \Throwable
     * @return mixed 返回条件数据
     */
    function throw_if(mixed $condition, Throwable|string $exception = 'RuntimeException', ...$parameters): mixed
    {
        if ($condition) {
            if (is_string($exception) && class_exists($exception)) {
                $exception = new $exception(...$parameters);
            }

            throw is_string($exception) ? new RuntimeException($exception) : $exception;
        }

        return $condition;
    }
}

if (! function_exists('throw_unless')) {
    /**
     * Throw the given exception unless the given condition is true.
     *
     * @param mixed $condition 判断条件
     * @param string|\Throwable $exception 指定异常信息(RuntimeException)|抛出异常
     * @param mixed ...$parameters 异常自定义参数
     *
     * @throws \Throwable
     * @return mixed 返回条件数据
     */
    function throw_unless(mixed $condition, Throwable|string $exception = 'RuntimeException', ...$parameters): mixed
    {
        throw_if(! $condition, $exception, ...$parameters);

        return $condition;
    }
}

if (! function_exists('redis')) {
    /**
     * redis用例.
     *
     * @param string $driver redis实例
     *
     * @return \Hyperf\Redis\RedisProxy
     */
    function redis(string $driver = 'default'): Hyperf\Redis\RedisProxy
    {
        return \Hyperf\Support\make(\Hyperf\Redis\RedisFactory::class)->get($driver);
    }
}

if (! function_exists('annotation_collector')) {
    /**
     * 获取指定annotation.
     *
     * @param string $class 查询类
     * @param string $method 查询方法
     * @param string $annotationTarget 指定注解类
     *
     * @throws \Hyperf\Di\Exception\AnnotationException
     * @return \Hyperf\Di\Annotation\AbstractAnnotation
     */
    function annotation_collector(string $class, string $method, string $annotationTarget): Hyperf\Di\Annotation\AbstractAnnotation
    {
        $methodAnnotation = \Hyperf\Di\Annotation\AnnotationCollector::getClassMethodAnnotation($class, $method)[$annotationTarget] ?? null;

        if ($methodAnnotation instanceof $annotationTarget) {
            return $methodAnnotation;
        }

        $classAnnotation = \Hyperf\Di\Annotation\AnnotationCollector::getClassAnnotations($class)[$annotationTarget] ?? null;
        if (! $classAnnotation instanceof $annotationTarget) {
            throw new \Hyperf\Di\Exception\AnnotationException("Annotation {$annotationTarget} couldn't be collected successfully.");
        }
        return $classAnnotation;
    }
}

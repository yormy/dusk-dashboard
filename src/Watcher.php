<?php

namespace BeyondCode\DuskDashboard;

use Closure;
use React\EventLoop\LoopInterface;
use Symfony\Component\Finder\Finder;
use Yosymfony\ResourceWatcher\ResourceCacheMemory;
use Yosymfony\ResourceWatcher\ResourceWatcher;
use Yosymfony\ResourceWatcher\Crc32ContentHash;

class Watcher
{
    /** @var \Symfony\Component\Finder\Finder */
    protected $finder;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public function __construct(Finder $finder, LoopInterface $loop)
    {
        $this->finder = $finder;
        $this->loop = $loop;
    }

    public function startWatching(Closure $callback)
    {
        $hashContent = new Crc32ContentHash();
        $watcher = new ResourceWatcher(new ResourceCacheMemory(), $this->finder, $hashContent);

        $this->loop->addPeriodicTimer(1 / 2, function () use ($watcher, $callback) {
            $watcher->findChanges();

            if ($watcher->hasChanges()) {
                call_user_func($callback);
            }
        });
    }
}

<?php

namespace IonBazan\ComposerDiff\Tests\Util;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Event\PostDiffEvent;

class PostDiffListener
{
    public static function onPostDiff(PostDiffEvent $event): void
    {
        $event->setProdEntries($event->getProdEntries()->matching(['symfony/event-dispatcher']));
        $event->setDevEntries(new DiffEntries([]));
        $event->setExitCode(32);
    }
}

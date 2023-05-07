<?php

namespace Feral\Inline\Process\Event\Listener;

use Feral\Inline\Process\DataCollector\Trace\ProcessTraceCollectorInterface;
use Feral\Core\Process\Event\ProcessEndEvent;
use Feral\Core\Process\Event\ProcessNodeAfterEvent;
use Feral\Core\Process\Event\ProcessNodeBeforeEvent;
use Feral\Core\Process\Event\ProcessStartEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProcessStartEvent::class, method: 'onStartEvent')]
#[AsEventListener(event: ProcessNodeBeforeEvent::class, method: 'onNodeBeforeEvent')]
#[AsEventListener(event: ProcessNodeAfterEvent::class, method: 'onNodeAfterEvent')]
#[AsEventListener(event: ProcessEndEvent::class, method: 'onEndEvent')]
class ProcessEventListener
{
    public function __construct(private ProcessTraceCollectorInterface $collector){}

    public function onStartEvent(ProcessStartEvent $event): void
    {
        $this->collector->start($event->getProcess(), $event->getContext());
    }

    public function onNodeBeforeEvent(ProcessNodeBeforeEvent $event): void
    {
        $this->collector->startNode($event->getNode(), $event->getContext());
    }

    public function onNodeAfterEvent(ProcessNodeAfterEvent $event): void
    {
        $this->collector->endNode($event->getResult(), $event->getContext());
    }

    public function onEndEvent(ProcessEndEvent $event): void
    {
        $this->collector->end($event->getContext());
    }
}
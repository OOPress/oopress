<?php

declare(strict_types=1);

namespace OOPress\Media\Event;

use OOPress\Media\MediaFile;
use OOPress\Event\Event;

/**
 * MediaDeleteEvent — Dispatched when media is deleted.
 * 
 * @api
 */
class MediaDeleteEvent extends Event
{
    public function __construct(
        private readonly MediaFile $media,
    ) {
        parent::__construct();
    }
    
    public function getMedia(): MediaFile
    {
        return $this->media;
    }
}
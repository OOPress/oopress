<?php

declare(strict_types=1);

namespace OOPress\Media\Event;

use OOPress\Media\MediaFile;
use OOPress\Event\Event;

/**
 * MediaUploadEvent — Dispatched when media is uploaded.
 * 
 * @api
 */
class MediaUploadEvent extends Event
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
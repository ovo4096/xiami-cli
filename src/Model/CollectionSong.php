<?php
namespace Xiami\Console\Model;

class CollectionSong extends Song
{
    public $introduction;

    public function merge($song)
    {
        parent::merge($song);

        if (empty($this->introduction) && !empty($song->introduction)) {
            $this->introduction = $song->introduction;
        }
    }
}

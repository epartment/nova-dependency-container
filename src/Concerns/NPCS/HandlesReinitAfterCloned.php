<?php

namespace Epartment\NovaDependencyContainer\Concerns\NPCS;

use Illuminate\Support\Facades\Log;

trait HandlesReinitAfterCloned {

    /**
     * Requires the component to be reinitialize after being cloned.
     */
    public function reinit_after_cloned() {
        $this->name = uniqid();
        // re-initialize after being cloned
        // Log::info('reinit_after_cloned being called on ['.static::class.'] @ ' . $this->name);
    }

}
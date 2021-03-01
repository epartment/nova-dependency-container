<?php

namespace Epartment\NovaDependencyContainer\Http\Controllers;

use Epartment\NovaDependencyContainer\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Controllers\ActionController as NovaActionController;

class ActionController extends NovaActionController
{
	/**
     * This uses the custom ActionRequest typehint to enable
     * dependency features.
	 *
	 * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
	 * @return \Illuminate\Http\Response
	 */
    public function store(ActionRequest $request)
    {
        return parent::store($request);
	}
}

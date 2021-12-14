<?php

namespace Epartment\NovaDependencyContainer\Http\Controllers;

use Epartment\NovaDependencyContainer\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\ActionRequest as NovaActionRequest;
use Laravel\Nova\Http\Controllers\ActionController as NovaActionController;

class ActionController extends NovaActionController
{
    /**
     * create custom request from base Nova ActionRequest
     *
     * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NovaActionRequest $request)
    {
        $request = ActionRequest::createFrom($request);

        return parent::store($request);
    }
}

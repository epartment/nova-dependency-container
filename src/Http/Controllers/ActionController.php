<?php

namespace Epartment\NovaDependencyContainer\Http\Controllers;

use Epartment\NovaDependencyContainer\Http\Requests\ActionRequest;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class ActionController extends Controller {

	/**
	 * List the actions for the given resource.
	 *
	 * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(NovaRequest $request) {
		return response()->json([
			'actions' => $request->newResource()->availableActions($request),
			'pivotActions' => [
				'name' => $request->pivotName(),
				'actions' => $request->newResource()->availablePivotActions($request),
			],
		]);
	}

	/**
	 * Perform an action on the specified resources.
	 *
	 * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(ActionRequest $request) {

		$request->validateFields();

		return $request->action()->handleRequest($request);
	}
}

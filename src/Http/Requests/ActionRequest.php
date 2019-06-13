<?php

namespace Dtannen\NovaDependencyContainer\Http\Requests;

use Dtannen\NovaDependencyContainer\HasDependencies;
use Laravel\Nova\Http\Requests\ActionRequest as NovaActionRequest;

class ActionRequest extends NovaActionRequest {

	use HasDependencies;
}

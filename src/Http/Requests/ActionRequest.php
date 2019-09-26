<?php

namespace Epartment\NovaDependencyContainer\Http\Requests;

use Epartment\NovaDependencyContainer\HasDependencies;
use Laravel\Nova\Http\Requests\ActionRequest as NovaActionRequest;

class ActionRequest extends NovaActionRequest {

	use HasDependencies;
}

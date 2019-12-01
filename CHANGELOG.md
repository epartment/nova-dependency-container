## Nova Dependency Container

<br />

### Releases
 - [v1.2.10](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.10)
 	- Fixes a problem with [Whitecubes Flexible Content](https://github.com/whitecube/nova-flexible-content) package where the dependency container would do nothing #93 [whitecube/nova-flexible-content#87](https://github.com/whitecube/nova-flexible-content/issues/87)
 - [v1.2.9](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.9)
 	- Support BelongsToMany field when depending on the seleced resource #52
 - [v1.2.8](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.8)
   - Ensure that each field resolves on it's own depending on the situation - PR #90
   - Fixes #89, #88, #87, #85 and #40 
 - [v1.2.7](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.7)
   - Fix an index error in Nova 2.5.0 where the rules-set array requires a key fields attribute #86
   - Support MorphTo controller #62,#85
   - Moved releases/changes to `CHANGELOG.md`
 - [v1.2.6](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.6)
   - Fixes a problem where it should check if a model exist when creating a resource when it's impossible to satisfy dependencies
 - [v1.2.5](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.5)
   - Fixes a problem where Nova can't resolve custom ActionRequest [#82](https://github.com/epartment/nova-dependency-container/issues/82)
 - [v1.2.4](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.4)
   - Fixes a problem where fields with same names use previous validation rules when used in multiple containers [#81](https://github.com/epartment/nova-dependency-container/issues/81), [#60](https://github.com/epartment/nova-dependency-container/pull/60) (@melewski)
 - [v1.2.3](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.3)
   - Fixed belongs-to/morph-to as nested fields [#80](https://github.com/epartment/nova-dependency-container/issues/80)
   - Added missing methods in validation rules
 - [v1.2.2](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.2)
   - fixed fields not resolving when using `displayUsingLabels`, `resolveUsing` or `displayUsing`. (@dbf)
   - fixed action fields who do not return a collection (@bsormagec)
   - added two new methods `onEmpty` and `onNullOrZero`. (@niektenhoopen, @dbf)
   - changed the logic for dependency satisfaction where it is reversed
   - changed `field` in two separate names; `field` and `property`, to avoid confusion when being used on a resource or component.
 - [v1.2.1](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.1)
   - fixed support for [BelongsTo](https://nova.laravel.com/docs/1.0/resources/relationships.html#belongsto) and [MorphTo](https://nova.laravel.com/docs/1.0/resources/relationships.html#morphto) fields (@mikaelpopowicz, @dbf)
 - [v1.2.0](https://github.com/epartment/nova-dependency-container/releases/tag/1.2.0) 
   - working version for Laravel 5.8 | 6 and Nova 2.x. (@FastPointGaming, @spaceo, @cdbeaton, @yaroslawww)

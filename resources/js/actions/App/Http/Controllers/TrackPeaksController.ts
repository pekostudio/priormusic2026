import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
const TrackPeaksController = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TrackPeaksController.url(args, options),
    method: 'get',
})

TrackPeaksController.definition = {
    methods: ["get","head"],
    url: '/tracks/{albumTrack}/peaks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
TrackPeaksController.url = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { albumTrack: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { albumTrack: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            albumTrack: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        albumTrack: typeof args.albumTrack === 'object'
        ? args.albumTrack.id
        : args.albumTrack,
    }

    return TrackPeaksController.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
TrackPeaksController.get = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TrackPeaksController.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
TrackPeaksController.head = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: TrackPeaksController.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
const TrackPeaksControllerForm = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TrackPeaksController.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
TrackPeaksControllerForm.get = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TrackPeaksController.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
TrackPeaksControllerForm.head = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TrackPeaksController.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

TrackPeaksController.form = TrackPeaksControllerForm

export default TrackPeaksController
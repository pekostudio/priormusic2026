import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
const TrackDownloadController = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TrackDownloadController.url(args, options),
    method: 'get',
})

TrackDownloadController.definition = {
    methods: ["get","head"],
    url: '/tracks/{albumTrack}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
TrackDownloadController.url = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return TrackDownloadController.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
TrackDownloadController.get = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TrackDownloadController.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
TrackDownloadController.head = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: TrackDownloadController.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
const TrackDownloadControllerForm = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TrackDownloadController.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
TrackDownloadControllerForm.get = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TrackDownloadController.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
TrackDownloadControllerForm.head = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TrackDownloadController.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

TrackDownloadController.form = TrackDownloadControllerForm

export default TrackDownloadController
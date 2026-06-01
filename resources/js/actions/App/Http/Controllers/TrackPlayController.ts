import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TrackPlayController::__invoke
* @see app/Http/Controllers/TrackPlayController.php:15
* @route '/tracks/{albumTrack}/plays'
*/
const TrackPlayController = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: TrackPlayController.url(args, options),
    method: 'post',
})

TrackPlayController.definition = {
    methods: ["post"],
    url: '/tracks/{albumTrack}/plays',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TrackPlayController::__invoke
* @see app/Http/Controllers/TrackPlayController.php:15
* @route '/tracks/{albumTrack}/plays'
*/
TrackPlayController.url = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return TrackPlayController.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TrackPlayController::__invoke
* @see app/Http/Controllers/TrackPlayController.php:15
* @route '/tracks/{albumTrack}/plays'
*/
TrackPlayController.post = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: TrackPlayController.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TrackPlayController::__invoke
* @see app/Http/Controllers/TrackPlayController.php:15
* @route '/tracks/{albumTrack}/plays'
*/
const TrackPlayControllerForm = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: TrackPlayController.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\TrackPlayController::__invoke
* @see app/Http/Controllers/TrackPlayController.php:15
* @route '/tracks/{albumTrack}/plays'
*/
TrackPlayControllerForm.post = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: TrackPlayController.url(args, options),
    method: 'post',
})

TrackPlayController.form = TrackPlayControllerForm

export default TrackPlayController
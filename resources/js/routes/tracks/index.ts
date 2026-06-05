import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import plays from './plays'
import favorite from './favorite'
import playlists from './playlists'
/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
export const download = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/tracks/{albumTrack}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
download.url = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return download.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
download.get = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
download.head = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
const downloadForm = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
downloadForm.get = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackDownloadController::__invoke
* @see app/Http/Controllers/TrackDownloadController.php:15
* @route '/tracks/{albumTrack}/download'
*/
downloadForm.head = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

download.form = downloadForm

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
export const peaks = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: peaks.url(args, options),
    method: 'get',
})

peaks.definition = {
    methods: ["get","head"],
    url: '/tracks/{albumTrack}/peaks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
peaks.url = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return peaks.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
peaks.get = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: peaks.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
peaks.head = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: peaks.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
const peaksForm = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: peaks.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
peaksForm.get = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: peaks.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\TrackPeaksController::__invoke
* @see app/Http/Controllers/TrackPeaksController.php:11
* @route '/tracks/{albumTrack}/peaks'
*/
peaksForm.head = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: peaks.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

peaks.form = peaksForm

const tracks = {
    download: Object.assign(download, download),
    peaks: Object.assign(peaks, peaks),
    plays: Object.assign(plays, plays),
    favorite: Object.assign(favorite, favorite),
    playlists: Object.assign(playlists, playlists),
}

export default tracks
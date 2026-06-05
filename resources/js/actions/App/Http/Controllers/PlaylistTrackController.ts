import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PlaylistTrackController::destroy
* @see app/Http/Controllers/PlaylistTrackController.php:24
* @route '/playlists/{playlist}/tracks/{albumTrack}'
*/
export const destroy = (args: { playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } } | [playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/playlists/{playlist}/tracks/{albumTrack}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PlaylistTrackController::destroy
* @see app/Http/Controllers/PlaylistTrackController.php:24
* @route '/playlists/{playlist}/tracks/{albumTrack}'
*/
destroy.url = (args: { playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } } | [playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            playlist: args[0],
            albumTrack: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        playlist: typeof args.playlist === 'object'
        ? args.playlist.id
        : args.playlist,
        albumTrack: typeof args.albumTrack === 'object'
        ? args.albumTrack.id
        : args.albumTrack,
    }

    return destroy.definition.url
            .replace('{playlist}', parsedArgs.playlist.toString())
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlaylistTrackController::destroy
* @see app/Http/Controllers/PlaylistTrackController.php:24
* @route '/playlists/{playlist}/tracks/{albumTrack}'
*/
destroy.delete = (args: { playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } } | [playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PlaylistTrackController::destroy
* @see app/Http/Controllers/PlaylistTrackController.php:24
* @route '/playlists/{playlist}/tracks/{albumTrack}'
*/
const destroyForm = (args: { playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } } | [playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistTrackController::destroy
* @see app/Http/Controllers/PlaylistTrackController.php:24
* @route '/playlists/{playlist}/tracks/{albumTrack}'
*/
destroyForm.delete = (args: { playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } } | [playlist: string | number | { id: string | number }, albumTrack: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
export const store = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/tracks/{albumTrack}/playlists',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
store.url = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{albumTrack}', parsedArgs.albumTrack.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
store.post = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
const storeForm = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
storeForm.post = (args: { albumTrack: string | number | { id: string | number } } | [albumTrack: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

const PlaylistTrackController = { destroy, store }

export default PlaylistTrackController
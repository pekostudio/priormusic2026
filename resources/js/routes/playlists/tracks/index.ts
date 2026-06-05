import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
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

const tracks = {
    destroy: Object.assign(destroy, destroy),
}

export default tracks
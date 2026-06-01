import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
export const store = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
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
store.url = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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
store.post = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
const storeForm = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistTrackController::store
* @see app/Http/Controllers/PlaylistTrackController.php:12
* @route '/tracks/{albumTrack}/playlists'
*/
storeForm.post = (args: { albumTrack: number | { id: number } } | [albumTrack: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

const playlists = {
    store: Object.assign(store, store),
}

export default playlists
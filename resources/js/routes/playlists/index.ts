import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import tracks from './tracks'
/**
* @see \App\Http\Controllers\PlaylistController::index
* @see app/Http/Controllers/PlaylistController.php:15
* @route '/playlists'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/playlists',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlaylistController::index
* @see app/Http/Controllers/PlaylistController.php:15
* @route '/playlists'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlaylistController::index
* @see app/Http/Controllers/PlaylistController.php:15
* @route '/playlists'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PlaylistController::index
* @see app/Http/Controllers/PlaylistController.php:15
* @route '/playlists'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PlaylistController::index
* @see app/Http/Controllers/PlaylistController.php:15
* @route '/playlists'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PlaylistController::index
* @see app/Http/Controllers/PlaylistController.php:15
* @route '/playlists'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PlaylistController::index
* @see app/Http/Controllers/PlaylistController.php:15
* @route '/playlists'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\PlaylistController::store
* @see app/Http/Controllers/PlaylistController.php:36
* @route '/playlists'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/playlists',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PlaylistController::store
* @see app/Http/Controllers/PlaylistController.php:36
* @route '/playlists'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlaylistController::store
* @see app/Http/Controllers/PlaylistController.php:36
* @route '/playlists'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistController::store
* @see app/Http/Controllers/PlaylistController.php:36
* @route '/playlists'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistController::store
* @see app/Http/Controllers/PlaylistController.php:36
* @route '/playlists'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\PlaylistController::show
* @see app/Http/Controllers/PlaylistController.php:52
* @route '/playlists/{playlist}'
*/
export const show = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/playlists/{playlist}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlaylistController::show
* @see app/Http/Controllers/PlaylistController.php:52
* @route '/playlists/{playlist}'
*/
show.url = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { playlist: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { playlist: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            playlist: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        playlist: typeof args.playlist === 'object'
        ? args.playlist.id
        : args.playlist,
    }

    return show.definition.url
            .replace('{playlist}', parsedArgs.playlist.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlaylistController::show
* @see app/Http/Controllers/PlaylistController.php:52
* @route '/playlists/{playlist}'
*/
show.get = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PlaylistController::show
* @see app/Http/Controllers/PlaylistController.php:52
* @route '/playlists/{playlist}'
*/
show.head = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PlaylistController::show
* @see app/Http/Controllers/PlaylistController.php:52
* @route '/playlists/{playlist}'
*/
const showForm = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PlaylistController::show
* @see app/Http/Controllers/PlaylistController.php:52
* @route '/playlists/{playlist}'
*/
showForm.get = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PlaylistController::show
* @see app/Http/Controllers/PlaylistController.php:52
* @route '/playlists/{playlist}'
*/
showForm.head = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\PlaylistController::destroy
* @see app/Http/Controllers/PlaylistController.php:81
* @route '/playlists/{playlist}'
*/
export const destroy = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/playlists/{playlist}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PlaylistController::destroy
* @see app/Http/Controllers/PlaylistController.php:81
* @route '/playlists/{playlist}'
*/
destroy.url = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { playlist: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { playlist: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            playlist: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        playlist: typeof args.playlist === 'object'
        ? args.playlist.id
        : args.playlist,
    }

    return destroy.definition.url
            .replace('{playlist}', parsedArgs.playlist.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlaylistController::destroy
* @see app/Http/Controllers/PlaylistController.php:81
* @route '/playlists/{playlist}'
*/
destroy.delete = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PlaylistController::destroy
* @see app/Http/Controllers/PlaylistController.php:81
* @route '/playlists/{playlist}'
*/
const destroyForm = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PlaylistController::destroy
* @see app/Http/Controllers/PlaylistController.php:81
* @route '/playlists/{playlist}'
*/
destroyForm.delete = (args: { playlist: string | number | { id: string | number } } | [playlist: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const playlists = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    destroy: Object.assign(destroy, destroy),
    tracks: Object.assign(tracks, tracks),
}

export default playlists
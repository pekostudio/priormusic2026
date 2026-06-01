import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\ReportController::index
* @see app/Http/Controllers/Settings/ReportController.php:20
* @route '/settings/reports'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/settings/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\ReportController::index
* @see app/Http/Controllers/Settings/ReportController.php:20
* @route '/settings/reports'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\ReportController::index
* @see app/Http/Controllers/Settings/ReportController.php:20
* @route '/settings/reports'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::index
* @see app/Http/Controllers/Settings/ReportController.php:20
* @route '/settings/reports'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::index
* @see app/Http/Controllers/Settings/ReportController.php:20
* @route '/settings/reports'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::index
* @see app/Http/Controllers/Settings/ReportController.php:20
* @route '/settings/reports'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::index
* @see app/Http/Controllers/Settings/ReportController.php:20
* @route '/settings/reports'
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
* @see \App\Http\Controllers\Settings\ReportController::store
* @see app/Http/Controllers/Settings/ReportController.php:40
* @route '/settings/reports'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/settings/reports',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Settings\ReportController::store
* @see app/Http/Controllers/Settings/ReportController.php:40
* @route '/settings/reports'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\ReportController::store
* @see app/Http/Controllers/Settings/ReportController.php:40
* @route '/settings/reports'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::store
* @see app/Http/Controllers/Settings/ReportController.php:40
* @route '/settings/reports'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::store
* @see app/Http/Controllers/Settings/ReportController.php:40
* @route '/settings/reports'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Settings\ReportController::download
* @see app/Http/Controllers/Settings/ReportController.php:72
* @route '/settings/reports/{musicUsageReport}/download'
*/
export const download = (args: { musicUsageReport: number | { id: number } } | [musicUsageReport: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/settings/reports/{musicUsageReport}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\ReportController::download
* @see app/Http/Controllers/Settings/ReportController.php:72
* @route '/settings/reports/{musicUsageReport}/download'
*/
download.url = (args: { musicUsageReport: number | { id: number } } | [musicUsageReport: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { musicUsageReport: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { musicUsageReport: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            musicUsageReport: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        musicUsageReport: typeof args.musicUsageReport === 'object'
        ? args.musicUsageReport.id
        : args.musicUsageReport,
    }

    return download.definition.url
            .replace('{musicUsageReport}', parsedArgs.musicUsageReport.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\ReportController::download
* @see app/Http/Controllers/Settings/ReportController.php:72
* @route '/settings/reports/{musicUsageReport}/download'
*/
download.get = (args: { musicUsageReport: number | { id: number } } | [musicUsageReport: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::download
* @see app/Http/Controllers/Settings/ReportController.php:72
* @route '/settings/reports/{musicUsageReport}/download'
*/
download.head = (args: { musicUsageReport: number | { id: number } } | [musicUsageReport: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::download
* @see app/Http/Controllers/Settings/ReportController.php:72
* @route '/settings/reports/{musicUsageReport}/download'
*/
const downloadForm = (args: { musicUsageReport: number | { id: number } } | [musicUsageReport: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::download
* @see app/Http/Controllers/Settings/ReportController.php:72
* @route '/settings/reports/{musicUsageReport}/download'
*/
downloadForm.get = (args: { musicUsageReport: number | { id: number } } | [musicUsageReport: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\ReportController::download
* @see app/Http/Controllers/Settings/ReportController.php:72
* @route '/settings/reports/{musicUsageReport}/download'
*/
downloadForm.head = (args: { musicUsageReport: number | { id: number } } | [musicUsageReport: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

download.form = downloadForm

const ReportController = { index, store, download }

export default ReportController
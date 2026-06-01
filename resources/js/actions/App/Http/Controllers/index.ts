import AlbumController from './AlbumController'
import FavoriteController from './FavoriteController'
import PlaylistController from './PlaylistController'
import PlaylistTrackController from './PlaylistTrackController'
import TrackController from './TrackController'
import TrackDownloadController from './TrackDownloadController'
import TrackPeaksController from './TrackPeaksController'
import TrackPlayController from './TrackPlayController'
import FavoriteTrackController from './FavoriteTrackController'
import Settings from './Settings'

const Controllers = {
    AlbumController: Object.assign(AlbumController, AlbumController),
    FavoriteController: Object.assign(FavoriteController, FavoriteController),
    PlaylistController: Object.assign(PlaylistController, PlaylistController),
    PlaylistTrackController: Object.assign(PlaylistTrackController, PlaylistTrackController),
    TrackController: Object.assign(TrackController, TrackController),
    TrackDownloadController: Object.assign(TrackDownloadController, TrackDownloadController),
    TrackPeaksController: Object.assign(TrackPeaksController, TrackPeaksController),
    TrackPlayController: Object.assign(TrackPlayController, TrackPlayController),
    FavoriteTrackController: Object.assign(FavoriteTrackController, FavoriteTrackController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers
export type PlaylistSummary = {
    id: number;
    name: string;
    tracks_count?: number;
    show_url?: string;
    delete_url?: string;
};

export type AlbumTrackPayload = {
    id: number;
    title: string;
    name: string;
    artist: string | null;
    version: string | null;
    genre: string | null;
    time: string | null;
    duration_seconds: number | null;
    bpm: number | null;
    keywords: string[];
    cover_url: string | null;
    album: {
        id: number;
        title: string;
        code: string;
        library: string | null;
    } | null;
    audio_url: string | null;
    download_url: string;
    peaks_url: string;
    play_url: string;
    favorite_url: string;
    unfavorite_url: string;
    is_favorite: boolean;
    playlist_url: string;
    playlist_ids: number[];
};

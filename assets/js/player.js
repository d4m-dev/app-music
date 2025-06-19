class MusicPlayer {
    constructor(config) {
        this.audio = new Audio();
        this.playlist = config.playlist || [];
        this.currentTrackIndex = 0;
        this.isPlaying = false;
        this.volume = 0.7;
        this.initElements();
        this.initEvents();
        this.initWaveform();
        this.loadTrack(config.currentTrack || this.playlist[0]);
    }

    initElements() {
        this.elements = {
            playPauseBtn: document.getElementById('playPauseBtn'),
            prevBtn: document.getElementById('prevBtn'),
            nextBtn: document.getElementById('nextBtn'),
            seekSlider: document.getElementById('seekSlider'),
            volumeSlider: document.getElementById('volumeSlider'),
            currentTime: document.getElementById('currentTime'),
            duration: document.getElementById('duration'),
            trackName: document.getElementById('trackName'),
            trackArtist: document.getElementById('trackArtist'),
            artworkImage: document.getElementById('artworkImage'),
            playlistContent: document.getElementById('playlistContent'),
            lyricsContent: document.getElementById('lyricsContent'),
            waveform: document.getElementById('waveform')
        };
    }

    initEvents() {
        // Player controls
        this.elements.playPauseBtn.addEventListener('click', () => this.togglePlay());
        this.elements.prevBtn.addEventListener('click', () => this.prevTrack());
        this.elements.nextBtn.addEventListener('click', () => this.nextTrack());
        
        // Seek slider
        this.elements.seekSlider.addEventListener('input', () => {
            const seekTo = this.audio.duration * (this.elements.seekSlider.value / 100);
            this.audio.currentTime = seekTo;
        });
        
        // Volume slider
        this.elements.volumeSlider.addEventListener('input', () => {
            this.volume = this.elements.volumeSlider.value / 100;
            this.audio.volume = this.volume;
            this.updateVolumeIcons();
        });
        
        // Audio events
        this.audio.addEventListener('timeupdate', () => this.updateTime());
        this.audio.addEventListener('ended', () => this.nextTrack());
        this.audio.addEventListener('volumechange', () => this.updateVolumeIcons());
        
        // Playlist items
        document.querySelectorAll('.playlist-item').forEach(item => {
            item.addEventListener('click', () => {
                const trackId = item.dataset.trackId;
                this.loadTrack(this.playlist.find(track => track.id == trackId));
                this.play();
            });
        });
        
        // Lyrics toggle
        document.getElementById('showLyricsBtn').addEventListener('click', () => {
            document.getElementById('lyricsContainer').classList.add('show');
        });
        
        document.getElementById('hideLyricsBtn').addEventListener('click', () => {
            document.getElementById('lyricsContainer').classList.remove('show');
        });
        
        // Playlist toggle
        document.getElementById('showPlaylistBtn').addEventListener('click', () => {
            document.getElementById('playlistContainer').classList.add('show');
        });
        
        document.getElementById('hidePlaylistBtn').addEventListener('click', () => {
            document.getElementById('playlistContainer').classList.remove('show');
        });
    }

    loadTrack(track) {
        if (!track) return;
        
        this.currentTrack = track;
        this.audio.src = track.audio_url;
        
        // Update UI
        this.elements.trackName.textContent = track.name;
        this.elements.trackArtist.textContent = track.artist;
        this.elements.artworkImage.src = track.artwork_url;
        this.elements.artworkImage.alt = track.name;
        
        // Highlight current track in playlist
        document.querySelectorAll('.playlist-item').forEach(item => {
            item.classList.toggle('active', item.dataset.trackId == track.id);
        });
        
        // Load lyrics
        this.loadLyrics(track.id);
        
        // Analytics
        this.trackPlayEvent(track.id);
    }

    async loadLyrics(trackId) {
        try {
            const response = await fetch(`${window.App.config.baseUrl}/api/lyrics?track_id=${trackId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                this.elements.lyricsContent.innerHTML = 
                    `<div class="lyrics-text">${data.lyrics.replace(/\n/g, '<br>')}</div>`;
            } else {
                this.elements.lyricsContent.innerHTML = 
                    '<div class="no-lyrics">Không có lời bài hát cho bài này</div>';
            }
        } catch (error) {
            console.error('Error loading lyrics:', error);
        }
    }

    togglePlay() {
        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }

    play() {
        this.audio.play()
            .then(() => {
                this.isPlaying = true;
                this.elements.playPauseBtn.querySelector('.play-icon').style.display = 'none';
                this.elements.playPauseBtn.querySelector('.pause-icon').style.display = 'block';
                this.updateWaveform();
            })
            .catch(error => {
                console.error('Playback failed:', error);
            });
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this.elements.playPauseBtn.querySelector('.play-icon').style.display = 'block';
        this.elements.playPauseBtn.querySelector('.pause-icon').style.display = 'none';
    }

    prevTrack() {
        this.currentTrackIndex = 
            (this.currentTrackIndex - 1 + this.playlist.length) % this.playlist.length;
        this.loadTrack(this.playlist[this.currentTrackIndex]);
        if (this.isPlaying) this.play();
    }

    nextTrack() {
        this.currentTrackIndex = (this.currentTrackIndex + 1) % this.playlist.length;
        this.loadTrack(this.playlist[this.currentTrackIndex]);
        if (this.isPlaying) this.play();
    }

    updateTime() {
        const currentTime = this.audio.currentTime;
        const duration = this.audio.duration || 1;
        
        // Update sliders
        this.elements.seekSlider.value = (currentTime / duration) * 100;
        
        // Update time displays
        this.elements.currentTime.textContent = this.formatTime(currentTime);
        this.elements.duration.textContent = this.formatTime(duration);
    }

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }

    updateVolumeIcons() {
        const volume = this.audio.volume;
        const muteIcon = document.getElementById('volumeMuteIcon');
        const upIcon = document.getElementById('volumeUpIcon');
        
        if (volume === 0) {
            muteIcon.style.display = 'block';
            upIcon.style.display = 'none';
        } else {
            muteIcon.style.display = 'none';
            upIcon.style.display = 'block';
        }
    }

    initWaveform() {
        // Sử dụng Wavesurfer.js hoặc thư viện waveform khác
        this.wavesurfer = WaveSurfer.create({
            container: this.elements.waveform,
            waveColor: '#4a4a4a',
            progressColor: '#2d5d7b',
            cursorColor: '#ffffff',
            barWidth: 2,
            barRadius: 3,
            cursorWidth: 1,
            height: 60,
            barGap: 2,
            responsive: true
        });
        
        this.wavesurfer.on('ready', () => {
            if (this.isPlaying) {
                this.wavesurfer.play();
            }
        });
        
        this.wavesurfer.on('click', (position) => {
            this.audio.currentTime = position * this.audio.duration;
        });
    }

    updateWaveform() {
        if (this.wavesurfer) {
            this.wavesurfer.load(this.audio.src);
        }
    }

    trackPlayEvent(trackId) {
        // Gửi sự kiện analytics
        if (window.gtag) {
            gtag('event', 'play', {
                'event_category': 'Audio',
                'event_label': trackId
            });
        }
        
        // Hoặc gửi đến server của bạn
        fetch(`${window.App.config.baseUrl}/api/track/play`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.config.csrfToken
            },
            body: JSON.stringify({ track_id: trackId })
        }).catch(error => console.error('Error tracking play:', error));
    }
}

// Khởi tạo player khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    window.player = new MusicPlayer(window.App.config);
    
    // Ẩn loading indicator
    document.getElementById('loading').style.display = 'none';
});

import { useEffect, useRef, useState } from 'react';

const WAVEFORM_BARS = [
  6, 18, 12, 24, 14, 20, 10, 26, 16, 22, 12, 18,
  28, 14, 22, 10, 20, 16, 26, 12, 18, 24, 14, 22,
  10, 20, 16, 26, 12, 18, 22, 14, 20, 10, 16, 12,
  24, 14, 20, 10, 26, 16, 22, 12, 18, 28, 14, 22
];

const PLAYBACK_RATES = [1, 1.25, 1.5, 0.75];
const AUDIO_RELEASE_DELAY_MS = 300;

const audioStateCache = new Map();

let sharedAudio = null;
let sharedAudioUrl = '';
let mountedPlayerCount = 0;
let releaseAudioTimer = null;

const formatAudioTime = (seconds = 0) => {
  if (!Number.isFinite(seconds)) return '00:00';

  const safeSeconds = Math.max(0, Math.floor(seconds));
  const minutes = Math.floor(safeSeconds / 60).toString().padStart(2, '0');
  const remainingSeconds = (safeSeconds % 60).toString().padStart(2, '0');

  return `${minutes}:${remainingSeconds}`;
};

const getPlayableDuration = (audio) => {
  if (Number.isFinite(audio.duration) && audio.duration > 0) {
    return audio.duration;
  }

  if (audio.seekable?.length > 0) {
    return audio.seekable.end(audio.seekable.length - 1);
  }

  return 0;
};

const createAudioState = (overrides = {}) => ({
  isPlaying: false,
  isMuted: false,
  currentTime: 0,
  duration: 0,
  playbackRate: 1,
  ...overrides
});

const getSharedAudio = () => {
  if (typeof Audio === 'undefined') return null;

  if (!sharedAudio) {
    sharedAudio = new Audio();
    sharedAudio.preload = 'metadata';
  }

  return sharedAudio;
};

const getRuntimeAudioState = (audio) => createAudioState({
  isPlaying: Boolean(audio && !audio.paused && !audio.ended),
  isMuted: Boolean(audio?.muted),
  currentTime: audio?.currentTime || 0,
  duration: audio ? getPlayableDuration(audio) : 0,
  playbackRate: audio?.playbackRate || 1
});

const getCachedAudioState = (audioUrl, fallbackState = {}) => {
  if (!audioUrl) return createAudioState(fallbackState);

  return createAudioState(
    audioStateCache.has(audioUrl)
      ? audioStateCache.get(audioUrl)
      : fallbackState
  );
};

const saveSharedAudioState = (overrides = {}) => {
  if (!sharedAudio || !sharedAudioUrl) return;

  audioStateCache.set(sharedAudioUrl, {
    ...getRuntimeAudioState(sharedAudio),
    ...overrides
  });
};

const restoreAudioTime = (audio, targetTime = 0) => {
  if (!audio || !Number.isFinite(targetTime) || targetTime <= 0) return;

  const playableDuration = getPlayableDuration(audio);
  const nextTime = playableDuration ? Math.min(targetTime, playableDuration) : targetTime;

  try {
    audio.currentTime = nextTime;
  } catch {
    // Some browsers only allow setting currentTime after metadata is loaded.
  }
};

const activateSharedAudio = (audioUrl) => {
  const audio = getSharedAudio();

  if (!audio) return null;

  if (!audioUrl) {
    saveSharedAudioState({ isPlaying: false });
    audio.pause();
    sharedAudioUrl = '';
    return null;
  }

  if (sharedAudioUrl === audioUrl) {
    return audio;
  }

  const shouldKeepPlaying = sharedAudioUrl
    ? !audio.paused && !audio.ended
    : false;

  saveSharedAudioState();

  const nextState = getCachedAudioState(audioUrl, {
    isPlaying: shouldKeepPlaying,
    isMuted: audio.muted,
    playbackRate: audio.playbackRate || 1
  });

  sharedAudioUrl = audioUrl;
  audio.src = audioUrl;
  audio.preload = 'metadata';
  audio.muted = nextState.isMuted;
  audio.playbackRate = nextState.playbackRate;
  audio.load();
  restoreAudioTime(audio, nextState.currentTime);

  if (shouldKeepPlaying || nextState.isPlaying) {
    audio.play().catch((error) => {
      console.error('Unable to continue audio playback:', error);
    });
  }

  return audio;
};

export default function ListeningAudioPlayer({ audioUrl }) {
  const audioRef = useRef(null);
  const [isPlaying, setIsPlaying] = useState(false);
  const [isMuted, setIsMuted] = useState(false);
  const [currentTime, setCurrentTime] = useState(0);
  const [duration, setDuration] = useState(0);
  const [playbackRate, setPlaybackRate] = useState(1);
  const isAudioAvailable = Boolean(audioUrl);

  useEffect(() => {
    mountedPlayerCount += 1;

    if (releaseAudioTimer) {
      clearTimeout(releaseAudioTimer);
      releaseAudioTimer = null;
    }

    return () => {
      mountedPlayerCount = Math.max(0, mountedPlayerCount - 1);
      saveSharedAudioState();

      releaseAudioTimer = window.setTimeout(() => {
        if (mountedPlayerCount > 0 || !sharedAudio) return;

        saveSharedAudioState({ isPlaying: false });
        sharedAudio.pause();
      }, AUDIO_RELEASE_DELAY_MS);
    };
  }, []);

  useEffect(() => {
    const applyAudioState = (state) => {
      setCurrentTime(state.currentTime);
      setDuration(state.duration);
      setIsPlaying(state.isPlaying);
      setIsMuted(state.isMuted);
      setPlaybackRate(state.playbackRate);
    };

    if (!audioUrl) {
      activateSharedAudio(null);
      audioRef.current = null;
      applyAudioState(createAudioState());
      return;
    }

    const audio = activateSharedAudio(audioUrl);

    if (!audio) return;

    audioRef.current = audio;

    const updateCurrentTime = () => {
      const nextState = getRuntimeAudioState(audio);
      audioStateCache.set(audioUrl, nextState);
      applyAudioState(nextState);
    };
    const updateDuration = () => {
      const nextState = getRuntimeAudioState(audio);
      audioStateCache.set(audioUrl, nextState);
      applyAudioState(nextState);
    };
    const restoreAndUpdateDuration = () => {
      restoreAudioTime(audio, getCachedAudioState(audioUrl).currentTime);
      updateDuration();
    };

    applyAudioState(getRuntimeAudioState(audio));

    audio.addEventListener('timeupdate', updateCurrentTime);
    audio.addEventListener('loadedmetadata', restoreAndUpdateDuration);
    audio.addEventListener('durationchange', updateDuration);
    audio.addEventListener('progress', updateDuration);
    audio.addEventListener('canplay', updateDuration);
    audio.addEventListener('play', updateDuration);
    audio.addEventListener('pause', updateDuration);
    audio.addEventListener('ended', updateDuration);

    return () => {
      audioStateCache.set(audioUrl, getRuntimeAudioState(audio));
      audio.removeEventListener('timeupdate', updateCurrentTime);
      audio.removeEventListener('loadedmetadata', restoreAndUpdateDuration);
      audio.removeEventListener('durationchange', updateDuration);
      audio.removeEventListener('progress', updateDuration);
      audio.removeEventListener('canplay', updateDuration);
      audio.removeEventListener('play', updateDuration);
      audio.removeEventListener('pause', updateDuration);
      audio.removeEventListener('ended', updateDuration);
    };
  }, [audioUrl]);

  const getActiveAudio = () => {
    const audio = activateSharedAudio(audioUrl);
    audioRef.current = audio;

    return audio;
  };

  const handleToggle = async () => {
    const audio = getActiveAudio();
    if (!audio) return;

    if (audio.paused) {
      try {
        await audio.play();
      } catch (error) {
        console.error('Unable to play audio:', error);
      }
    } else {
      audio.pause();
    }
  };

  const handleRestart = () => {
    const audio = getActiveAudio();
    if (!audio) return;

    audio.currentTime = 0;
    setCurrentTime(0);
    audioStateCache.set(audioUrl, getRuntimeAudioState(audio));
  };

  const handleBack = () => {
    const audio = getActiveAudio();
    if (!audio) return;

    audio.currentTime = Math.max(0, audio.currentTime - 5);
    setCurrentTime(audio.currentTime);
    audioStateCache.set(audioUrl, getRuntimeAudioState(audio));
  };

  const handleSeekChange = (event) => {
    const audio = getActiveAudio();
    if (!audio) return;

    const nextTime = Number(event.target.value);
    if (!Number.isFinite(nextTime)) return;

    const playableDuration = getPlayableDuration(audio) || duration;
    audio.currentTime = nextTime;
    setCurrentTime(nextTime);
    setDuration(playableDuration || duration);
    audioStateCache.set(audioUrl, getRuntimeAudioState(audio));
  };

  const handleMuteToggle = () => {
    const audio = getActiveAudio();
    if (!audio) return;

    audio.muted = !audio.muted;
    setIsMuted(audio.muted);
    audioStateCache.set(audioUrl, getRuntimeAudioState(audio));
  };

  const handlePlaybackRateToggle = () => {
    const audio = getActiveAudio();
    if (!audio) return;

    const currentIndex = PLAYBACK_RATES.indexOf(audio.playbackRate || playbackRate);
    const nextRate = PLAYBACK_RATES[(currentIndex + 1) % PLAYBACK_RATES.length];
    audio.playbackRate = nextRate;
    setPlaybackRate(nextRate);
    audioStateCache.set(audioUrl, getRuntimeAudioState(audio));
  };

  return (
    <div className="listening-test__audio-section">
      <div className="listening-test__audio-controls">
        <button type="button" className="listening-test__audio-icon-button" onClick={handleBack} disabled={!isAudioAvailable} aria-label="Tua lại 5 giây">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M3.5 3.5V6H6M3.86 9.74A4.08 4.08 0 1 0 3.5 5.67" stroke="currentColor" strokeWidth="1.35" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
        <button type="button" className="listening-test__audio-play-button" onClick={handleToggle} disabled={!isAudioAvailable} aria-label={isPlaying ? 'Tạm dừng' : 'Phát audio'}>
          {isPlaying ? (
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M8 6v12M16 6v12" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" />
            </svg>
          ) : (
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M4 2L13.3333 8L4 14V2Z" fill="white" stroke="white" strokeWidth="1.33333" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          )}
        </button>
        <button type="button" className="listening-test__audio-icon-button" onClick={handleRestart} disabled={!isAudioAvailable} aria-label="Phát lại từ đầu">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M10.5 3.5V6H8M10.14 9.74A4.08 4.08 0 1 1 10.5 5.67" stroke="currentColor" strokeWidth="1.35" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
        <div className="listening-test__audio-progress-marker" />
        <div className="listening-test__audio-waveform">
          {WAVEFORM_BARS.map((height, index) => {
            const progress = duration ? currentTime / duration : 0;
            const isActive = (index + 1) / WAVEFORM_BARS.length <= progress;

            return (
              <span
                key={`${height}-${index}`}
                className={`listening-test__audio-waveform-bar ${isActive ? 'is-active' : ''}`}
                style={{ height: `${height}px` }}
              />
            );
          })}
          <input
            type="range"
            className="listening-test__audio-waveform-range"
            min="0"
            max={duration || 0}
            step="0.1"
            value={Math.min(currentTime, duration || currentTime)}
            onChange={handleSeekChange}
            onInput={handleSeekChange}
            disabled={!duration || !isAudioAvailable}
            aria-label="Tua audio"
          />
        </div>
        <div className="listening-test__audio-spacer" />
        <div className="listening-test__audio-time">
          {formatAudioTime(currentTime)} / {formatAudioTime(duration)}
        </div>
        <button type="button" className="listening-test__audio-icon-button" onClick={handleMuteToggle} disabled={!isAudioAvailable} aria-label={isMuted ? 'Bật âm lượng' : 'Tắt âm lượng'}>
          {isMuted ? (
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <path d="M6.4165 2.74281C6.41639 2.66156 6.3922 2.58216 6.34701 2.51464C6.30181 2.44712 6.23762 2.3945 6.16255 2.36342C6.08748 2.33235 6.00488 2.3242 5.92519 2.34002C5.84549 2.35584 5.77227 2.39491 5.71475 2.45231L3.74075 4.42573C3.66457 4.50236 3.57394 4.56312 3.47411 4.60447C3.37428 4.64583 3.26723 4.66696 3.15917 4.66664H1.74984C1.59513 4.66664 1.44675 4.7281 1.33736 4.8375C1.22796 4.94689 1.1665 5.09527 1.1665 5.24998V8.74998C1.1665 8.90468 1.22796 9.05306 1.33736 9.16245C1.44675 9.27185 1.59513 9.33331 1.74984 9.33331H3.15917C3.26723 9.33299 3.37428 9.35412 3.47411 9.39548C3.57394 9.43683 3.66457 9.49759 3.74075 9.57423L5.71417 11.5482C5.77169 11.6059 5.84502 11.6451 5.92487 11.661C6.00472 11.6769 6.08749 11.6688 6.16271 11.6376C6.23793 11.6065 6.3022 11.5537 6.34738 11.4859C6.39256 11.4182 6.41662 11.3386 6.4165 11.2571V2.74281Z" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
              <path d="M9.3335 5.25L12.8335 8.75" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
              <path d="M12.8335 5.25L9.3335 8.75" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          ) : (
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <path d="M6.4165 2.74281C6.41639 2.66156 6.3922 2.58216 6.34701 2.51464C6.30181 2.44712 6.23762 2.3945 6.16255 2.36342C6.08748 2.33235 6.00488 2.3242 5.92519 2.34002C5.84549 2.35584 5.77227 2.39491 5.71475 2.45231L3.74075 4.42573C3.66457 4.50236 3.57394 4.56312 3.47411 4.60447C3.37428 4.64583 3.26723 4.66696 3.15917 4.66664H1.74984C1.59513 4.66664 1.44675 4.7281 1.33736 4.8375C1.22796 4.94689 1.1665 5.09527 1.1665 5.24998V8.74998C1.1665 8.90468 1.22796 9.05306 1.33736 9.16245C1.44675 9.27185 1.59513 9.33331 1.74984 9.33331H3.15917C3.26723 9.33299 3.37428 9.35412 3.47411 9.39548C3.57394 9.43683 3.66457 9.49759 3.74075 9.57423L5.71417 11.5482C5.77169 11.6059 5.84502 11.6451 5.92487 11.661C6.00472 11.6769 6.08749 11.6688 6.16271 11.6376C6.23793 11.6065 6.3022 11.5537 6.34738 11.4859C6.39256 11.4182 6.41662 11.3386 6.4165 11.2571V2.74281Z" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
              <path d="M9.3335 5.25C9.71214 5.75486 9.91683 6.36892 9.91683 7C9.91683 7.63108 9.71214 8.24514 9.3335 8.75" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
              <path d="M11.2959 10.7124C11.7834 10.2249 12.1701 9.64612 12.434 9.00916C12.6978 8.37219 12.8336 7.6895 12.8336 7.00005C12.8336 6.31061 12.6978 5.62791 12.434 4.99095C12.1701 4.35399 11.7834 3.77523 11.2959 3.28772" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          )}
        </button>
        <button type="button" className="listening-test__audio-icon-button" onClick={handlePlaybackRateToggle} disabled={!isAudioAvailable} aria-label={`Tốc độ audio ${playbackRate}x`}>
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M6.99984 8.75C7.96634 8.75 8.74984 7.9665 8.74984 7C8.74984 6.0335 7.96634 5.25 6.99984 5.25C6.03334 5.25 5.24984 6.0335 5.24984 7C5.24984 7.9665 6.03334 8.75 6.99984 8.75Z" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M11.0832 7C11.0832 7.23333 11.0598 7.46083 11.019 7.6825L12.3957 8.75583L11.229 10.7767L9.60734 10.1267C9.25234 10.4242 8.84484 10.6575 8.39984 10.8092L8.1665 12.5417H5.83317L5.59984 10.8092C5.15484 10.6575 4.74734 10.4242 4.39234 10.1267L2.77067 10.7767L1.604 8.75583L2.98067 7.6825C2.93984 7.46083 2.9165 7.23333 2.9165 7C2.9165 6.76667 2.93984 6.53917 2.98067 6.3175L1.604 5.24417L2.77067 3.22333L4.39234 3.87333C4.74734 3.57583 5.15484 3.3425 5.59984 3.19083L5.83317 1.45833H8.1665L8.39984 3.19083C8.84484 3.3425 9.25234 3.57583 9.60734 3.87333L11.229 3.22333L12.3957 5.24417L11.019 6.3175C11.0598 6.53917 11.0832 6.76667 11.0832 7Z" stroke="currentColor" strokeWidth="1.16667" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </button>
      </div>
    </div>
  );
}

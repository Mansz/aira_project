import { useState, useEffect, useRef } from 'react';
import { ZegoExpressEngine } from 'zego-express-engine-webrtc';
import {
  Box,
  Button,
  Paper,
  Typography,
  Stack,
  TextField,
  Alert,
  CircularProgress,
  IconButton,
  Tooltip,
} from '@mui/material';
import {
  Mic as MicIcon,
  MicOff as MicOffIcon,
  Videocam as VideocamIcon,
  VideocamOff as VideocamOffIcon,
} from '@mui/icons-material';
import { useAuth } from '@/hooks/useAuth';
import { api } from '@/lib/api';
import { User } from '@/types/auth';
import { LiveStreamTokenResponse } from '@/types/stream';

// Get ZEGOCLOUD configuration from environment variables
const appID = parseInt(import.meta.env.VITE_ZEGO_APP_ID || '0');
const server = import.meta.env.VITE_ZEGO_SERVER_URL || '';

// Validate configuration
if (!appID || !server) {
  console.error('ZEGOCLOUD configuration is missing. Please check your environment variables.');
}

export const LiveStreamingPage = () => {
  const { user } = useAuth() as { user: User | null };
  const [zg, setZg] = useState<ZegoExpressEngine | null>(null);
  const [localStream, setLocalStream] = useState<MediaStream | null>(null);
  const [isLive, setIsLive] = useState(false);
  const [error, setError] = useState('');
  const [streamTitle, setStreamTitle] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isMuted, setIsMuted] = useState(false);
  const [isVideoEnabled, setIsVideoEnabled] = useState(true);
  const localVideoRef = useRef<HTMLVideoElement>(null);

  useEffect(() => {
    // Initialize ZEGOCLOUD
    const zegoEngine = new ZegoExpressEngine(appID, server);
    setZg(zegoEngine);

    return () => {
      if (localStream) {
        zegoEngine.destroyStream(localStream);
      }
      zegoEngine.logoutRoom();
    };
  }, []);

  const startLiveStream = async () => {
    if (!zg || !streamTitle) {
      setError('Please enter a stream title');
      return;
    }
    
    if (!appID || !server) {
      setError('ZEGOCLOUD configuration is missing');
      return;
    }

    setIsLoading(true);
    setError('');

  try {
      // Get stream token from backend
      const tokenResponse = await api.getStreamToken(streamTitle) as { data: LiveStreamTokenResponse };
      
      // Login to room
      await zg.loginRoom(
        streamTitle,
        tokenResponse.data.token,
        { userID: user?.id?.toString() || '0', userName: user?.name || 'Admin' },
        { userUpdate: true }
      );

      // Start publishing stream
      const stream = await zg.createStream({
        camera: {
          audio: true,
          video: true,
        },
      });

      // Save stream info to backend
      await api.startLiveStream({
        title: streamTitle,
        description: '',
        stream_id: stream.streamID,
      });

      setLocalStream(stream);

      if (localVideoRef.current) {
        localVideoRef.current.srcObject = stream;
      }

      await zg.startPublishingStream(streamTitle, stream);
      setIsLive(true);
    } catch (err: any) {
      console.error('Failed to start live stream:', err);
      setError(err.response?.data?.message || err.message || 'Failed to start live stream');
    } finally {
      setIsLoading(false);
    }
  };

  const stopLiveStream = async () => {
    if (!zg || !localStream || !streamTitle) return;
    setIsLoading(true);

    try {
      zg.stopPublishingStream(streamTitle);
      zg.destroyStream(localStream);
      zg.logoutRoom();
      setLocalStream(null);
      setIsLive(false);
      if (localVideoRef.current) {
        localVideoRef.current.srcObject = null;
      }
    } catch (err: any) {
      console.error('Failed to stop live stream:', err);
      setError(err.response?.data?.message || err.message || 'Failed to stop live stream');
    } finally {
      setIsLoading(false);
    }
  };

  const toggleMute = () => {
    if (localStream) {
      localStream.getAudioTracks().forEach(track => {
        track.enabled = !track.enabled;
      });
      setIsMuted(!isMuted);
    }
  };

  const toggleVideo = () => {
    if (localStream) {
      localStream.getVideoTracks().forEach(track => {
        track.enabled = !track.enabled;
      });
      setIsVideoEnabled(!isVideoEnabled);
    }
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Live Streaming
      </Typography>

      <Paper elevation={0} sx={{ p: 3, maxWidth: 800 }}>
        {error && (
          <Alert severity="error" sx={{ mb: 3 }}>
            {error}
          </Alert>
        )}

        <Stack spacing={3}>
          <Box
            sx={{
              width: '100%',
              aspectRatio: '16/9',
              backgroundColor: 'black',
              borderRadius: 1,
              overflow: 'hidden',
              position: 'relative',
            }}
          >
            <video
              ref={localVideoRef}
              autoPlay
              playsInline
              muted
              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
            />
            {isLive && (
              <Box
                sx={{
                  position: 'absolute',
                  bottom: 16,
                  left: '50%',
                  transform: 'translateX(-50%)',
                  display: 'flex',
                  gap: 2,
                  backgroundColor: 'rgba(0, 0, 0, 0.5)',
                  padding: 1,
                  borderRadius: 2,
                }}
              >
                <Tooltip title={isMuted ? 'Unmute' : 'Mute'}>
                  <IconButton onClick={toggleMute} sx={{ color: 'white' }}>
                    {isMuted ? <MicOffIcon /> : <MicIcon />}
                  </IconButton>
                </Tooltip>
                <Tooltip title={isVideoEnabled ? 'Disable Video' : 'Enable Video'}>
                  <IconButton onClick={toggleVideo} sx={{ color: 'white' }}>
                    {isVideoEnabled ? <VideocamIcon /> : <VideocamOffIcon />}
                  </IconButton>
                </Tooltip>
              </Box>
            )}
          </Box>

          {!isLive ? (
            <Stack spacing={2}>
              <TextField
                label="Stream Title"
                value={streamTitle}
                onChange={(e) => setStreamTitle(e.target.value)}
                required
                disabled={isLive || isLoading}
              />
              <Button
                variant="contained"
                onClick={startLiveStream}
                disabled={!streamTitle || isLoading}
                sx={{
                  backgroundColor: 'black',
                  '&:hover': {
                    backgroundColor: '#333',
                  },
                }}
              >
                {isLoading ? (
                  <CircularProgress size={24} sx={{ color: 'white' }} />
                ) : (
                  'Start Streaming'
                )}
              </Button>
            </Stack>
          ) : (
            <Stack spacing={2}>
              <Typography variant="h6" align="center">
                Live: {streamTitle}
              </Typography>
              <Button
                variant="contained"
                color="error"
                onClick={stopLiveStream}
                disabled={isLoading}
                sx={{
                  backgroundColor: '#dc3545',
                  '&:hover': {
                    backgroundColor: '#c82333',
                  },
                }}
              >
                {isLoading ? (
                  <CircularProgress size={24} sx={{ color: 'white' }} />
                ) : (
                  'Stop Streaming'
                )}
              </Button>
            </Stack>
          )}
        </Stack>
      </Paper>
    </Box>
  );
};

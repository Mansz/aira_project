import { useState, useRef, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  Grid,
  Card,
  CardContent,
  Stack,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  MenuItem,
  IconButton,
  Alert,
  CircularProgress,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
} from '@mui/material';
import {
  Videocam,
  Timeline,
  People,
  Timer,
  CurrencyExchange,
  CalendarToday,
  Close,
  Visibility,
  Edit,
  Delete,
} from '@mui/icons-material';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts';
import { api } from '../../lib/api';
import { LiveStream } from '../../types/stream';

// Mock data for viewer trends
const viewerData = [
  { time: '10:00', viewers: 500 },
  { time: '11:00', viewers: 650 },
  { time: '12:00', viewers: 800 },
  { time: '13:00', viewers: 750 },
  { time: '14:00', viewers: 900 },
];

interface NewStreamDialogProps {
  open: boolean;
  onClose: () => void;
  onStart: (data: any) => void;
}

const NewStreamDialog = ({ open, onClose, onStart }: NewStreamDialogProps) => {
  const [formData, setFormData] = useState({
    title: '',
    category: '',
  });

  const handleSubmit = () => {
    onStart(formData);
    onClose();
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          Mulai Live Streaming Baru
          <IconButton onClick={onClose} size="small">
            <Close />
          </IconButton>
        </Box>
      </DialogTitle>
      <DialogContent>
        <Box sx={{ pt: 2, display: 'flex', flexDirection: 'column', gap: 2 }}>
          <TextField
            fullWidth
            label="Judul Sesi"
            value={formData.title}
            onChange={(e) => setFormData({ ...formData, title: e.target.value })}
          />
          <TextField
            fullWidth
            select
            label="Kategori"
            value={formData.category}
            onChange={(e) => setFormData({ ...formData, category: e.target.value })}
          >
            <MenuItem value="fashion">Fashion</MenuItem>
            <MenuItem value="beauty">Beauty</MenuItem>
            <MenuItem value="electronics">Electronics</MenuItem>
          </TextField>
          <Box
            sx={{
              width: '100%',
              height: 240,
              backgroundColor: '#f5f5f5',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              borderRadius: 1,
            }}
          >
            <video
              id="local-video"
              style={{
                width: '100%',
                height: '100%',
                objectFit: 'cover',
                borderRadius: 8,
              }}
              muted
              playsInline
            />
          </Box>
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Batal</Button>
        <Button
          onClick={handleSubmit}
          variant="contained"
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Mulai Sekarang
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export const LiveDashboardPage = () => {
  const [isLive, setIsLive] = useState(false);
  const [showNewStreamDialog, setShowNewStreamDialog] = useState(false);
  const [liveTime, setLiveTime] = useState(0);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<any>(null);
  const [activeStream, setActiveStream] = useState<LiveStream | null>(null);
  const [streams, setStreams] = useState<LiveStream[]>([]);
  const timerRef = useRef<NodeJS.Timeout>();

  useEffect(() => {
    loadData();
  }, []);

  useEffect(() => {
    if (isLive) {
      timerRef.current = setInterval(() => {
        setLiveTime((prev) => prev + 1);
      }, 1000);
    }

    return () => {
      if (timerRef.current) {
        clearInterval(timerRef.current);
      }
    };
  }, [isLive]);

  const loadData = async () => {
    try {
      setLoading(true);
      const [statsResponse, activeStreamResponse, historyResponse] = await Promise.all([
        api.getLiveStreamStats(),
        api.getActiveStream().catch(() => ({ data: null })), // Handle 404 for no active stream
        api.getLiveStreamHistory()
      ]);
      
      setStats({
        ...statsResponse.data,
        stream_history: historyResponse.data || []
      });
      setStreams(historyResponse.data || []);
      
      if (activeStreamResponse.data) {
        setActiveStream(activeStreamResponse.data);
        setIsLive(true);
        // Calculate live time if stream is active
        const startTime = new Date(activeStreamResponse.data.start_time);
        const now = new Date();
        const diffInSeconds = Math.floor((now.getTime() - startTime.getTime()) / 1000);
        setLiveTime(diffInSeconds);
      } else {
        setIsLive(false);
        setActiveStream(null);
      }
    } catch (error) {
      console.error('Error loading data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEditStream = (stream: LiveStream) => {
    // Navigate to edit page or show edit dialog
    console.log('Edit stream:', stream.id);
  };

  const handleDeleteStream = async (streamId: string) => {
    if (window.confirm('Anda yakin ingin menghapus sesi streaming ini?')) {
      try {
        await api.deleteLiveStream(streamId);
        loadData(); // Refresh data
      } catch (error) {
        console.error('Error deleting stream:', error);
        alert('Gagal menghapus sesi streaming. Silakan coba lagi.');
      }
    }
  };

  const handleViewDetails = (stream: LiveStream) => {
    // Navigate to stream details or show details modal
    console.log('View details for stream:', stream);
  };

  const formatTime = (seconds: number) => {
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hrs.toString().padStart(2, '0')}:${mins
      .toString()
      .padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  const handleStartStream = async (data: any) => {
    try {
      const response = await api.startLiveStream(data);
      setActiveStream(response.data);
      setIsLive(true);
      setLiveTime(0);
    } catch (error) {
      console.error('Error starting stream:', error);
    }
  };

  const handleEndStream = async () => {
    if (window.confirm('Anda yakin ingin mengakhiri siaran?')) {
      try {
        await api.endLiveStream();
        setIsLive(false);
        setLiveTime(0);
        setActiveStream(null);
        loadData(); // Refresh stats
      } catch (error) {
        console.error('Error ending stream:', error);
      }
    }
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '50vh' }}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h4">Dashboard Live</Typography>
        <Stack direction="row" spacing={2}>
          <Button
            variant="outlined"
            startIcon={<Timeline />}
            sx={{
              borderColor: 'black',
              color: 'black',
              '&:hover': {
                borderColor: '#333',
                backgroundColor: 'rgba(0, 0, 0, 0.04)',
              },
            }}
          >
            Lihat Analitik Detail
          </Button>
          {!isLive && (
            <Button
              variant="contained"
              startIcon={<Videocam />}
              onClick={() => setShowNewStreamDialog(true)}
              sx={{
                backgroundColor: 'black',
                '&:hover': {
                  backgroundColor: '#333',
                },
              }}
            >
              Mulai Live Streaming
            </Button>
          )}
          {isLive && (
            <Button
              variant="contained"
              color="error"
              onClick={handleEndStream}
            >
              Akhiri Siaran
            </Button>
          )}
        </Stack>
      </Box>

      {isLive && (
        <Alert
          severity="info"
          sx={{ mb: 3 }}
          action={
            <Typography variant="body2" sx={{ fontFamily: 'monospace' }}>
              {formatTime(liveTime)}
            </Typography>
          }
        >
          Siaran sedang berlangsung
        </Alert>
      )}

      <Grid container spacing={3}>
        {/* Stat Cards */}
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <People sx={{ fontSize: 40, color: 'primary.main' }} />
                <Typography variant="h4">{activeStream?.viewer_count || 0}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Penonton Aktif
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Timer sx={{ fontSize: 40, color: 'secondary.main' }} />
                <Typography variant="h4">{stats?.average_watch_time || '00:00'}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Rata-rata Durasi Menonton
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <CurrencyExchange sx={{ fontSize: 40, color: 'success.main' }} />
                <Typography variant="h4">
                  {stats?.total_revenue 
                    ? `Rp ${(stats.total_revenue / 1000000).toFixed(1)}M`
                    : 'Rp 0'}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Pendapatan Live
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <CalendarToday sx={{ fontSize: 40, color: 'warning.main' }} />
                <Typography variant="h4">{stats?.total_sessions || 0}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Sesi Bulan Ini
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>

        {/* Viewer Trend Chart */}
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" sx={{ mb: 3 }}>
              Grafik Penonton
            </Typography>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={viewerData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="time" />
                <YAxis />
                <Tooltip />
                <Line
                  type="monotone"
                  dataKey="viewers"
                  stroke="#000"
                  strokeWidth={2}
                />
              </LineChart>
            </ResponsiveContainer>
          </Paper>
        </Grid>

        {/* Scheduled Sessions */}
        <Grid item xs={12} md={4}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" sx={{ mb: 3 }}>
              Jadwal Live Hari Ini
            </Typography>
            {stats?.upcoming_sessions?.length > 0 ? (
              <Stack spacing={2}>
                {stats.upcoming_sessions.map((session: any) => (
                  <Card key={session.id} variant="outlined">
                    <CardContent>
                      <Typography variant="subtitle1">{session.title}</Typography>
                      <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                        {new Date(session.start_time).toLocaleTimeString('id-ID', {
                          hour: '2-digit',
                          minute: '2-digit',
                          hour12: false
                        })} - {session.end_time ? new Date(session.end_time).toLocaleTimeString('id-ID', {
                          hour: '2-digit',
                          minute: '2-digit',
                          hour12: false
                        }) : 'Selesai'} WIB
                      </Typography>
                      <Chip 
                        size="small" 
                        label={session.status === 'scheduled' ? 'Belum Mulai' : session.status} 
                        color={session.status === 'scheduled' ? 'warning' : 'default'} 
                      />
                    </CardContent>
                  </Card>
                ))}
              </Stack>
            ) : (
              <Typography variant="body2" color="text.secondary" sx={{ textAlign: 'center' }}>
                Tidak ada jadwal live streaming hari ini
              </Typography>
            )}
          </Paper>
        </Grid>
      </Grid>

      {/* Live Stream History Table */}
      <Paper sx={{ mt: 4, p: 3 }}>
        <Box sx={{ mb: 3, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <Typography variant="h6">Riwayat Live Streaming</Typography>
          <Button
            variant="contained"
            startIcon={<Videocam />}
            onClick={() => setShowNewStreamDialog(true)}
            sx={{
              backgroundColor: 'black',
              '&:hover': {
                backgroundColor: '#333',
              },
            }}
          >
            Tambah Sesi Baru
          </Button>
        </Box>

        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Judul</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Penonton</TableCell>
                <TableCell>Mulai</TableCell>
                <TableCell>Selesai</TableCell>
                <TableCell>Total Pendapatan</TableCell>
                <TableCell align="right">Aksi</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {streams.map((stream: LiveStream) => (
                <TableRow key={stream.id} hover>
                  <TableCell>{stream.title}</TableCell>
                  <TableCell>
                    <Chip
                      size="small"
                      label={
                        stream.status === 'live' ? 'Sedang Live' :
                        stream.status === 'ended' ? 'Selesai' :
                        'Dijadwalkan'
                      }
                      color={
                        stream.status === 'live' ? 'success' :
                        stream.status === 'ended' ? 'default' :
                        'warning'
                      }
                    />
                  </TableCell>
                  <TableCell>{stream.viewer_count}</TableCell>
                  <TableCell>
                    {stream.start_time ? new Date(stream.start_time).toLocaleString('id-ID') : '-'}
                  </TableCell>
                  <TableCell>
                    {stream.end_time ? new Date(stream.end_time).toLocaleString('id-ID') : '-'}
                  </TableCell>
                  <TableCell>
                    Rp 0
                  </TableCell>
                  <TableCell align="right">
                    <Stack direction="row" spacing={1} justifyContent="flex-end">
                      <IconButton
                        size="small"
                        onClick={() => handleViewDetails(stream)}
                      >
                        <Visibility fontSize="small" />
                      </IconButton>
                      <IconButton
                        size="small"
                        onClick={() => handleEditStream(stream)}
                      >
                        <Edit fontSize="small" />
                      </IconButton>
                      <IconButton
                        size="small"
                        onClick={() => handleDeleteStream(stream.id)}
                      >
                        <Delete fontSize="small" />
                      </IconButton>
                    </Stack>
                  </TableCell>
                </TableRow>
              ))}
              {streams.length === 0 && (
                <TableRow>
                  <TableCell colSpan={7} align="center">
                    <Typography variant="body2" color="text.secondary">
                      Belum ada riwayat live streaming
                    </Typography>
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </TableContainer>
      </Paper>

      <NewStreamDialog
        open={showNewStreamDialog}
        onClose={() => setShowNewStreamDialog(false)}
        onStart={handleStartStream}
      />
    </Box>
  );
};

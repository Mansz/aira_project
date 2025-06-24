import { useState, useEffect, useCallback } from 'react';
import { LiveStream } from '@/types/stream';
import {
  Box,
  Paper,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  IconButton,
  TextField,
  InputAdornment,
  Stack,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Grid,
  Card,
  CardContent,
  CircularProgress,
} from '@mui/material';
import {
  Search,
  Visibility,
  Edit,
  Delete,
  LiveTv,
  People,
  Schedule,
  Add as AddIcon,
} from '@mui/icons-material';
import { api } from '@/lib/api';

interface StreamDetailDialogProps {
  open: boolean;
  stream?: LiveStream;
  onClose: () => void;
}

const StreamDetailDialog = ({ open, stream, onClose }: StreamDetailDialogProps) => {
  if (!stream) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Detail Live Streaming
      </DialogTitle>
      <DialogContent>
        <Grid container spacing={3}>
          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Informasi Stream
                </Typography>
                <Typography variant="h6" sx={{ mt: 1 }}>
                  {stream.title}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {stream.description || 'Tidak ada deskripsi'}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Status
                </Typography>
                <Stack direction="row" spacing={1} sx={{ mt: 1 }}>
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
                </Stack>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                  Statistik
                </Typography>
                <Grid container spacing={2}>
                  <Grid item xs={4}>
                    <Typography variant="h6">{stream.viewer_count}</Typography>
                    <Typography variant="body2" color="text.secondary">
                      Total Penonton
                    </Typography>
                  </Grid>
                  <Grid item xs={4}>
                    <Typography variant="h6">
                      {stream.start_time ? new Date(stream.start_time).toLocaleTimeString('id-ID') : '-'}
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Waktu Mulai
                    </Typography>
                  </Grid>
                  <Grid item xs={4}>
                    <Typography variant="h6">
                      {stream.end_time ? new Date(stream.end_time).toLocaleTimeString('id-ID') : '-'}
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Waktu Selesai
                    </Typography>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Tutup</Button>
      </DialogActions>
    </Dialog>
  );
};

export const LiveStreamTablePage = () => {
  const [selectedStream, setSelectedStream] = useState<LiveStream | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [streams, setStreams] = useState<LiveStream[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string>('');

  const handleOpenDetail = (stream: LiveStream) => {
    setSelectedStream(stream);
  };

  const handleCloseDetail = () => {
    setSelectedStream(null);
  };

  const fetchStreams = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const response = await api.getLiveStreamHistory();
      setStreams(response.data || []);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch live streams');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchStreams();
  }, [fetchStreams]);

  const handleDeleteStream = async (streamId: string) => {
    if (window.confirm('Anda yakin ingin menghapus sesi streaming ini?')) {
      try {
        await api.deleteLiveStream(streamId);
        fetchStreams(); // Refresh data
      } catch (err: any) {
        console.error('Error deleting stream:', err);
        alert('Gagal menghapus sesi streaming');
      }
    }
  };

  // Stats calculation
  const stats = {
    totalStreams: streams.length,
    activeStreams: streams.filter(stream => stream.status === 'live').length,
    scheduledStreams: streams.filter(stream => stream.status === 'scheduled').length,
    totalViewers: streams.reduce((sum, stream) => sum + stream.viewer_count, 0),
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Daftar Live Streaming
      </Typography>

      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <LiveTv sx={{ fontSize: 40, color: 'primary.main' }} />
                <Typography variant="h4">{stats.totalStreams}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Sesi
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <People sx={{ fontSize: 40, color: 'success.main' }} />
                <Typography variant="h4">{stats.totalViewers}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Penonton
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <LiveTv sx={{ fontSize: 40, color: 'error.main' }} />
                <Typography variant="h4">{stats.activeStreams}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Sedang Live
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Schedule sx={{ fontSize: 40, color: 'warning.main' }} />
                <Typography variant="h4">{stats.scheduledStreams}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Dijadwalkan
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Paper sx={{ p: 3 }}>
        <Box sx={{ mb: 3, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <TextField
            placeholder="Cari live streaming..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            sx={{ width: 300 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Search />
                </InputAdornment>
              ),
            }}
          />
          <Button
            variant="contained"
            startIcon={<AddIcon />}
            onClick={() => window.location.href = '/streaming/live'}
            sx={{
              backgroundColor: 'black',
              '&:hover': {
                backgroundColor: '#333',
              },
            }}
          >
            Mulai Live Streaming
          </Button>
        </Box>

        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
            <CircularProgress />
          </Box>
        ) : error ? (
          <Typography color="error" align="center" sx={{ p: 3 }}>
            {error}
          </Typography>
        ) : (
          <TableContainer>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Judul</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell>Penonton</TableCell>
                  <TableCell>Mulai</TableCell>
                  <TableCell>Selesai</TableCell>
                  <TableCell align="right">Aksi</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {streams
                  .filter(stream =>
                    stream.title.toLowerCase().includes(searchQuery.toLowerCase())
                  )
                  .map((stream) => (
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
                      <TableCell align="right">
                        <Stack direction="row" spacing={1} justifyContent="flex-end">
                          <IconButton
                            size="small"
                            onClick={() => handleOpenDetail(stream)}
                            sx={{ color: 'black' }}
                          >
                            <Visibility fontSize="small" />
                          </IconButton>
                          <IconButton
                            size="small"
                            onClick={() => {/* TODO: Implement edit */}}
                            sx={{ color: 'black' }}
                          >
                            <Edit fontSize="small" />
                          </IconButton>
                          <IconButton
                            size="small"
                            onClick={() => handleDeleteStream(stream.id)}
                            sx={{ color: 'error.main' }}
                          >
                            <Delete fontSize="small" />
                          </IconButton>
                        </Stack>
                      </TableCell>
                    </TableRow>
                  ))}
                {streams.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={6} align="center">
                      <Typography variant="body2" color="text.secondary">
                        Belum ada sesi live streaming
                      </Typography>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </TableContainer>
        )}
      </Paper>

      <StreamDetailDialog
        open={Boolean(selectedStream)}
        stream={selectedStream || undefined}
        onClose={handleCloseDetail}
      />
    </Box>
  );
};

export default LiveStreamTablePage;

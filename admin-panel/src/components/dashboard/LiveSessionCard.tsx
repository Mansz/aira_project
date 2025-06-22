import {
  Box,
  Card,
  CardContent,
  Typography,
  Button,
  Stack,
  Chip,
  Skeleton,
  Paper,
} from '@mui/material';

// Definisi type langsung di dalam file untuk mengatasi error import
interface LiveStreamStats {
  isLive: boolean;
  viewers: number;
  duration: string;
  startTime?: string;
  title?: string;
}

interface LiveSessionCardProps {
  loading: boolean;
  stats: LiveStreamStats | null;
  onStartLive?: () => void;
}

export const LiveSessionCard = ({ loading, stats, onStartLive }: LiveSessionCardProps) => (
  <Paper sx={{ p: 3, height: '100%' }}>
    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
      <Typography variant="h6">Live Session</Typography>
      <Button
        variant="contained"
        size="small"
        onClick={onStartLive}
        sx={{
          backgroundColor: 'black',
          '&:hover': {
            backgroundColor: '#333',
          },
        }}
      >
        Start New Live
      </Button>
    </Box>
    {loading ? (
      <Skeleton variant="rectangular" height={100} />
    ) : stats?.isLive ? (
      <Card sx={{ mb: 2 }}>
        <CardContent>
          <Typography variant="subtitle1">Current Live Session</Typography>
          <Stack
            direction="row"
            spacing={2}
            alignItems="center"
            sx={{ mt: 1 }}
          >
            <Chip
              size="small"
              label={`${stats.viewers} viewers`}
              color="primary"
            />
            <Typography variant="body2" color="text.secondary">
              {stats.duration}
            </Typography>
            <Chip
              size="small"
              label="Live"
              color="error"
            />
          </Stack>
        </CardContent>
      </Card>
    ) : (
      <Typography color="text.secondary">No active live sessions</Typography>
    )}
  </Paper>
);

// Export type juga agar bisa digunakan di file lain jika diperlukan
export type { LiveStreamStats };